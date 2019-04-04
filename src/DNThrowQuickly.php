<?php
namespace DNMVCS;

trait DNThrowQuickly
{
    public static function ThrowOn($flag, $message, $code=0)
    {
        if (!$flag) {
            return;
        }
        $class=static::class;
        throw new $class($message, $code);
    }
}
