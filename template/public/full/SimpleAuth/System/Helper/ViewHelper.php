<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System\Helper;

use DuckPhp\Helper\ViewHelper as Helper;

class ViewHelper extends Helper
{
    public static $Sections = [];
    public static function startSection($name)
    {
        ob_start(function ($str) use ($name) {
            if (!isset(static::$Sections[$name])) {
                static::$Sections[$name] = '';
            }
            static::$Sections[$name] .= $str;
        });
    }
    public static function stopSection()
    {
        ob_end_flush();
    }
    public static function yieldContent($name)
    {
        if (isset(static::$Sections[$name])) {
            return static::$Sections[$name];
        }
        return '';
    }
}
