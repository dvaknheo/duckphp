<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use DuckPhp\App as SystemApp;

class App extends SystemApp
{
    //@override
    protected $option_project = [
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'error_debug' =>  '_sys/error_debug',
        
        'is_debug' => true, // @DUCKPHP_DELETE
        'skip_setting_file' => true, // @DUCKPHP_DELETE
    ];
    //@override
    protected function onPrepare()
    {
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
}
