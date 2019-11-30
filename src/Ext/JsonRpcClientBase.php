<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;

class JsonRpcClientBase
{
    use SingletonEx;
    public $_base_class=null;

    
    public function __construct()
    {
    }
    public function __call($method, $arguments)
    {
        $this->_base_class=$this->_base_class?$this->_base_class:JsonRpcExt::G()->getRealClass($this);
        $ret=JsonRpcExt::G()->callRPC($this->_base_class, $method, $arguments);
        return $ret;
    }
}
