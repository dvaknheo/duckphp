<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core\Helper;

use DuckPhp\Core\Helper\HelperTrait;
use DuckPhp\Core\App;

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
    public static function HL($str, $args = [])
    {
        return App::HL($str, $args);
    }
    public static function ShowBlock($view, $data = null)
    {
        return App::ShowBlock($view, $data);
    }
    public static function URL($url)
    {
        return App::URL($url);
    }
}
