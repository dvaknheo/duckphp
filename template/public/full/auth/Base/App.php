<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UserSystemDemo\Base;

use DuckPhp\App as DuckPhpApp;
use DuckPhp\Core\AppPluginTrait;

class App extends DuckPhpApp
{
    use AppPluginTrait;
    
    protected $options_project = [
        'path_config' => 'auth/config',
        'path_view' => 'auth/view',
    ];
    protected $plugin_options_project = [];
    public function onInit()
    {
    }
    protected function onRun()
    {
    }
	////
    protected function onPluginModeInit()
    {
        //your code here
    }
	protected function onPluginModeRun()
    {
        //your code here
    }
}
