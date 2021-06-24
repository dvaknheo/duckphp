<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\DuckPhp;
//use DuckPhp\Component\AppPluginTrait;

class App extends DuckPhp
{
    //use AppPluginTrait;

    //@override
    public $options = [
        'use_setting_file' => true,
        //'error_404' => '_sys/error_404',
        //'error_500' => '_sys/error_500',
        
        //'path_info_compact_enable' => false,        
    ];
    /**
     * console command sample
     */
    public function command_hello()
    {
        // 多一个 hello 的命令
        echo "override you the routes\n";
    }
    //@override
    protected function onPrepare()
    {
        //your code here
    }
    //@override
    protected function onInit()
    {
        // your code here
    }
    //@override
    protected function onRun()
    {
        // your code here
    }
    public function __construct()
    {
        parent::__construct();
        $options = [];

        $this->options = array_replace_recursive($this->options, $options);
    }
}
