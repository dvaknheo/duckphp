<?php
namespace Project\Base;

use DuckPhp\App as DuckPhp_App;
use DuckPhp\Core\AppPluginTrait;

class App extends DuckPhp_App
{
    use AppPluginTrait;
    
    public function onInit()
    {
        //Your code here
        return parent::onInit();
    }
    protected function onRun()
    {
        static::session_start();
        return parent::onRun();
    }
}
