<?php
namespace DNMVCS\Ext;

use DNMVCS\SingletonEx;

class FacadesBase
{
    use SingletonEx;
    
    public static function __callStatic($name, $arguments)
    {
        $callback=FacadesAutoLoader::G()->getFacadesCallback(static::class, $name);
        $ret=call_user_func_array($callback, $arguments);
        return $ret;
    }
}

class FacadesAutoLoader
{
    use SingletonEx;

    protected $prefix='';
    protected $facades_map=[];
    
    protected $is_loaded=false;
    protected $is_inited=false;
    
    public function init($options=[], $context)
    {
        $namespace_facades=$options['facades_namespace']??'Facades';
        $this->facades_map=$options['facades_map']??[];
        
        
        $this->prefix=trim($namespace_facades, '\\').'\\';
        $this->is_inited=true;
        spl_autoload_register([$this,'_autoload']);
        
        return $this;
    }
    
    public function _autoload($class)
    {
        if (substr($class, 0, strlen($this->prefix))!==$this->prefix) {
            return;
        }
        
        $blocks=explode('\\', $class);
        $basename=array_pop($blocks);
        $namespace=implode('\\', $blocks);
        
        $code="namespace $namespace{ class $basename extends \\". __NAMESPACE__  ."\\FacadesBase{} }";
        eval($code);
    }
    public function getFacadesCallback($class, $name)
    {
        $class=substr($class, strlen($this->prefix));
        if (!empty($this->facades_map) && !class_exists($class)) {
            foreach ($this->facades_map as $k=>$v) {
                if ($k===$class) {
                    $class=$v;
                    break;
                }
            }
        }
        $object=call_user_func([$class,'G']);
        return [$object,$name];
    }
}
