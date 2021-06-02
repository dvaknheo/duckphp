<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\DuckPhp;
use DuckPhp\Component\DbManager;
use DuckPhp\Core\Configer;
use DuckPhp\Ext\Misc;
use DuckPhp\Ext\RouteHookRewrite;
use SimpleAuth\Base\App as SimpleAuthApp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'is_debug' => true,
        'use_setting_file' => true, // 启用设置文件
        'setting_file_ignore_exists' => true, // 忽略设置文件
        
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        'ext' => [
            RouteHookRewrite::class => true,    // 我们需要 重写 url
            Misc::class => true,                // 我们需要两个助手函数
            SimpleAuthApp::class => true,       // 使用第三方的验证登录包
        ],
        
        //注入处理
        'injected_helper_map' =>  self::DEFAULT_INJECTED_HELPER_MAP, //'~\\Helper\\',  // 打开助手类注入模式
        'misc_auto_method_extend'=>true,
        'route_map_auto_extend_method'=>true,
        
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
        //$this->options['is_debug'] = true;
    }
    public function command_reset_password()
    {
        $new_pass = AdminBusiness::G()->reset();
        echo 'new password: '.$new_pass;
    }

    // 这两个流程之外的要放其他地方
    public function CheckDb($setting)
    {
        $options = DbManager::G()->options;
        $options['database']=$setting;
        DbManager::G()->init($options);
    }
    public function writeSettingFile($setting)
    {
        $this->options['path_config'] = $this->options['path_config'] ?? 'config';
        $path = $this->getComponenetPathByKey('path_config');
        $setting_file = $this->options['setting_file'] ?? 'setting';
        $file = $path.$setting_file.'.php';
        
        $data = '<'.'?php ';
        $data .="\n // gen by ".static::class.' '.date(DATE_ATOM) ." \n";
        $data .= ' return ';
        $data .= var_export($setting,true);
        $data .=';';
        return @file_put_contents($file,$data);
    }
}
