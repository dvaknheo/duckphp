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
    public static function ShowBlock($view, $data=null)
    {
        return App::ShowBlock($view, $data);
    }
}
