<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Component\CallInPhaseTrait;
use DuckPhp\Core\Route;

trait SimpleControllerTrait
{
    use CallInPhaseTrait;

    public static function _($object = null)
    {
        if ($object) {
            Route::_()->replaceController(static::class, get_class($object));
            return $object;
        } else {
            $class = Route::_()->options['controller_class_map'][static::class] ?? static::class;
            $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            return $object;
        }
    }
}
