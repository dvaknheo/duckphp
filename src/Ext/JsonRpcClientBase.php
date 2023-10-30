<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Ext\JsonRpcExt;

class JsonRpcClientBase extends ComponentBase
{
    protected $_base_class = null;

    public function __construct()
    {
    }
    public function setJsonRpcClientBase($class)
    {
        $this->_base_class = $class;
        return $this;
    }
    public function __call($method, $arguments)
    {
        $this->_base_class = $this->_base_class?$this->_base_class:JsonRpcExt::_()->getRealClass($this);
        $ret = JsonRpcExt::_()->callRPC($this->_base_class, $method, $arguments);
        return $ret;
    }
    public function init(array $options, ?object $context = null)
    {
        if ($this->_base_class) {
            JsonRpcExt::_()->callRPC($this->_base_class, __FUNCTION__, func_get_args());
        }
        return parent::init($options, $context);
    }
    public function isInited(): bool
    {
        if ($this->_base_class) {
            JsonRpcExt::_()->callRPC($this->_base_class, __FUNCTION__, func_get_args());
        }
        return parent::isInited();
    }
}
