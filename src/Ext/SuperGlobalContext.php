<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class SuperGlobalContext extends ComponentBase
{
    public $options = [
        'superglobal_auto_extend_method' => false,
        'superglobal_auto_define' => false,
    ];
    
    public $_GET;
    public $_POST;
    public $_REQUEST;
    public $_SERVER;
    public $_COOKIE;
    public $_SESSION;
    public $_FILES;
    
    public function __construct()
    {
        $this->_LoadSuperGlobalAll();
    }
    protected function initOptions(array $options)
    {
        if ($this->options['superglobal_auto_define']) {
            static::DefineSuperGlobalContext();
        }
    }
    protected function initContext(object $context)
    {
        //$this->context_class = get_class($context);
        //////////////////////////
        if ($this->options['superglobal_auto_extend_method'] && \method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'LoadSuperGlobalAll' => static::class . '::LoadSuperGlobalAll',
                    'SaveSuperGlobalAll' => static::class . '::SaveSuperGlobalAll',
                ],
                ['A']
            );
        }
    }
    
    public static function DefineSuperGlobalContext()
    {
        if (!defined('__SUPERGLOBAL_CONTEXT')) {
            define('__SUPERGLOBAL_CONTEXT', static::class .'::G');
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
}
