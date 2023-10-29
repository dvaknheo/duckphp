<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

trait ThrowOnTrait
{
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) {
            return;
        }
        throw new static($message, $code);
    }
}
