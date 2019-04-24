<?php
namespace DNMVCS\Ext;

use DNMVCS\Basic\SingletonEx;

class FacadesAutoLoader
{
    use SingletonEx;

    protected $prefix='';
    protected $facades_map=[];
    
    protected $is_loaded=false;
    protected $is_inited=false;
    
    public function init($options=[], $context)
    {
        $namespace='';
        if ($context) {
            $namespace=$dn->options['namespace'];
        }
        $namespace_facades=$options['facades_namespace'];
        $facades_namespace=$options['facades_map'];
        
        if (substr($namespace_facades, 0, 1)!=='\\') {
            $namespace_facades=$namespace.'\\'.$namespace_facades;
        }
        $namespace_facades=ltrim($namespace_facades, '\\');
        $this->prefix=$namespace_facades.'\\Facade\\';
        
        $this->is_inited=true;
        
        if ($context) {
        }
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
        
        $code="namespace $namespace{ class $basename extends \\DNMVCS\\Ext\\FacadesBase{} }";
        eval($code);
    }
    public function getFacadesCallback($class, $name)
    {
        foreach ($this->facades_map as $k=>$v) {
            if ($k===$class) {
                $class=$v;
                break;
            }
        }
        // DNexception::ThrowOn(!class_exists($class),"No Class");
        $object=call_user_func([$class,'G']);
        return [$object,$name];
    }
}
