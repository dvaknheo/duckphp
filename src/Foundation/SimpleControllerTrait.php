<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\Route;

trait SimpleControllerTrait
{
    public static function _($object = null)
    {
        if ($object) {
            Route::G()->replaceController(static::class, get_class($object));
            return $object;
        } else {
            $class = Route::G()->options[static::class] ?? static::class;
            $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            return $object;
        }
    }
}
