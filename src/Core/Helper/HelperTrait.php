<?php declare(strict_types=1);
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\ExtendableStaticCallTrait;
use DNMVCS\Core\ThrowOn;
use DNMVCS\Core\App;

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
