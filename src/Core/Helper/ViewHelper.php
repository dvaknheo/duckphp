<?php
namespace DNMVCS\Core\Helper;

use DNMVCS\Core\Helper\HelperTrait;
use DNMVCS\Core\App;

class ViewHelper
{
    use HelperTrait;
    
    public static function H($str)
    {
        return App::H($str);
    }
    public static function L($str, $args=[])
    {
        return App::L($str, $args);
    }
    public static function HL($str, $args=[])
    {
        return App::HL($str, $args);
    }
    public static function ShowBlock($view, $data=null)
    {
        return App::ShowBlock($view, $data);
    }
}
