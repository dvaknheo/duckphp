<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Helper;

use DuckPhp\Helper\HelperTrait;
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
    public static function Display($view, $data = null)
    {
        return App::Display($view, $data);
    }
    public static function URL($url)
    {
        return App::URL($url);
    }
    public static function Domain()
    {
        return App::Domain();
    }
}
