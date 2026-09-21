<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com

namespace DuckPhp\Foundation;

class Helper
{
    public static function __callStatic($method, $args)
    {
        $classes = [
            \DuckPhp\Foundation\System\Helper::class,
            \DuckPhp\Foundation\Controller\Helper::class,
            \DuckPhp\Foundation\Business\Helper::class,
            \DuckPhp\Foundation\Model\Helper::class,
        ];
        foreach($classes as $class){
            if (method_exists($class, $method)) {
                return $class::$method(...$args);
            }
        }
        trigger_error("Call to undefined method " . static::class . "::$method()", E_USER_ERROR);
    }
}
