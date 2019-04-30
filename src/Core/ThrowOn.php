<?php
namespace DNMVCS\Core;

trait ThrowOn
{
    public static function ThrowOn($flag, $message, $code=0, $exception_class='')
    {
        if (!$flag) {
            return;
        }
        $exception_class=$exception_class?:\Exception::class;
        throw new $exception_class($message, $code);
    }
}
