<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Core\App;
use DuckPhp\Core\ExtendableStaticCallTrait;
use DuckPhp\Core\SingletonEx;

trait HelperTrait
{
    use SingletonEx;
    use ExtendableStaticCallTrait;

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
    public static function Logger($object = null)
    {
        return App::Logger($object);
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
    public static function ThrowOn($flag, $message, $code = 0, $exception_class = null)
    {
        return App::G()->_ThrowOn($flag, $message, $code, $exception_class);
    }
}
