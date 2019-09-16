<?php
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\App;
use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\ThrowOn;

use DNMVCS\Core\View;

class ViewHelper
{
    use SingletonEx;
    use ThrowOn;
    
    public static function IsDebug()
    {
        return App::G()->is_debug;
    }
    public static function Platform()
    {
        return App::G()->platform;
    }
    public static function ShowBlock($view, $data=null)
    {
        return View::G()->_ShowBlock($view, $data);
    }
    public static function DumpTrace()
    {
        return App::G()->_DumpTrace();
    }
    public static function Dump(...$args)
    {
        return App::G()->_Dump(...$args);
    }
}
