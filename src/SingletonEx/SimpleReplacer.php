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
    public static function Replace($enableCompactable = false)
    {
        if (!defined('__SINGLETONEX_REPALACER')) {
            define('__SINGLETONEX_REPALACER', static::class . '::GetObject');
            self::$EnableCompactable = $enableCompactable;
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
        try {
            $prop = $ref->getProperty('_instances');
        } catch (\ReflectionException $ex) {
            self::$classes[$class] = new $class;
            return self::$classes[$class];
        }
        $prop->setAccessible(true);
        $array = $prop->getValue();
        if (empty($array[$class])) {
            self::$classes[$class] = new $class;
            return self::$classes[$class];
        }
        self::$classes[$class] = $array[$class];
        return self::$classes[$class];
    }
}
