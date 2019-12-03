<?php
namespace Project\Base;

use DNMVCS\DNMVCS as DN;

class App extends DN
{
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
