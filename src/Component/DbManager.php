<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Logger;
use DuckPhp\Db\Db;

class DbManager extends ComponentBase
{
    const TAG_WRITE = 0;
    const TAG_READ = 1;
    /*
     database_list=>
    [[
        'dsn'=>"",
        'username'=>'???',
        'password'=>'???'
    ]]
    */
    public $options = [
        'database' => null,
        'database_list' => null,
        'database_list_reload_by_setting' => true,
        'database_list_try_single' => true,
        'database_log_sql_query' => false,
        'database_log_sql_level' => 'debug',
        'database_class' => '',
    ];
    
    protected $database_config_list = [];
    protected $databases = [];
    protected $init_once = true;
    protected $db_before_get_object_handler = null;
    //@override
    protected function initOptions(array $options)
    {
        //TODO $this->is_inited,
        //TODO ，拼合式的 dsn
        $database_list = $this->options['database_list'];
        if (!isset($database_list) && $this->options['database_list_try_single']) {
            $database = $this->options['database'];
            $database_list = $database ? array($database) : null;
        }
        $this->database_config_list = $database_list;
    }
    //@override
    protected function initContext(object $context)
    {
        //$this->context_class = $context
        //($this->context_class)::G()->_Setting();
        $setting = $context->_Setting(); /** @phpstan-ignore-line */
        
        if ($this->options['database_list_reload_by_setting']) {
            /** @var mixed */
            $database_list = $setting['database_list'] ?? null;
            if (!isset($database_list) && $this->options['database_list_try_single']) {
                $database = $setting['database'] ?? null;
                $database_list = $database ? array($database) : null;
            }
            $this->database_config_list = $database_list ?? $this->database_config_list;
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
    public static function CloseAll()
    {
        return static::G()->_CloseAll();
    }
    public static function OnQuery($db, $sql, ...$args)
    {
        return static::G()->_OnQuery($db, $sql, ...$args);
    }

    ///////////////////////
    
    public function setBeforeGetDbHandler($db_before_get_object_handler)
    {
        $this->db_before_get_object_handler = $db_before_get_object_handler;
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
        if (isset($this->db_before_get_object_handler)) {
            ($this->db_before_get_object_handler)($this, $tag);
        }
        if (!isset($this->databases[$tag])) {
            $db_config = $this->database_config_list[$tag] ?? null;
            if ($db_config === null) {
                throw new \ErrorException('DuckPhp: setting database_list['.$tag.'] missing');
            }
            $db = $this->getDb($db_config);
            
            $this->databases[$tag] = $db;
        }
        return $this->databases[$tag];
    }
    protected function getDb($db_config)
    {
        // todo $db = (clone Db::G())->init([...$db_config 'log_func'=>static::class.'::OnQuery']);
        if (empty($this->options['database_class'])) {
            $db = new Db();
        } else {
            $class = $this->options['database_class'];
            $db = new $class();
        }
        $db->init($db_config);
        if ($this->options['database_log_sql_query'] && is_callable([$db,'setBeforeQueryHandler'])) {
            $db->setBeforeQueryHandler([static::class, 'OnQuery']);
        }
        return $db;
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
    
    public function _CloseAll()
    {
        foreach ($this->databases as $tag => $db) {
            $db->close();
        }
        $this->databases = [];
    }
    public function _OnQuery($db, $sql, ...$args)
    {
        if (!$this->options['database_log_sql_query']) {
            return;
        }
        Logger::G()->log($this->options['database_log_sql_level'], '[sql]: ' . $sql, $args);
    }
}
