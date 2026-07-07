<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace YourProjectName\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'path' => __DIR__ . '/../../',
        //'path_info_compact_enable' => false,
        
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        
        'controller_method_prefix' => 'action_', // maybe next version  default value is  ''
        //...
    ];
    //@override
    protected function onInited()
    {
        parent::onInited();
        // your code here
    }
}
