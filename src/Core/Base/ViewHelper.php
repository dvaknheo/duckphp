<?php
namespace DNMVCS\Core\Base;

use DNMVCS\Core\App;

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
        return App::G()->_ShowBlock($view, $data);
    }
    public static function DumpTrace()
    {
        echo "<pre>\n";
        echo (new Exception('',0))->getTraceString();
        echo "</pre>\n";
    }
    public static function Dump($object)
    {
        return App::G()->_Dump($object);
    }
}
