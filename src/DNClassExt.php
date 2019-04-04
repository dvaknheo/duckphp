<?php
namespace DNMVCS;

trait DNClassExt
{
    protected $static_methods=[];
    protected $dynamic_methods=[];
    
    public static function __callStatic($name, $arguments)
    {
        $self=static::G();
        $class=get_class($self);
        if ($class!==static::class && method_exists($class, $name)) {
            return call_user_func_array([$class,$name], $arguments);
        }
        if (isset($self->static_methods[$name]) && is_callable($self->static_methods[$name])) {
            return call_user_func_array($self->static_methods[$name], $arguments);
        }
        throw new \Error("Call to undefined method ".static::class ."::$name()");
    }
    public function __call($name, $arguments)
    {
        if (isset($this->dynamic_methods[$name]) && is_callable($this->dynamic_methods[$name])) {
            return call_user_func_array($this->dynamic_methods[$name], $arguments);
        }
        
        throw new \Error("Call to undefined method ".static::class ."::$name()");
    }
    public function assignStaticMethod($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->static_methods=array_merge($this->static_methods, $key);
        } else {
            $this->static_methods[$key]=$value;
        }
    }
    public function assignDynamicMethod($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->dynamic_methods=array_merge($this->dynamic_methods, $key);
        } else {
            $this->dynamic_methods[$key]=$value;
        }
    }
    public function extendClassMethodByThirdParty($object_or_class, array $StaticMethodList, array $DynamicMethodList=[])
    {
        if (is_object($object_or_class)) {
            $class=get_class($object_or_class);
            $object=$object_or_class;
        } else {
            $class=$object_or_class;
            $object=$class::G();
        }
        
        $methods=[];
        foreach ($StaticMethodList as $method) {
            $methods[$method]=[$class,$method];
        }
        $this->assignStaticMethod($methods);
        $methods=[];
        foreach ($DynamicMethodList as $method) {
            $methods[$method]=[$object,$method];
        }
        $this->assignDynamicMethod($methods);
    }
}
