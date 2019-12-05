<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;

class FacadesAutoLoader
{
    use SingletonEx;
    
    public $options = [
        'facades_namespace' => 'Facades',
        'facades_map' => [],
        'facades_enable_autoload' => true,
    ];
    protected $prefix = '';
    protected $facades_map = [];
    
    protected $is_loaded = false;
    
    public function __construct()
    {
    }
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        
        $this->facades_map = $this->options['facades_map'] ?? [];
        $namespace_facades = $this->options['facades_namespace'] ?? 'Facades';
        $this->prefix = trim($namespace_facades, '\\').'\\';
        
        if ($this->options['facades_enable_autoload']) {
            spl_autoload_register([$this,'_autoload']);
        }
        
        return $this;
    }
    
    public function _autoload($class)
    {
        $flag = (substr($class, 0, strlen($this->prefix)) === $this->prefix)?true:false;
        if (!$flag) {
            $flag = ($this->facades_map && in_array($class, array_keys($this->facades_map)))?true:false;
        }
        if (!$flag) {
            return;
        }
        $blocks = explode('\\', $class);
        $basename = array_pop($blocks);
        $namespace = implode('\\', $blocks);
        
        $code = "namespace $namespace{ class $basename extends \\". __NAMESPACE__  ."\\FacadesBase{} }";
        eval($code);
    }
    public function getFacadesCallback($input_class, $name)
    {
        $class = null;
        foreach ($this->facades_map as $k => $v) {
            if ($k === $input_class) {
                $class = $v;
                break;
            }
        }
        if (!$class) {
            if (substr($input_class, 0, strlen($this->prefix)) === $this->prefix) {
                $class = substr($input_class, strlen($this->prefix));
            }
        }
        if (!is_callable([$class,'G'])) {
            return null;
        }
        $object = call_user_func([$class,'G']);
        return [$object,$name];
    }
    public function clear()
    {
        $this->facades_map = [];
        spl_autoload_unregister([$this,'_autoload']);
    }
}
