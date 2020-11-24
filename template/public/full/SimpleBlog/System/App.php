<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'is_debug'=>true,   //启用调试模式
        'use_setting_file' => true, // 启用设置文件
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',
        'injected_helper_map' =>  '~\\Helper\\',  // 打开助手类注入模式

        'ext' => [
            'DuckPhp\\Ext\\RouteHookRewrite' => true, // 我们需要 重写 url
            'DuckPhp\\Ext\\Misc' => true, //我们需要两个助手函数
            'SimpleAuth\\Base\\App' => [
            ],
        ],

        //url 重写
        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
    ];
    
    protected function onPrepare()
    {
        // 我们要引入第三方包
        $path = realpath($this->options['path'].'../SimpleAuth/');
        $this->assignPathNamespace($path, 'SimpleAuth');
    }
    protected function onInit()
    {
        //另一种 url 重写的
        static::assignRoute([
            '^abc(\d*)' => function () {
                var_dump("OK");
            },
        ]);
    }
}
