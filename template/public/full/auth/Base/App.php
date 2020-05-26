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
    
    //@override
    protected $options_project = [
        'path_config' => 'auth/config',
        'path_view' => 'auth/view',
    ];
    //@override
    protected $plugin_options_project = [
    ];
    //@override
    public function onInit()
    {
    }
    //@override
    protected function onRun()
    {
    }
	//@override
    protected function onPluginModeInit()
    {
        //your code here
    }
    //@override
	protected function onPluginModeRun()
    {
        //your code here
    }
}
