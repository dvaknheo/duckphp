<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        //'path_info_compact_enable' => false,
        'controller_class_postfix' => 'Controller',
    ];
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello\n";
    }
}
