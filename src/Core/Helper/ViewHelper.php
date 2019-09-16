<?php
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\App;
use DNMVCS\Core\ThrowOn;

class ViewHelper
{
    use ThrowOn;
    
    public static function IsDebug()
    {
        return App::IsDebug()
    }
    public static function Platform()
    {
        return App::Platform()
    }
    public static function ShowBlock($view, $data=null)
    {
        return App::ShowBlock($view, $data);
    }
    public static function DumpTrace()
    {
        return App::DumpTrace();
    }
    public static function Dump(...$args)
    {
        return App::Dump(...$args);
    }
}
