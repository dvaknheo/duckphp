<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\RouteHookRewrite;
use SimpleAuth\Api\SimpleAuthPlugin;

class App extends DuckPhp
{
    //@override
    public $options = [
        'use_setting_file' => true, // 启用设置文件
        'setting_file_ignore_exists' => true, // 忽略设置文件
        
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        
        'simple_blog_check_installed' => true,
        'simple_blog_table_prefix' => '',
        'simple_blog_session_prefix' => '',
        
        'ext' => [
            RouteHookRewrite::class => true,    // 我们需要 重写 url
            SimpleAuthPlugin::class => [
                'simple_auth_check_installed' => true,  //       // 使用第三方的验证登录包
                'simple_auth_table_prefix' => 'SimpleAuth',
                'simple_auth_session_prefix' => '',
            ], 
        ],
        
        //url 重写
        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
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
    ////////////////////////////
    
    public function command_reset_password()
    {
        $new_pass = AdminBusiness::G()->reset();
        echo 'new password: '.$new_pass;
        echo PHP_EOL;
    }
    public function command_install()
    {
        echo "Welcome to Use SimpleBlog installer  --force  to force install\n";
        $parameters =  static::Parameter();
        if(count($parameters)==1 || ($parameters['help'] ?? null)){
            // echo "--force  to force install ;";
            //return;
        }

        Installer::G()->init($options,$this);
        
        if(Installer::G()->isInstalled()){
           echo "You had been installed ";
           return; 
        }
        echo Installer::G()->run();        
        echo "Done \n";
    }
    public function install($database)
    {
        //TODO 我们先检查子系统安装。
        
        $options = [
            'force' => $parameters['force']?? false,
            'path' => $this->getPath(),
            'path_sql_dump' => 'config',
        ];
        return Installer::G()->install($database);
    }
    ///////////////////////
    protected function getPath()
    {
        return $this->options['path'];
    }
    public function getTablePrefix()
    {
        return $this->options['simple_blog_table_prefix'];
    }
    public function getSessionPrefix()
    {
        return $this->options['simple_blog_session_prefix'];
    }
}
