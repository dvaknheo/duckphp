<?php
namespace DNMVCS\Core;

trait ExtendableStaticCallTrait
{
    protected static $static_methods=[];
    
    public static function AssignExtendStaticMethod($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            static::$static_methods=array_merge(static::$static_methods, $key);
        } else {
            static::$static_methods[$key]=$value;
        }
    }
    public static function GetExtendStaticStaticMethodList()
    {
        return static::$static_methods;
    }
    protected static function CallExtendStaticMethod($name, $arguments)
    {
        $callback=(static::$static_methods[$name])??null;
        
        if (!is_callable($callback)) {
            if (is_array($callback) && is_string($callback[0]) && substr($callback[0], -3)==='::G') {
                $class=substr($callback[0], 0, -3);
                $object=$class::G();
                $callback=[$object,$callback[1]];
                if (!is_callable($callback)) {
                    throw new \BadMethodCallException("Call to undefined static method ".static::class ."::$name()");
                }
            } else {
                throw new \BadMethodCallException("Call to undefined static method ".static::class ."::$name()");
            }
        }
        return call_user_func_array($callback, $arguments);
    }
    public static function __callStatic($name, $arguments)
    {
        return static::CallExtendStaticMethod($name, $arguments);
    }
}
