<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

class ExitException extends DuckPhpSystemException
{
    //
    public static function Init()
    {
        if (!defined('__EXIT_EXCEPTION')) {
            define('__EXIT_EXCEPTION', static::class);
        }
    }
}
