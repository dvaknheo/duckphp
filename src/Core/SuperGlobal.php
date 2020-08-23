<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class SuperGlobal extends ComponentBase
{
    public $options = [];
    
    public $_GET;
    public $_POST;
    public $_REQUEST;
    public $_SERVER;
    public $_ENV;
    public $_COOKIE;
    public $_SESSION;
    public $_FILES;
    
    public $GLOBALS = [];
    public $STATICS = [];
    public $CLASS_STATICS = [];
    
    protected $is_inited = false;
    public function __construct()
    {
        parent::__construct();
        $this->init([]);
    }
    public function reset()
    {
        $this->is_inited = false;
        return $this;
    }
    //@override
    protected function initOptions(array $options)
    {
        $this->_GET = &$_GET;
        $this->_POST = &$_POST;
        $this->_REQUEST = &$_REQUEST;
        $this->_SERVER = &$_SERVER;
        $this->_ENV = &$_ENV;
        $this->_COOKIE = &$_COOKIE;
        $this->_SESSION = &$_SESSION;
        $this->_FILES = &$_FILES;
        $this->GLOBALS = &$GLOBALS;
    }
    ///////////////////////////////
    public function session_start(array $options = [])
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            //try {
            session_start($options);
            //} catch (\Throwable $ex) {
                //return false;
            //}
        }
        $this->_SESSION = &$_SESSION;
    }
    public function session_id($session_id)
    {
        if ($session_id === null) {
            return session_id();
        }
        return session_id($session_id);
    }
    public function session_destroy()
    {
        session_destroy();
        $this->_SESSION = [];
    }
    public function session_set_save_handler($handler)
    {
        session_set_save_handler($handler);
    }
    ///////////////////////////////
    public function &_GLOBALS($k, $v = null)
    {
        if (!isset($this->GLOBALS[$k])) {
            $this->GLOBALS[$k] = $v;
        }
        return $this->GLOBALS[$k];
    }
    public function &_STATICS($name, $value = null, $parent = 0)
    {
        $t = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, $parent + 2)[$parent + 1] ?? [];
        $k = '';
        $k .= isset($t['object'])?'object_'.spl_object_hash($t['object']):'';
        $k .= $t['class'] ?? '';
        $k .= $t['type'] ?? '';
        $k .= $t['function'] ?? '';
        $k .= $k?'$':'';
        $k .= $name;
        
        if (!isset($this->STATICS[$k])) {
            $this->STATICS[$k] = $value;
        }
        return $this->STATICS[$k];
    }
    public function &_CLASS_STATICS($class_name, $var_name)
    {
        $k = $class_name.'::$'.$var_name;
        if (!isset($this->CLASS_STATICS[$k])) {
            $ref = new \ReflectionClass($class_name);
            $reflectedProperty = $ref->getProperty($var_name);
            $reflectedProperty->setAccessible(true);
            $this->CLASS_STATICS[$k] = $reflectedProperty->getValue();
        }
        return $this->CLASS_STATICS[$k];
    }
}
