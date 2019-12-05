<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

trait ExtendableStaticCallTrait
{
    protected static $static_methods = [];
    
    public static function AssignExtendStaticMethod($key, $value = null)
    {
        self::$static_methods[static::class] = self::$static_methods[static::class] ?? [];
        if (is_array($key) && $value === null) {
            self::$static_methods[static::class] = array_merge(static::$static_methods[static::class], $key);
        } else {
            self::$static_methods[static::class][$key] = $value;
        }
    }
    public static function GetExtendStaticStaticMethodList()
    {
        self::$static_methods[static::class] = self::$static_methods[static::class] ?? [];
        return self::$static_methods[static::class];
    }
    protected static function CallExtendStaticMethod($name, $arguments)
    {
        self::$static_methods[static::class] = self::$static_methods[static::class] ?? [];
        $callback = (self::$static_methods[static::class][$name]) ?? null;
        
        if (!\is_callable($callback)) {
            if (is_array($callback) && is_string($callback[0]) && substr($callback[0], -3) === '::G') {
                $class = substr($callback[0], 0, -3);
                $object = $class::G();
                $callback = [$object,$callback[1]];
                if (!\is_callable($callback)) {
                    throw new \BadMethodCallException("Call to undefined static method ".static::class ."::$name()");
                }
            } elseif (is_string($callback)) {
                throw new \BadMethodCallException("Call to undefined static method ".static::class ."::$name()");
            } else {
                throw new \BadMethodCallException("Call to undefined static method ".static::class ."::$name()");
            }
        }
        return call_user_func_array($callback, $arguments);
    }
    public static function __callStatic($name, $arguments)
    {
        return static::CallExtendStaticMethod($name, $arguments);
    }
}
