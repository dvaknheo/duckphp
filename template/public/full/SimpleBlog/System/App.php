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
        'is_debug'=>true,
        'use_setting_file' => true,
        'helper_map' =>  '~\\Helper\\',
        'error_404' =>'_sys/error-404',
        'error_500' => '_sys/error-exception',

        'ext' => [
            'DuckPhp\\Ext\\RouteHookRewrite' => true,
            'DuckPhp\\Ext\\Misc' => true,
            'SimpleAuth\\Base\\App' => [
            ],
        ],

        'rewrite_map' => [
            '~article/(\d+)/?(\d+)?' => 'article?id=$1&page=$2',
        ],
    ];
    
    protected function onPrepare()
    {
        $path = realpath($this->options['path'].'../SimpleAuth/');
        $this->assignPathNamespace($path, 'SimpleAuth');
    }
    protected function onInit()
    {
        static::assignRoute([
            '^abc(\d*)' => function () {
                var_dump("OK");
            },
        ]);
    }
}
