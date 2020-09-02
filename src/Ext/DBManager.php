<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Db\Db;

class DBManager extends ComponentBase
{
    const TAG_WRITE = 0;
    const TAG_READ = 1;
    
    public $options = [
        'database_list' => null,
        'db_before_get_object_handler' => null,
        'db_database_list_from_setting' => true,
        
        'log_sql_query' => false,
        'log_sql_level' => 'debug',
    ];
    
    protected $database_config_list = [];
    protected $databases = [];
    protected $context_class = null;
    //@override
    protected function initOptions(array $options)
    {
        $this->database_config_list = $this->options['database_list'];
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        
        if ($this->options['db_database_list_from_setting']) {
            /** @var mixed */
            $database_list = get_class($context)::Setting('database_list');
            if (!isset($database_list)) {
                $database_list = isset($context->options) ? ($context->options['database_list'] ?? null) : null;
            }
            if ($database_list) {
                $this->database_config_list = $database_list;
            }
        }
        
        //////////////////////////
        if (method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'setBeforeGetDbHandler' => [static::class .'::G', 'setBeforeGetDbHandler'],
                ],
                ['A']
            );
        }
    }
    public static function Db($tag = null)
    {
        return static::G()->_Db($tag);
    }
    public static function DbForWrite()
    {
        return static::G()->_DbForWrite();
    }
    public static function DbForRead()
    {
        return static::G()->_DbForRead();
    }
    public static function CloseAllDb()
    {
        return static::G()->_closeAllDb();
    }
    public static function OnQuery($db, $sql, ...$args)
    {
        return static::G()->_OnQuery($db, $sql, ...$args);
    }

    ///////////////////////
    
    public function setBeforeGetDbHandler($db_before_get_object_handler)
    {
        $this->options['db_before_get_object_handler'] = $db_before_get_object_handler;
    }

    public function _Db($tag = null)
    {
        if (!isset($tag)) {
            if (empty($this->database_config_list)) {
                throw new \ErrorException('DuckPhp: setting database_list missing');
            }
            $tag = static::TAG_WRITE;
        }
        return $this->getDatabase($tag);
    }
    protected function getDatabase($tag)
    {
        if (isset($this->options['db_before_get_object_handler'])) {
            ($this->options['db_before_get_object_handler'])($this, $tag);
        }
        if (!isset($this->databases[$tag])) {
            $db_config = $this->database_config_list[$tag] ?? null;
            if ($db_config === null) {
                throw new \ErrorException('DuckPhp: setting database_list['.$tag.'] missing');
            }
            $db = new Db($db_config);
            $db->init($db_config);
            $db->setBeforeQueryHandler([static::class, 'OnQuery']);
            
            $this->databases[$tag] = $db;
        }
        return $this->databases[$tag];
    }
    public function _DbForWrite()
    {
        return $this->_Db(static::TAG_WRITE);
    }
    public function _DbForRead()
    {
        if (!isset($this->database_config_list[static::TAG_READ])) {
            return $this->_Db(static::TAG_WRITE);
        }
        return $this->_Db(static::TAG_READ);
    }
    
    public function _closeAllDb()
    {
        foreach ($this->databases as $tag => $db) {
            $db->close();
        }
        $this->databases = [];
    }
    public function _OnQuery($db, $sql, ...$args)
    {
        if (!$this->options['log_sql_query']) {
            return;
        }
        ($this->context_class)::Logger()->log($this->options['log_sql_level'], '[sql]: ' . $sql, $args);
    }
}
