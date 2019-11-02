<?php
namespace DNMVCS\Core;

use Throwable;
use Exception;

trait ThrowOn
{
    public static function ThrowOn($flag, $message, $code=0, $exception_class=null)
    {
        if (!$flag) {
            return;
        }
        if ($exception_class===null && is_string($code)) {
            $exception_class=$code;
            $code=0;
        }
        $exception_class=$exception_class?:(is_a(static::class,Throwable::class)?static::class:Exception::class);
        throw new $exception_class($message, $code);
    }
}
