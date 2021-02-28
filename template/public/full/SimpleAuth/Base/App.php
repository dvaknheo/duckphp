<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Base;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\AppPluginTrait;

class App extends DuckPhp
{
    use AppPluginTrait;
    
    //@override
    protected $plugin_options = [
    ];
//////////////////////
    //@override
    public $options = [
        'use_setting_file' => true, // 启用设置文件
        
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        'ext' => [
            // TODO 我插我自己，不用你们的插件方式
            //SimpleAuthApp::class => [
            //],
        ],
    ];


//////////////////////
    
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
