<?php
namespace DNMVCS;

class FacadesAutoLoader
{
    use DNSingleton;

    protected $prefix='';
    protected $facades_map=[];
    
    protected $is_loaded=false;
    protected $is_inited=false;
    
    public function init($namespace_facades='', $facades_map=[], $namespace='')
    {
        if (substr($namespace_facades, 0, 1)!=='\\') {
            $namespace_facades=$namespace.'\\'.$namespace_facades;
        }
        $namespace_facades=ltrim($namespace_facades, '\\');
        $this->prefix=$namespace_facades.'\\Facade\\';
        
        $this->is_init=true;
        return $this;
    }
    public function run()
    {
        if ($this->is_loaded) {
            return;
        }
        $this->is_loaded=true;
        spl_autoload_register([$this,'_autoload']);
    }
    
    public function _autoload($class)
    {
        if (substr($class, 0, strlen($this->prefix))!==$this->prefix) {
            return;
        }
        
        $blocks=explode('\\', $class);
        $basename=array_pop($blocks);
        $namespace=implode('\\', $blocks);
        
        $code="namespace $namespace{ class $basename extends \\DNMVCS\\FacadesBase{} }";
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
