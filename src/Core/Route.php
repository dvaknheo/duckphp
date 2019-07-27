<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class Route
{
    use SingletonEx;
    
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'namespace_controller'=>'Controller',
            
            'controller_base_class'=>null,
            'controller_enable_paramters'=>false,
            'controller_hide_boot_class'=>false,
            'controller_methtod_for_miss'=>null,
            
            'controller_prefix_post'=>'do_',
            'controller_welcome_class'=>'Main',
            
            'on_404_handler'=>null,
            'skip_deal_route_404_handler'=>false,
        ];
    
    public $parameters=[];
    public $urlHandler=null;
    
    protected $controller_welcome_class='Main';
    protected $controller_index_method='index';
    
    public $controller_enable_paramters=false;
    public $namespace_controller='';
    protected $controller_hide_boot_class=false;
    protected $controller_methtod_for_miss=null;
    protected $controller_base_class=null;
    public $on_404_handler=null;
    
    protected $enable_post_prefix=true;
    public $controller_prefix_post='do_';
    
    public $calling_path='';
    public $calling_class='';
    public $calling_method='';
    
    public $has_bind_server_data=false;
    public $path_info='';
    public $request_method='';
    public $script_filename='';
    public $document_root='';

    public $routeHooks=[];
    public $callback=null;
    public $ext_route_handler=null;
    public function _URL($url=null)
    {
        if ($this->urlHandler) {
            return ($this->urlHandler)($url);
        }
        return $this->defaultURLHandler($url);
    }
    public function defaultURLHandler($url=null)
    {
        if (strlen($url)>0 && '/'==$url{0}) {
            return $url;
        };
        
        $basepath=substr(rtrim(str_replace('\\', '/', $this->script_filename), '/').'/', strlen($this->document_root));

        if ($basepath=='/index.php') {
            $basepath='/';
        }
        if ($basepath=='/index.php/') {
            $basepath='/';
        }
        
        if (''===$url) {
            return $basepath;
        }
        
        if ('?'==$url{0}) {
            return $basepath.$this->path_info.$url;
        }
        if ('#'==$url{0}) {
            return $basepath.$this->path_info.$url;
        }
        
        return $basepath.$url;
    }
    public function _Parameters()
    {
        return $this->parameters;
    }
    
    public function init($options=[], $context=null)
    {
        $this->controller_index_method='index';
        
        $options=array_merge(static::DEFAULT_OPTIONS, $options);
        
        $this->controller_enable_paramters=$options['controller_enable_paramters'];
        
        $this->controller_prefix_post=$options['controller_prefix_post'];
        $this->enable_post_prefix=$this->controller_prefix_post?true:false;
        
        $this->controller_hide_boot_class=$options['controller_hide_boot_class'];
        $this->controller_methtod_for_miss=$options['controller_methtod_for_miss'];
        
        $this->controller_welcome_class=$options['controller_welcome_class'];
        
        $this->on_404_handler=$options['on_404_handler'];
        
        $namespace=$options['namespace'];
        $namespace_controller=$options['namespace_controller'];
        if (substr($namespace_controller, 0, 1)!=='\\') {
            $namespace_controller=$namespace.'\\'.$namespace_controller;
        }
        $namespace_controller=ltrim($namespace_controller, '\\');
        $this->namespace_controller=$namespace_controller;
        
        $this->controller_base_class=$options['controller_base_class'];
        if ($this->controller_base_class && substr($this->controller_base_class, 0, 1)!=='\\') {
            $this->controller_base_class=$namespace.'\\'.$this->controller_base_class;
        }
        $this->skip_deal_route_404_handler=$options['skip_deal_route_404_handler'];
        
        return $this;
    }
    public function bindServerData($server)
    {
        $this->script_filename=$server['SCRIPT_FILENAME']??'';
        $this->document_root=$server['DOCUMENT_ROOT']??'';
        $this->request_method=$server['REQUEST_METHOD']??'GET';
        $this->path_info=$server['PATH_INFO']??'';
        
        $argv=$server['argv']??[];
        
        if (PHP_SAPI==='cli') {
            if (count($argv)>=2) {
                $this->path_info=$argv[1];
                array_shift($argv);
                array_shift($argv);
                $this->parameters=$argv;
            }
        }
        $this->has_bind_server_data=true;
        return $this;
    }
    public function set404($callback)
    {
        $this->on_404_handler=$callback;
    }
    public function setURLHandler($callback)
    {
        $this->urlHandler=$callback;
    }
    public function getURLHandler()
    {
        return $this->urlHandler;
    }
    public function addRouteHook($hook, $prepend=false, $once=true)
    {
        if ($once) {
            foreach ($this->routeHooks as $v) {
                if ($v==$hook) {
                    return false;
                }
            }
        }
        if (!$prepend) {
            array_push($this->routeHooks, $hook);
        } else {
            array_unshift($this->routeHooks, $hook);
        }
        return true;
    }
    public function run()
    {
        if (!$this->has_bind_server_data) {
            $this->bindServerData($_SERVER);
        }
        $this->path_info=ltrim($this->path_info, '/');
        $this->callback=null;
        foreach ($this->routeHooks as $hook) {
            ($hook)($this);
        }
        if (null===$this->callback) {
            $this->callback=$this->defaultRouteHandler();
        }
        if (null===$this->callback && $this->ext_route_handler) {
            $this->callback=($this->ext_route_handler)();
        }
        if (null!==$this->callback) {
            ($this->callback)(...$this->parameters);
            return true;
        }
        if ($this->skip_deal_route_404_handler) {
            return false;
        }
        
        if (!$this->on_404_handler) {
            header("HTTP/1.0 404 Not Found");
            echo "404 File Not Found.\n";
            echo "DNRoute Notice: 404 .  You need set 404 Handler by DNRoute->set404(\$callback).";
            exit;
        }
        ($this->on_404_handler)();
        return false;
    }
    public function stopRunDefaultHandler()
    {
        $this->callback=function () {
        };
    }
    
    protected function getFullClassByAutoLoad($path_class)
    {
        $path_class=$path_class?:$this->controller_welcome_class;
        $class=$this->namespace_controller.'\\'.str_replace('/', '\\', $path_class);
        if (!class_exists($class)) {
            return null;
        }
        return $class;
    }
    protected function getClassMethodAndParameters($blocks, $method)
    {
        $class=null;
        $paramters=[];
        $callinig_path='';
        $p=implode('/', $blocks);
        $l=count($blocks);
        for ($i=0;$i<$l;$i++) {
            $class_names=array_slice($blocks, 0, $l-$i);
            $parameters=$i?array_slice($blocks, -$i):[];
            $calling_path=implode('/', $class_names);
            
            $class=$this->namespace_controller.'\\'.implode('\\', $class_names);
            if (class_exists($class)) {
                break;
            }
        }
        if (!$class) {
            $this->error="No faill paramter not failed";
            return [null,$method,$parameters,$calling_path];
        }
        array_push($parameters, $method);
        $method=array_shift($parameters);
        $calling_path=$calling_path.'/'.$method;
        
        return [$class,$method,$parameters,$calling_path];
    }
    public function defaultRouteHandler()
    {
        $path_info=$this->path_info;
        
        $class_blocks=explode('/', $path_info);
        $method=array_pop($class_blocks);
        $class_path=implode('/', $class_blocks);
        
        $this->calling_path=$class_path?$this->path_info:$this->controller_welcome_class.'/'.$method;
        
        if ($this->controller_hide_boot_class) {
            if ($class_path===$this->controller_welcome_class) {
                $this->error="controller_hide_boot_class! {$this->controller_welcome_class} ";
                return null;
            }
        }
        $full_class=$this->getFullClassByAutoLoad($class_path, true);
        $callback=$this->getCallback($full_class, $method);
        if ($callback) {
            return $callback;
        }
        if ($this->controller_enable_paramters) {
            list($full_class, $the_method, $parameters, $calling_path)=$this->getClassMethodAndParameters($class_blocks, $method);
            if ($full_class) {
                $method=$the_method;
                $this->parameters=$parameters;
                $this->calling_path=$calling_path;
                
                $callback=$this->getCallback($full_class, $method);
                if ($callback) {
                    return $callback;
                }
            }
        }
        return null;
    }
    public function getCallback($full_class, $method)
    {
        if (!$full_class) {
            return null;
        }
        $this->calling_class=$full_class;
        $this->calling_method=$method;
        
        $object=$this->createControllerObject($full_class);
        if ($this->controller_base_class && !is_a($object, $this->controller_base_class)) {
            return null;
        }
        return $this->getMethodToCall($object, $method);
    }
    protected function createControllerObject($full_class)
    {
        return new $full_class();
    }
    protected function getMethodToCall($obj, $method)
    {
        $method=$method===''?$this->controller_index_method:$method;
        if (substr($method, 0, 2)=='__') {
            return null;
        }
        if ($this->enable_post_prefix && $this->request_method==='POST' &&  method_exists($obj, $this->controller_prefix_post.$method)) {
            $method=$this->controller_prefix_post.$method;
        }
        if ($this->controller_methtod_for_miss && !method_exists($obj, $method)) {
            $method=$this->controller_methtod_for_miss;
        }
        if (!is_callable([$obj,$method])) {
            return null;
        }
        return [$obj,$method];
    }
    public function getRouteCallingPath()
    {
        return $this->calling_path;
    }
    public function getRouteCallingClass()
    {
        return $this->calling_class;
    }
    public function getRouteCallingMethod()
    {
        return $this->calling_method;
    }
}
