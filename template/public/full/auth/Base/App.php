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
    
    public function onInit()
    {
        return parent::onInit();
    }
    protected function onRun()
    {
        return parent::onRun();
    }
	////
    protected function onPluginInit()
    {
        return parent::onPluginInit();
    }
	protected function onPluginModeRun()
		{
		}
}
