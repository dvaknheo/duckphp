<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

trait ThrowOnableTrait
{
    protected $exception_class = 'Exception';
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) {
            return;
        }
        $exception_class = static::G()->exception_class;
        throw new $exception_class($message, $code);
    }
    public static function ExceptionClass()
    {
        return static::G()->exception_class;
    }
    public function setExceptionClass($exception_class)
    {
        $this->exception_class = $exception_class;
    }
}
