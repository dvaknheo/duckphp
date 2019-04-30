<?php
namespace MY\Base;

class App extends \DNMVCS\DNMVCS
{
    public function init($options=[], $context=null)
    {
        parent::init($options, $context);
        return $this;
    }
    public function run()
    {
        return parent::run();
    }
}
