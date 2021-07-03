<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace Duckphp\Ext;

use DuckPhp\SingletonEx\SingletonExTrait;
use DuckPhp\Core\App;

trait SingletonControllerTrait
{
    //////// [[[[ ////////
    use SingletonExTrait { G  as _G ; }
    protected static $_instance_created_flags = [];
    public static function G($object = null)
    {
        if($object === null && !isset(static::$_instance_created_flags[static::class])) {
            $object = (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();
            static::$_instance_created_flags[static::class] = true;
            return SingletonExTrait::G($object);
        }
        return SingletonExTrait::G($object);
    }
    protected $is_controller = false;
    
    public function __construct()
    {
        $this->is_controller = true;
        //作为助手类，禁止访问这里的方法。
        if (method_exists(self::class, App::getRouteCallingMethod())){
            App::Exit404();
            return;
        }
        $this->is_controller = true;
        $this->initController();
    }

    public function __destruct()
    {
        if (!$this->is_controller){
            return;
        }
        $this->destroyController();
    }
    //for override();
    protected function initController()
    {
        //
    }
    //for override();
    protected function destroyController()
    {
        //
    }
}
