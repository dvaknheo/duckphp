<?php
namespace DNMVCS\Core\Base;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Glue\GlueThrowOn;
use DNMVCS\Glue\GlueConfiger;

class ServiceHelper
{
    use SingletonEx;
    use GlueThrowOn;
    use GlueConfiger;
    
    public static function IsDebug()
    {
        return App::G()->is_debug;
    }
    public static function Platform()
    {
        return App::G()->platform;
    }
}
