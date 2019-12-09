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
        return parent::onRun();
    }
    protected function runAsPlugin()
    {
        DuckPhp_App::G()->options['error_404']=null;
        DuckPhp_App::G()->options['error_500']=null;
        DuckPhp_App::G()->options['error_exception']=null;
        DuckPhp_App::G()->options['error_debug']=null;//'_sys/error_debug';
    }
}
