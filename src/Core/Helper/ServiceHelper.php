<?php
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\ExtendableStaticCallTrait;
use DNMVCS\Core\ThrowOn;
use DNMVCS\Core\App;

class ServiceHelper
{
    use ExtendableStaticCallTrait;
    use ThrowOn;
    
    public static function Setting($key)
    {
        return App::Setting($key);
    }
    public static function Config($key, $file_basename='config')
    {
        return App::Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return App::LoadConfig($file_basename);
    }
    
    public static function IsDebug()
    {
        return App::IsDebug();
    }
    public static function Platform()
    {
        return App::Platform();
    }
    ////
}
