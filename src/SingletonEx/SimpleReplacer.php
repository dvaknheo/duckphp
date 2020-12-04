<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\SingletonEx;

class SimpleReplacer
{
    protected static $classes;
    public static $EnableCompactable = false;
    public static function Replace()
    {
        if (!defined('__SINGLETONEX_REPALACER')) {
            define('__SINGLETONEX_REPALACER', static::class . '::GetObject');
            return true;
        }
        return false;
    }
    public static function GetObject($class, $object)
    {
        if (isset($object)) {
            self::$classes[$class] = $object;
            return self::$classes[$class];
        }
        if (isset(self::$classes[$class])) {
            return self::$classes[$class];
        }
        if (!self::$EnableCompactable) {
            self::$classes[$class] = new $class;
            return self::$classes[$class];
        }
        
        $ref = new \ReflectionClass($class);
        $prop = $ref->getProperty('_instances'); //OK Get It
        $prop->setAccessible(true);
        $array = $prop->getValue();
        if (!empty($array[$class])) {
            self::$classes[$class] = $array[$class];
        } else {
            self::$classes[$class] = new $class;
        }
    }
}
