<?php
namespace MY\Base;

use DNMVCS\DNMVCS;

class App extends DNMVCS
{
    public function init($options=[], $context=null)
    {
        parent::init($options, $context);
        //Your code here
        return $this;
    }
    protected function onRun()
    {
        //Your code here
        return parent::run();
    }
}
