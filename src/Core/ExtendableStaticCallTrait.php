<?php declare(strict_types=1);
/**
 * DuckPhp
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
    public static function GetExtendStaticMethodList()
    {
        self::$static_methods[static::class] = self::$static_methods[static::class] ?? [];
        return self::$static_methods[static::class];
    }
    protected static function CallExtendStaticMethod($name, $arguments)
    {
        self::$static_methods[static::class] = self::$static_methods[static::class] ?? [];
        
        $callback = (self::$static_methods[static::class][$name]) ?? null;
        
        if (is_string($callback) && !\is_callable($callback)) {
            if (false !== strpos($callback, '@')) {
                list($class, $method) = explode('@', $callback);
                /** @var callable */ $callback = [$class::G(), $method];
            } elseif (false !== strpos($callback, '->')) {
                list($class, $method) = explode('->', $callback);
                /** @var callable */ $callback = [ new $class(), $method];
            }
        }
        
        return call_user_func_array($callback, $arguments);
    }
    public static function __callStatic($name, $arguments)
    {
        return static::CallExtendStaticMethod($name, $arguments);
    }
}
