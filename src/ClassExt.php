<?php
namespace DNMVCS;

trait ClassExt
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
    public static function DumpExtMethods($return=false)
    {
        return static::G()->_dumpExtMethods($return);
    }
    protected function get_callback_dump_desc($callback)
    {
        do {
            if (is_array($callback)) {
                $method=$callback[1]??'';
                $method=$callback[1]??'';
                if (is_object($callback[0])) {
                    $class=get_class($callback[0]);
                    $callback_desc="{$class}->{$method}";
                    break;
                }
                if (is_string($callback[0])) {
                    if (substr($callback[0], -3)==='::G') {
                        $class=substr($callback[0], 0, -3);
                        $object=$class::G();
                        $real_class=get_class($object);
                        $real_class=$real_class===$class?'':$real_class;
                        $callback_desc="{$class}::G($real_class)->{$method}";
                        ; // DNMVCS::G()->foo;
                        break;
                    }
                    $class=$callback[0]??'';
                    $callback_desc="{$class}::{$method}";
                    break;
                }
                $callback_desc=var_export($callback, true);
                break;
            }
            if (is_ojbect($callback)) {
                $callback_desc="object(".get_class($callback).")";
                break;
            }
            $callback_desc=var_export($callback, true);
        } while (false);
        return $callback_desc;
    }
    public function _dumpExtMethods($return=false)
    {
        $class=get_class($this);
        $ret="\n<pre>\nDumpExtMethods: ($class) [[[[\n";
        $ret.="---- static ----\n";

        foreach ($this->static_methods as $name=>$callback) {
            $callback_desc=$this->get_callback_dump_desc($callback);
            $ret.="{$name} => {$callback_desc}\n";
        }
        $ret.="---- dynamic ----\n";
        foreach ($this->dynamic_methods as $name=>$callback) {
            $callback_desc=$this->get_callback_dump_desc($callback);
            $ret.="{$name} => {$callback_desc}\n";
        }
        $ret.="]]]]</pre>\n";
        if (!$return) {
            echo $ret;
        }
        return $ret;
    }
}
