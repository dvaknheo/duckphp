<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core\Helper;

use DuckPhp\Core\ExtendableStaticCallTrait;
use DuckPhp\Core\ThrowOn;
use DuckPhp\Core\App;

trait HelperTrait
{
    use ExtendableStaticCallTrait;
    use ThrowOn;

    public static function IsDebug()
    {
        return App::IsDebug();
    }
    public static function IsRealDebug()
    {
        return App::IsRealDebug();
    }
    public static function Platform()
    {
        return App::Platform();
    }
    public static function Logger()
    {
        return App::Logger();
    }
    ////
    public static function trace_dump()
    {
        return App::trace_dump();
    }
    public static function var_dump(...$args)
    {
        return App::var_dump(...$args);
    }
}
