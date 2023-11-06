<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        //'is_debug' => true, // debug switch
        //'path_info_compact_enable' => false,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'controller_class_postfix' => 'Controller', // in common, use this
        
    ];
    //@override
    protected function onInit()
    {
        // your code here
    }
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello\n";
    }
}
