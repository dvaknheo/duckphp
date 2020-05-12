<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Core;

use Throwable;
use Exception;

trait ThrowOn
{
    public static function ThrowOn($flag, $message, $code = 0, $exception_class = null)
    {
        if (!$flag) {
            return;
        }
        if ($exception_class === null && is_string($code) && class_exists($code)) {
            $exception_class = $code;
            $code = 0;
        }
        $exception_class = $exception_class?:(is_subclass_of(static::class, Exception::class)?static::class:Exception::class);
        throw new $exception_class($message, $code);
    }
}
