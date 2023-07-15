<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

trait ContainerTrait
{
    public static $containers=[];
    public static $current;
    public static $default;
    public static $shared;
    public static function ReplaceSingletonImplement()
    {
        if (!defined('__SINGLETONEX_REPALACER')) {
            define('__SINGLETONEX_REPALACER', static::class . '::GetObject');
            define('__SINGLETONEX_REPALACER_CLASS', static::class );
            static::$default = static::class;
            static::$current = static::$default;
            static::$shared[static::class]=true;
            return true;
        }
        return false;
    }
    public static function GetObject($class, $object = null)
    {
        if(isset($containers[static::$current][$class])){
            if($object){
                $containers[static::$current][$class] = $object;
            }
            return $containers[static::$current][$class];
        }
        if(isset(static::$shared[$class])){
          if(isset(static::$containers[static::$default][$class])){
            if($object){
                static::$containers[static::$default][$class] = $object;
            }
            return static::$containers[static::$default][$class];
          }
          $result =  $object ?? new $class;
          static::$containers[static::$default][$class] =  $result;
          return $result;
        }
        $result =  $object ?? new $class;
        static::$containers[static::$current][$class] =  $result;
        return $result;
        
    }
    public function setDefaultContainer($class)
    {
        static::$default = $class;
    }
    public static function AddSharedClasses($classes)
    {
        foreach($classes as $class){
            static::$shared[$class] = true;
        }
    }
    public static function SwitchContainer($container)
    {
        static::ReplaceSingletonImplement();
        static::$current = $container;
    }
    public static function DumpSingleton()
    {
        var_dump(static::$default);
        var_dump(static::$current);
        var_dump(static::$shared);
        var_dump(static::$containers);
    }

}
