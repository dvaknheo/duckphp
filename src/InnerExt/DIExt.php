<?php
namespace DNMVCS\InnerExt;

use DNMVCS\Basic\SingletonEx;

class DIExt
{
    use SingletonEx;

    protected $_di_container;
    public static function DI($name, $object=null)
    {
        return static::G()->_DI($name, $object);
    }
    public function _DI($name, $object=null)
    {
        if (null===$object) {
            return $this->_di_container[$name];
        }
        $this->_di_container[$name]=$object;
        return $object;
    }
    ////////////
    public function init($options=[], $context=null)
    {
        if ($context) {
            $this->initContext($options, $context);
        }
        return $this;
    }
    protected function initContext($options=[], $context=null)
    {
        $context->assignStaticMethod('DI', [static::class,'DI']);
    }
}
