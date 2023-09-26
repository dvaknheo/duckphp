<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\Route;

trait SimpleControllerTrait
{
    public static function G($class = null)
    {
        if ($class) {
            Route::G()->replaceController(static::class, $class);
        }
        return static::class;
    }
}
