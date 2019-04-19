<?php
namespace DNMVCS;

trait DNClassExt
{
    protected $static_methods=[];
    protected $dynamic_methods=[];
    
    public static function __callStatic($name, $arguments)
    {
        $static=static::G();
        $class=get_class($static);
        if ($class!==static::class && method_exists($class, $name)) {
            return call_user_func_array([$class,$name], $arguments);
        }
        ///////
        $callback=$static->static_methods[$name]??null;
        if (is_array($callback) && is_string($callback[0]) && substr($callback[0], -3)==='::G') {
            $class=substr($callback[0], 0, -3);
            $object=$class::G();
            $callback=[$object,$callback[1]];
        }
        if (!is_callable($callback)) {
            throw new \BadMethodCallException("Call to undefined static method ".static::class ."::$name()");
        }
        return call_user_func_array($callback, $arguments);
    }
    public function __call($name, $arguments)
    {
        $callback=$this->dynamic_methods[$name]??null;
        if (is_array($callback) && is_string($callback[0]) && substr($callback[0], -3)==='::G') {
            $class=substr($callback[0], 0, -3);
            $object=$class::G();
            $callback=[$object,$callback[1]];
        }
        if (!is_callable($callback)) {
            throw new \BadMethodCallException("Call to undefined dynamic method ".static::class ."::$name()");
        }
        return call_user_func_array($callback, $arguments);
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
    public function extendClassMethodByThirdParty($class, array $StaticMethodList, array $DynamicMethodList=[])
    {
        $object=$class.'::G';
        $methods=[];
        foreach ($StaticMethodList as $method) {
            $methods[$method]=[$class,$method];
        }
        $this->assignStaticMethod($methods);
        ////
        $methods=[];
        foreach ($DynamicMethodList as $method) {
            $methods[$method]=[$object,$method];
        }
        $this->assignDynamicMethod($methods);
    }
    public function dumpExtMethods()
    {
        $ret=[
            'static_methods'=>$this->static_methods,
            'dynamic_methods'=>$this->dynamic_methods,
       ];
        return $ret;
    }
}
