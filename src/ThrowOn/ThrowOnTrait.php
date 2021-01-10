<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\ThrowOn;

use Exception;
use Throwable;

trait ThrowOnTrait
{
    public static $To;
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) {
            return;
        }
        if (empty(static::$To) || empty(static::$To[static::class])) {
            throw new static($message, $code);
        } else {
            $exception_class = static::$To[static::class];
            throw new $exception_class($message, $code);
        }
    }
    public static function Handle($class)
    {
        $class::$To[$class] = static::class;
    }
    public static function Proxy($ex)
    {
        throw new static($ex->getMessage(), $ex->getCode());
    }
}
