<?php
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

class Route
{
    use SingletonEx;
    
    const DEFAULT_OPTIONS=[
            'namespace'=>'MY',
            'namespace_controller'=>'Controller',
            
            'controller_base_class'=>null,
            'controller_welcome_class'=>'Main',
            
            'controller_hide_boot_class'=>false,
            'controller_methtod_for_miss'=>'_missing',
            'controller_prefix_post'=>'do_',
            'controller_postfix'=>'',
            
        ];
    
    public $parameters=[];
    public $urlHandler=null;
    
    protected $controller_welcome_class='Main';
    protected $controller_index_method='index';
    
    public $namespace_controller='';
    protected $controller_hide_boot_class=false;
    protected $controller_methtod_for_miss=null;
    protected $controller_base_class=null;
    
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
    public $error='';
    
    protected $prependedObjectList=[];
    protected $appendedObjectList=[];
    
    public static function RunQuickly(array $options=[], callable $after_init=null)
    {
        $instance=static::G()->init($options);
        if($after_init){
            ($after_init)();
        }
        return $instance->run();
    }
    public static function URL($url=null)
    {
        return static::G()->_URL($url);
    }
    public static function Parameters()
    {
        return static::G()->_Parameters();
    }
    ////
    public function _URL($url=null)
    {
        if ($this->urlHandler) {
            return ($this->urlHandler)($url);
        }
        return $this->defaultURLHandler($url);
    }
    public function _Parameters()
    {
        return $this->parameters;
    }
    public function defaultURLHandler($url=null)
    {
        if (strlen($url)>0 && substr($url, 0, 1)==='/') {
            return $url;
        }

        $basepath=substr(rtrim(str_replace('\\', '/', $this->script_filename), '/').'/', strlen($this->document_root));

        if ($basepath=='/index.php/') {
            $basepath='/';
        }
        if ($basepath=='/index.php') {
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

    
    public function init($options=[], $context=null)
    {
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, $options);
        $this->options=$options;
        $this->controller_prefix_post=$options['controller_prefix_post'];
        $this->enable_post_prefix=$this->controller_prefix_post?true:false;
        
        $this->controller_hide_boot_class=$options['controller_hide_boot_class'];
        $this->controller_methtod_for_miss=$options['controller_methtod_for_miss'];
        
        $this->controller_welcome_class=$options['controller_welcome_class'];
        
        
        $namespace=$options['namespace'];
        $namespace_controller=$options['namespace_controller'];
        if (substr($namespace_controller, 0, 1)!=='\\') {
            $namespace_controller=rtrim($namespace, '\\').'\\'.$namespace_controller;
        }
        $namespace_controller=trim($namespace_controller, '\\');
        $this->namespace_controller=$namespace_controller;
        
        $this->controller_base_class=$options['controller_base_class'];
        if ($this->controller_base_class && substr($this->controller_base_class, 0, 1)!=='\\') {
            $this->controller_base_class=rtrim($namespace, '\\').'\\'.$this->controller_base_class;
        }
        
        return $this;
    }
    public function bindServerData($server)
    {
        $this->script_filename=$server['SCRIPT_FILENAME']??'';
        $this->document_root=$server['DOCUMENT_ROOT']??'';
        $this->request_method=$server['REQUEST_METHOD']??'GET';
        if (isset($server['PATH_INFO'])) {
            $this->path_info=$server['PATH_INFO'];
        }elseif (PHP_SAPI==='cli') {
            $argv=$server['argv']??[];
            if (count($argv)>=2) {
                $this->path_info=$argv[1];
                array_shift($argv);
                array_shift($argv);
                $this->parameters=$argv;
            }
        }else{
            $this->path_info=''; // @codeCoverageIgnore
        }
        
        $this->has_bind_server_data=true;
        return $this;
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
    
    protected function beforeRun()
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
    }
    public function run()
    {
        foreach ($this->prependedObjectList as $object) {
            $flag=$object->run();
            if ($flag) {
                return true;
            }
        }
        
        $this->beforeRun();
        
        if (null!==$this->callback) {
            ($this->callback)();
            $this->callback=null;
            // to make  $this->controller __destruct;
            return true;
        }
        
        foreach ($this->appendedObjectList as $object) {
            $flag=$object->run();
            if ($flag) {
                return true;
            }
        }
        return false;
    }
    public function prepend(object $object): void
    {
        array_unshift($this->prependedObjectList, $object);
    }
    public function append(object $object): void
    {
        array_push($this->appendedObjectList, $object);
    }
    public function stopRunDefaultHandler()
    {
        $this->callback=function () {
            return; //keep for tests
        };
    }
    
    protected function getFullClassByAutoLoad($path_class)
    {
        $path_class=$path_class?:$this->controller_welcome_class;
        $class=$this->namespace_controller.'\\'.str_replace('/', '\\', $path_class).$this->options['controller_postfix'];
        if (!class_exists($class)) {
            $this->error="Can't find class($class)";
            return null;
        }
        return $class;
    }
    
    public function defaultRouteHandler()
    {
        $path_info=$this->path_info;
        $t=explode('/', $path_info);
        $method=array_pop($t);
        $class_path=implode('/', $t);
        
        $this->calling_path=$class_path?$this->path_info:$this->controller_welcome_class.'/'.$method;
        
        if ($this->controller_hide_boot_class) {
            if ($class_path===$this->controller_welcome_class) {
                $this->error="controller_hide_boot_class! {$this->controller_welcome_class} ";
                return null;
            }
        }
        $full_class=$this->getFullClassByAutoLoad($class_path);
        $callback=$this->getCallback($full_class, $method);
        if ($callback) {
            return $callback;
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
            $this->error="no the controller_base_class! {$this->controller_base_class} ";
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
        if ($this->controller_methtod_for_miss) {
            if ($method===$this->controller_methtod_for_miss) {
                return null;
            }
            if (!method_exists($obj, $method)) {
                $method=$this->controller_methtod_for_miss;
            }
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
    public function setRouteCallingMethod($calling_method)
    {
        $this->calling_method=$calling_method;
    }
}
