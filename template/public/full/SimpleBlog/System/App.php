<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\Misc;
use DuckPhp\Ext\RouteHookRewrite;
use SimpleAuth\System\App as SimpleAuthApp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'use_setting_file' => true, // 启用设置文件
        'setting_file_ignore_exists' => true, // 忽略设置文件
        
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        
        'ext' => [
            RouteHookRewrite::class => true,    // 我们需要 重写 url
            Misc::class => true,                // 我们需要两个助手函数
            //SimpleAuthApp::class => true,       // 使用第三方的验证登录包
        ],
        
        //url 重写
        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
        
        //注入处理
        'injected_helper_map' =>  self::DEFAULT_INJECTED_HELPER_MAP, //'~\\Helper\\',  // 打开助手类注入模式
        'misc_auto_method_extend'=>true,
        'route_map_auto_extend_method'=>true,
    ];
    
    protected function onPrepare()
    {
        // 我们要引入第三方包,这里我们没采用 composer。
        if (!class_exists(SimpleAuthApp::class)) {
            $path = realpath($this->options['path'].'../SimpleAuth/');
            $this->assignPathNamespace($path, 'SimpleAuth');
        }
    }
    protected function onInit()
    {
        // 我们加个检查安装的钩子？
        // 我们从设置里再入第三方验证包吧
        // 我们在每次执行的时候检查 权限，如果没有，那就跳到 安装页面。 $this::Route()->addRouteHook()
    }
    protected function onBeforeRun()
    {
        
        // 如果不是命令行模式
        // 我们在这里检查有没有安装。
    }
    public function command_reset_password()
    {
        $new_pass = AdminBusiness::G()->reset();
        echo 'new password: '.$new_pass;
    }
    public function command_install()
    {
        echo "install";
    }
    public function install($database)
    {
        return Installer::G()->install($database);
    }

}
