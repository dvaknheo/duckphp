<?php
namespace MY\Base;

class App extends \DNMVCS\DNMVCS
{
    public function init($options=[], $context=null)
    {
        parent::init($options);
        return $this;
    }
}
