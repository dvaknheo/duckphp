<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\DuckPhp;
use DuckPhp\Component\AppPluginTrait;

class App extends DuckPhp
{
    protected $is_plugin = false;
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
    //
    public function __construct()
    {
        $this->plugin_options['plugin_path']=__DIR__.'/../';
        parent::__construct();
    }
    protected function onPluginModePrepare()
    {
        $this->is_plugin = true;
    }
//////////////////////
    public static function SessionManager()
    {
        return SessionManager::G();
    }
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
