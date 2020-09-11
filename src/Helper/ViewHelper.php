<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Core\App;
use DuckPhp\Helper\HelperTrait;

class ViewHelper
{
    use HelperTrait;
    
    public static function H($str)
    {
        return App::H($str);
    }
    public static function L($str, $args = [])
    {
        return App::L($str, $args);
    }
    public static function Hl($str, $args = [])
    {
        return App::Hl($str, $args);
    }
    public static function Display($view, $data = null)
    {
        return App::Display($view, $data);
    }
    public static function Url($url)
    {
        return App::Url($url);
    }
    public static function Domain()
    {
        return App::Domain();
    }
}
