<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Base;

use DuckPhp\DuckPhp;
use DuckPhp\Core\AppPluginTrait;

class App extends DuckPhp
{
    use AppPluginTrait;
    
    //@override
    public $options = [
        'path_config' => 'config',
        'path_view' => 'view',
    ];
    //@override
    protected $plugin_options = [
    ];
    //@override
    protected function onPrepare()
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
