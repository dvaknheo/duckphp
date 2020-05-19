<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\DB\DB;

class DBManager extends ComponentBase
{
    const TAG_WRITE = 0;
    const TAG_READ = 1;
    
    public $options = [
        'database_list' => null,
        'db_create_handler' => null,
        'db_close_handler' => null,
        'db_exception_handler' => null,
        'db_before_get_object_handler' => null,
        'db_database_list_from_setting' => true,
        'db_close_at_output' => true,
    ];

    
    protected $database_config_list = [];
    protected $databases = [];
    
    protected $db_create_handler = null;
    protected $db_close_handler = null;
    protected $db_exception_handler = null;
    
    protected $db_before_get_object_handler = null;
    protected $before_query_handler = null;

    //@override
    protected function initOptions(array $options)
    {
        $this->db_before_get_object_handler = $this->options['db_before_get_object_handler'] ?? null;
        $this->database_config_list = $this->options['database_list'];
        $this->db_create_handler = $this->options['db_create_handler'] ?? [DB::class,'CreateDBInstance'];
        $this->db_close_handler = $this->options['db_close_handler'] ?? [DB::class,'CloseDBInstance'];
        $this->db_exception_handler = $this->options['db_exception_handler'] ?? null;
    }
    //@override
    protected function initContext(object $context)
    {
        if ($this->options['db_database_list_from_setting']) {
            $database_list = get_class($context)::Setting('database_list') ?? null;
            if (!isset($database_list)) {
                $database_list = isset($context->options) ? ($context->options['database_list'] ?? null) : null;
            }
            if ($database_list) {
                $this->database_config_list = $database_list;
            }
        }
        // db_before_get_object_handler
        if (is_array($this->db_before_get_object_handler) && $this->db_before_get_object_handler[0] === null) {
            $this->db_before_get_object_handler[0] = get_class($context);
        }
        if ($this->options['db_close_at_output'] && method_exists($context, 'addBeforeShowHandler')) {
            $context->addBeforeShowHandler([static::class,'CloseAllDB']);
        }
        $this->before_query_handler = isset($context->options) ? ($context->options['db_before_query_handler'] ?? null) : null;
        if (method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'DB' => [static::class, 'DB'],
                    'DB_R' => [static::class, 'DB_R'],
                    'DB_W' => [static::class, 'DB_W'],
                ],
                ['M','A']
            );
            $context->extendComponents(
                [
                    'setDBHandler' => [static::class .'::G', 'setDBHandler'],
                    'setBeforeGetDBHandler' => [static::class .'::G', 'setBeforeGetDBHandler'],
                ],
                ['A']
            );
        }
    }
    public static function CloseAllDB()
    {
        return static::G()->_closeAllDB();
    }
    public function OnException()
    {
        return static::G()->_OnException();
    }
    public static function DB($tag = null)
    {
        return static::G()->_DB($tag);
    }
    public static function DB_W()
    {
        return static::G()->_DB_W();
    }
    public static function DB_R()
    {
        return static::G()->_DB_R();
    }
    
    public function setDBHandler($db_create_handler, $db_close_handler = null, $db_exception_handler = null)
    {
        $this->db_create_handler = $db_create_handler;
        $this->db_close_handler = $db_close_handler;
        $this->db_exception_handler = $db_exception_handler;
    }
    public function setBeforeGetDBHandler($db_before_get_object_handler)
    {
        $this->db_before_get_object_handler = $db_before_get_object_handler;
    }
    public function getDBHandler()
    {
        return [$this->db_create_handler,$this->db_close_handler,$this->db_exception_handler];
    }
    public function _DB($tag = null)
    {
        if (isset($this->db_before_get_object_handler)) {
            ($this->db_before_get_object_handler)($this, $tag);
        }
        if (!isset($tag)) {
            if (empty($this->database_config_list)) {
                return null; // @codeCoverageIgnore
            }
            $t = array_keys($this->database_config_list);
            $tag = $t[0];
        }
        $db_config = $this->database_config_list[$tag] ?? null;
        if ($db_config === null) {
            return null; // @codeCoverageIgnore
        }
        return $this->getDatabase($db_config, $tag);
    }
    protected function getDatabase($db_config, $tag)
    {
        if (!isset($this->databases[$tag])) {
            $db = ($this->db_create_handler)($db_config, $tag);
            if ($this->before_query_handler) {
                $this->setBeforeQueryHandler($db, $this->before_query_handler);
            }
            $this->databases[$tag] = $db;
        }
        return $this->databases[$tag];
    }
    public function setBeforeQueryHandler($db, $before_query_handler)
    {
        if (is_callable([$db,'setBeforeQueryHandler'])) {
            $db->setBeforeQueryHandler($this->before_query_handler);
        }
    }
    public function _DB_W()
    {
        return $this->_DB(static::TAG_WRITE);
    }
    public function _DB_R()
    {
        if (!isset($this->database_config_list[static::TAG_READ])) {
            return $this->_DB(static::TAG_WRITE);
        }
        return $this->_DB(static::TAG_READ);
    }
    
    public function _closeAllDB()
    {
        if (!$this->db_close_handler) {
            return;
        }
        foreach ($this->databases as $tag => $v) {
            ($this->db_close_handler)($v, $tag);
        }
        $this->databases = [];
    }

    public function _OnException()
    {
        if (!$this->db_exception_handler) {
            return;
        }
        foreach ($this->databases as $tag => $v) {
            ($this->db_exception_handler)($v, $tag);
        }
        $this->databases = [];
    }
}
