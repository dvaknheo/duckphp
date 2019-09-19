<?php
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
}