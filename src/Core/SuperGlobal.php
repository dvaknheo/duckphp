<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class SuperGlobal extends ComponentBase
{
    public $options = [
        'superglobal_auto_define' => false,
    ];
    
    public $_GET;
    public $_POST;
    public $_REQUEST;
    public $_SERVER;
    public $_COOKIE;
    public $_SESSION;
    public $_FILES;
    
    protected $init_once = true;

    protected function initOptions(array $options)
    {
        if ($this->options['superglobal_auto_define']) {
            static::DefineSuperGlobalContext();
            $this->_LoadSuperGlobalAll();
        }
    }
    
    public static function DefineSuperGlobalContext()
    {
        if (!defined('__SUPERGLOBAL_CONTEXT')) {
            define('__SUPERGLOBAL_CONTEXT', static::class .'::_');
            return true;
        }
        return false;
    }
    public static function LoadSuperGlobalAll()
    {
        return static::G()->_LoadSuperGlobalAll();
    }
    public static function SaveSuperGlobalAll()
    {
        return static::G()->_SaveSuperGlobalAll();
    }
    public function _LoadSuperGlobalAll()
    {
        $this->_GET = $_GET;
        $this->_POST = $_POST;
        $this->_REQUEST = $_REQUEST;
        $this->_SERVER = $_SERVER;
        //$this->_ENV = $_ENV;
        $this->_COOKIE = $_COOKIE;
        $this->_SESSION = $_SESSION ?? null;
        $this->_FILES = $_FILES;
    }
    public function _SaveSuperGlobalAll()
    {
        $_GET = $this->_GET;
        $_POST = $this->_POST;
        $_REQUEST = $this->_REQUEST;
        $_SERVER = $this->_SERVER;
        //$_ENV = $this->_ENV;
        $_COOKIE = $this->_COOKIE;
        $_SESSION = $this->_SESSION;
        $_FILES = $this->_FILES;
    }
    //////////////////////
    public static function LoadSuperGlobal($key)
    {
        return static::G()->_LoadSuperGlobal($key);
    }
    public static function SaveSuperGlobal($key)
    {
        return static::G()->_SaveSuperGlobal($key);
    }
    public function _LoadSuperGlobal($key)
    {
        $this->$key = $GLOBALS[$key];
    }
    public function _SaveSuperGlobal($key)
    {
        $GLOBALS[$key] = $this->$key;
    }
    /////////////////////////
    /*
    public static function GET($key = null, $default = null)
    {
        return static::G()->_Get($key, $default);
    }
    public static function POST($key = null, $default = null)
    {
        return static::G()->_POST($key, $default);
    }
    public static function REQUEST($key = null, $default = null)
    {
        return static::G()->_REQUEST($key, $default);
    }
    public static function COOKIE($key = null, $default = null)
    {
        return static::G()->_COOKIE($key, $default);
    }
    public static function SERVER($key = null, $default = null)
    {
        return static::G()->_SERVER($key, $default);
    }
    public static function SESSION($key = null, $default = null)
    {
        return static::G()->_SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return static::G()->_FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return static::G()->_SessionSet($key, $value);
    }
    public static function SessionUnset($key)
    {
        return static::G()->_SessionUnset($key);
    }
    public static function SessionGet($key, $default = null)
    {
        return static::G()->_SessionGet($key, $default);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return static::G()->_CookieSet($key, $value, $expire);
    }
    public static function CookieGet($key, $default = null)
    {
        return static::G()->_CookieGet($key, $default);
    }
    //*/
    ///////////////////////////////////////
    protected function getSuperGlobalData($superglobal_key, $key, $default)
    {
        $data = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->$superglobal_key : ($GLOBALS[$superglobal_key] ?? []);
        
        if (isset($key)) {
            return $data[$key] ?? $default;
        } else {
            return $data ?? $default;
        }
    }
    public function _GET($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_GET', $key, $default);
    }
    public function _POST($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_POST', $key, $default);
    }
    public function _REQUEST($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_REQUEST', $key, $default);
    }
    public function _COOKIE($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_COOKIE', $key, $default);
    }
    public function _SERVER($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_SERVER', $key, $default);
    }
    public function _SESSION($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_SESSION', $key, $default);
    }
    public function _FILES($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_FILES', $key, $default);
    }
    //////////////////////////////////
    public function _SessionSet($key, $value)
    {
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            (__SUPERGLOBAL_CONTEXT)()->_SESSION[$key] = $value;
        } else {
            $_SESSION[$key] = $value;
        }
    }
    public function _SessionUnset($key)
    {
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            unset((__SUPERGLOBAL_CONTEXT)()->_SESSION[$key]);
        }
        unset($_SESSION[$key]);
    }
    public function _CookieSet($key, $value, $expire = 0)
    {
        SystemWrapper::_()->_setcookie($key, $value, $expire ? $expire + time():0);
    }
    public function _SessionGet($key, $default = null)
    {
        return $this->getSuperGlobalData('_SESSION', $key, $default);
    }
    public function _CookieGet($key, $default = null)
    {
        return $this->getSuperGlobalData('_COOKIE', $key, $default);
    }
}
