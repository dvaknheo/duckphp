<?php
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\ThrowOn;
use DNMVCS\Core\Configer;
use DNMVCS\Core\App;

class ServiceHelper
{
    use SingletonEx;
    use ThrowOn;
    
    public static function Setting($key)
    {
        return Configer::G()->_Setting($key);
    }
    public static function Config($key, $file_basename='config')
    {
        return Configer::G()->_Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return Configer::G()->_LoadConfig($file_basename);
    }
    
    public static function IsDebug()
    {
        return App::G()->is_debug;
    }
    public static function Platform()
    {
        return App::G()->platform;
    }
    ////
}
