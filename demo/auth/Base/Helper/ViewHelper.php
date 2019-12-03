<?php
namespace Project\Base\Helper;

use DNMVCS\Helper\ViewHelper as Helper;

class ViewHelper extends Helper
{
    public static function HL($str,$args=[])
    {
        return static::H($str);
    }
    static $Sections=[];
    public static function startSection($name)
    {
        ob_start(function($str)use($name){
            if(!isset(static::$Sections[$name])){
                static::$Sections[$name]='';
            }
            static::$Sections[$name].=$str;
        });
    }
    public static function stopSection()
    {
        ob_end_flush();
        
    }
    public static function yieldContent($name)
    {
        if(isset(static::$Sections[$name])){
            return static::$Sections[$name];
        }
        return '';
    }
}
