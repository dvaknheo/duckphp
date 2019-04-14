<?php
namespace DNMVCS;

//TODO don't do so more;
class FunctionDispatcher
{
    use DNSingleton;
    
    protected $path_info;
    public $prefix='action_';
    public $default_callback='action_index';
    public function hook($route)
    {
        $this->path_info=$route->path_info;
        $flag=$this->runRoute();
        if ($flag) {
            $route->stopRunDefaultHandler();
        }
    }
    public function runRoute()
    {
        $route=DNRoute::G();
        $post=($route->request_method==='POST')?$route->prefix_post:'';
        $callback=$this->prefix.$post.$this->path_info;
        $path_info=$this->path_info?:'index';
        $prefix=str_replace('\\', '/', $this->prefix);
        $fullpath=$prefix.$path_info;
        $blocks=explode('/', $fullpath);
        $method=array_pop($blocks);
        $classname=implode('\\', $blocks);
        // a\b
        if ($classname) {
            if (class_exists($classname)) {
                $class=new $classname();
                $method=$post?$post.$method:$method;
                $callback=[$class,$method];
            } else {
                $callback=null;
            }
        } else {
            $method=$post?$post.$path_info:$path_info;
            $method=$this->prefix.$method;
            $callback=$method;
            if (!is_callable($callback)) {
                var_dump('xx'.$callback);

                $callback=null;
            }
        }
        
        if ($callback) {
            ($callback)();
            return true;
        }
        if (is_callable($this->default_callback)) {
            ($this->default_callback)();
            return true;
        } else {
            //($route->the404Handler)();
            return false;
        }
    }
}
