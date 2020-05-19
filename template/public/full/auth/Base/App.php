<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UserSystemDemo\Base;

use DuckPhp\App as DuckPhp_App;
use DuckPhp\Core\AppPluginTrait;

class App extends DuckPhp_App
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
}
