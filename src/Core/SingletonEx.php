<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

trait SingletonEx
{
    protected static $_instances = [];
    public static function G($object = null)
    {
        if (defined('DuckPhp_SINGLETONEX_REPALACER')) {
            $callback = DuckPhp_SINGLETONEX_REPALACER;
            return ($callback)(static::class, $object);
        }
        //fwrite(STDOUT,"SINGLETON ". static::class ."\n");
        if ($object) {
            self::$_instances[static::class] = $object;
            return $object;
        }
        $me = self::$_instances[static::class] ?? null;
        if (null === $me) {
            $me = new static();
            self::$_instances[static::class] = $me;
        }
        return $me;
        // Bug static $_instance;
        // Bug $_instance=$object?:($_instance??new static);
        // Bug return $_instance;
    }
}
