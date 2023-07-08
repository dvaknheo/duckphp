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
            self::$default = static::class;
            self::$current = self::$default;
            self::$shared[static::class]=true;
            return true;
        }
        return false;
    }
    public static function GetObject($class, $object = null)
    {
        if(isset($containers[self::$current][$class])){
            if($object){
                $containers[self::$current][$class] = $object;
            }
            return $containers[self::$current][$class];
        }
        if(isset(self::$shared[$class])){
          if(isset(self::$containers[self::$default][$class])){
            if($object){
                self::$containers[self::$default][$class] = $object;
            }
            return self::$containers[self::$default][$class];
          }
          $result =  $object ?? new $class;
          self::$containers[self::$default][$class] =  $result;
          return $result;
        }
        $result =  $object ?? new $class;
        self::$containers[self::$current][$class] =  $result;
        return $result;
        
    }
    public static function SetShareClass($class)
    {
        self::$shared[$class] = true;
    }
    public static function SwitchContainer($container)
    {
        static::ReplaceSingletonImplement();
        self::$current = $container;
    }
    public static function DumpContainers()
    {
        var_dump(self::$containers);
        var_dump(self::$containers);
        var_dump(self::$containers);
    }

}
