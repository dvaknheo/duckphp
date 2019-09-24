<?php
namespace DNMVCS\Helper;

use DNMVCS\Core\Helper\ControllerHelper as Helper;
use DNMVCS\Core\App as App;

class ControllerHelper extends Helper
{
    public static function Import($file)
    {
        return App::G()::Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return App::G()::RecordsetUrl($data, $cols_map);
    }
    public static function RecordsetH(&$data, $cols=[])
    {
        return App::G()::RecordsetH($data, $cols);
    }
    ///////
    public static function Pager()
    {
        return App::G()::Pager();
    }
    ////
    public static function MapToService($serviceClass, $input)
    {
        return App::G()::MapToService($serviceClass, $input);
    }
    //TODO
    public static function explodeService($object, $namespace="MY\\Service\\")
    {
        return App::G()::explodeService($object, $namespace);
    }
}