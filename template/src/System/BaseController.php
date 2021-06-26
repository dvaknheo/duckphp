<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use Duckphp\Helper\ControllerHelperTrait;
use Duckphp\SingletonEx\SingletonExTrait;

class BaseController
{
    use ControllerHelperTrait;
    
    use SingletonExTrait{ G  as _G; };
    protected static $singleton_lock = false;
    public function G($object =null)
    {
        static::$singleton_lock = true;
        $ret = SingletonExTrait::_G($object);
        static::$singleton_lock = false;
        return $ret;
    }
    protected $is_controller = false;
    
    public function __construct($base='')
    {
        //作为助手类
        if(static::$singleton_lock){
            static::$singleton_lock = false;
            return;
        }
        //作为助手类，禁止访问这里的方法。
        if (method_exists(self::class, static::getRouteCallingMethod()){
            static::Exit404();
            return;
        }
        $this->is_controller = true;
        $this->onInitController();
    }

    public function __destroy()
    {
        if (!$this->is_controller){
            return;
        }
        $this->onDestroyController();
    }
    protected function onInitController()
    {
        // real constructor();
        // do you work
    }
    protected function onDestroyController()
    {
        // real constructor();
        // do you work
    }
    //////////////////////
    public static function SessionManager()
    {
        return SessionManager::G();
    }
}
