<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;

class FacadesAutoLoader
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'facades_namespace'=>'Facades',
        'facades_map'=>[],
    ];
    protected $prefix='';
    protected $facades_map=[];
    
    protected $is_loaded=false;
    protected $is_inited=false;
    
    public function init(array $options=[], $context=null)
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
        $flag=(substr($class, 0, strlen($this->prefix))===$this->prefix)?true:false;
        if (!$flag) {
            $flag=($this->facades_map && in_array($class, array_keys($this->facades_map)))?true:false;
        }
        if (!$flag) {
            return;
        }
        $blocks=explode('\\', $class);
        $basename=array_pop($blocks);
        $namespace=implode('\\', $blocks);
        
        $code="namespace $namespace{ class $basename extends \\". __NAMESPACE__  ."\\FacadesBase{} }";
        eval($code);
    }
    public function getFacadesCallback($input_class, $name)
    {
        $class=null;
        foreach ($this->facades_map as $k=>$v) {
            if ($k===$input_class) {
                $class=$v;
                break;
            }
        }
        if (!$class) {
            if (substr($input_class, 0, strlen($this->prefix))===$this->prefix) {
                $class=substr($input_class, strlen($this->prefix));
            }
        }
        if (!is_callable([$class,'G'])) {
            return null;
        }
        $object=call_user_func([$class,'G']);
        return [$object,$name];
    }
    public function cleanUp()
    {
        spl_autoload_unregister([$this,'_autoload']);
    }
}

