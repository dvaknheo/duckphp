<?php
namespace SwooleHttpd;

trait SwooleHttpd_SuperGlobal
{
    public static function SG()
    {
        return SwooleSuperGlobal::G();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return SwooleSuperGlobal::G()->_GLOBALS($k, $v);
    }
    public static function &STATICS($k, $v=null)
    {
        return SwooleSuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return SwooleSuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
}
