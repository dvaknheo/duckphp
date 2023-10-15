<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Foundation;

use DuckPhp\Core\Route;

trait SimpleControllerTrait
{
    public static function ReplaceTo($class = null)
    {
        if ($class) {
            Route::G()->replaceController(static::class, $class);
        }
        return static::class;
    }
    //public function __construct()
    //{
    //    if(self::class == static::class){ return;}
    //}
}
