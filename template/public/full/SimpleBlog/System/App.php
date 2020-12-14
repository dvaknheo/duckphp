<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\DuckPhp;
use DuckPhp\Ext\RouteHookRewrite;
use DuckPhp\Ext\Misc;
use SimpleAuth\Base\App as SimpleAuthApp;
use DuckPhp\Ext\DbManager;

class App extends DuckPhp
{
    //@override
    public $options = [
        'use_setting_file' => true, // 启用设置文件
        
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
        $path = realpath($this->options['path'].'../SimpleAuth/');
        $this->assignPathNamespace($path, 'SimpleAuth');
    }
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
        
        $file = $path.'setting.php'; // 这里
        $data = '<'.'?php ';
        $data .="\n // gen by ".static::class.' '.date(DATE_ATOM) ." \n";
        $data .= ' return ';
        $data .= var_export($setting,true);
        $data .=';';
        return @file_put_contents($file,$data);
    }
}
