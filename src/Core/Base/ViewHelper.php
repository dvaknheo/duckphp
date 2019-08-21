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
    public static function ShowBlock()
    {
        return App::G()->_ShowBlock($view, $data);
    }

}
