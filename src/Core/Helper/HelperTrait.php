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
    public static function Platform()
    {
        return App::Platform();
    }
    public static function DumpTrace()
    {
        return App::DumpTrace();
    }
    public static function var_dump(...$args)
    {
        return App::var_dump(...$args);
    }
}
