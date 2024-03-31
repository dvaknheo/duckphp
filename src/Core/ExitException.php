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
        if (!defined('DUCKPHP_EXIT_EXCEPTION')) {
            define('DUCKPHP_EXIT_EXCEPTION', static::class);
        }
    }
}
