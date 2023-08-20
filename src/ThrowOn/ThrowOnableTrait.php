<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\ThrowOn;

trait ThrowOnableTrait
{
    protected $exception_class;
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) {
            return;
        }
        $exception_class = static::G()->exception_class;
        $exception_class = $exception_class ?? \Exception::class;
        throw new $exception_class($message, $code);
    }
    public static function ExceptionClass($new_class = null)
    {
        if ($new_class) {
            static::G()->exception_class = $new_class;
        }
        return static::G()->exception_class;
    }
}
