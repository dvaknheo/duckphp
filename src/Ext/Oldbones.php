<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;

class Oldbones
{
    use SingletonEx;
    
    public function init($options=[], $context=null)
    {
        DIExt::G()->init($options, $context);
        FunctionDispatcher::G()->init($options, $context);
        FunctionView::G()->init($options, $context);
        ProjectCommonAutoloader::G()->init($options, $context);
        ProjectCommonConfiger::G()->init($options, $context);
    }
}
class DIExt
{
    use SingletonEx;

    protected $_di_container;
    public static function DI($name, $object=null)
    {
        return static::G()->_DI($name, $object);
    }
    public function _DI($name, $object=null)
    {
        if (null===$object) {
            return $this->_di_container[$name];
        }
        $this->_di_container[$name]=$object;
        return $object;
    }
    ////////////
    public function init($options=[], $context=null)
    {
        if ($context) {
            $this->initContext($options, $context);
        }
        return $this;
    }
    protected function initContext($options=[], $context=null)
    {
        //$context->assignStaticMethod('DI', [static::class,'DI']); //TODO
    }
}

//TODO don't do so more;
class FunctionDispatcher
{
    use SingletonEx;
    
    protected $path_info;
    public $prefix='action_';
    public $default_callback='action_index';
    public function init($options=[], $context=null)
    {
        if ($context) {
            $context->addRouteHook([static::G(),'hook']);
        }
    }
    public function hook($route)
    {
        $this->path_info=$route->path_info;
        $flag=$this->runRoute($route);
        if ($flag) {
            $route->stopRunDefaultHandler();
        }
    }
    public function runRoute($route)
    {
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
                $callback=null;
            }
        }
        
        if ($callback && is_callable($callback)) {
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
class FunctionView extends View
{
    const DEFAULT_OPTIONS=[
    
    ];
    public $prefix='view_';
    public $head_callback;
    public $foot_callback;
    
    private $callback;
    
    public function init($options=[], $context=null)
    {
        $ret=parent::init($options, $context);
        $options=$context->options;
        $this->head_callback=$options['function_view_head']??'';
        $this->foot_callback=$options['function_view_foot']??'';
        return $ret;
    }
    public function _Show($data=[], $view)
    {
        $this->view=$view;
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);

        $this->prepareFiles();
        
        
        if ($this->head_callback) {
            if (is_callable($this->head_callback)) {
                ($this->head_callback)($this->data);
            }
        } else {
            if ($this->head_file) {
                $this->head_file=rtrim($this->head_file, '.php').'.php';
                include($this->path.$this->head_file);
            }
        }
        
        $this->callback=$this->prefix.str_replace('/', '__', preg_replace('/^Main\//', '', $this->view));
        if (is_callable($this->callback)) {
            ($this->callback)($this->data);
        } else {
            if (!is_file($this->view_file)) {
                //echo "DNMVCS FunctionView: Not callback {$this->callback}; not file $this->view_file";
                return;
            }
            include($this->view_file);
        }
        
        if ($this->head_callback) {
            if (is_callable($this->foot_callback)) {
                ($this->foot_callback)($this->data);
            }
        } else {
            if ($this->foot_file) {
                $this->foot_file=rtrim($this->foot_file, '.php').'.php';
                include($this->path.$this->foot_file);
            }
        }
    }
    public function _ShowBlock($view, $data=null)
    {
        $this->view=$view;
        $this->data=array_merge($this->data, $data);
        $data=null;
        $view=null;
        extract($this->data);
        
        $this->callback=$this->prefix.str_replace('/', '__', preg_replace('/^Main\//', '', $this->view));
        if (is_callable($this->callback)) {
            ($this->callback)($this->data);
        } else {
            if (!is_file($this->view_file)) {
                echo "DNMVCS FunctionView ShowBlock: Not callback {$this->callback}; No file {$this->view_file}";
                return;
            }
            include($this->view_file);
        }
    }
}

class ProjectCommonAutoloader
{
    use SingletonEx;
    const DEFAULT_OPTIONS_EX=[
        'fullpath_project_share_common'=>'',
    ];
    protected $path_common;
    public function init($options=[], $context=null)
    {
        $this->path_common=isset($options['fullpath_project_share_common'])??'';
        if ($context) {
            $this->run();
        }
        return $this;
    }
    public function run()
    {
        spl_autoload_register([$this,'_autoload']);
    }
    public function _autoload($class)
    {
        if (strpos($class, '\\')!==false) {
            return;
        }
        $path_common=$this->path_common;
        if (!$path_common) {
            return;
        }
        $flag=preg_match('/Common(Service|Model)$/', $class, $m);
        if (!$flag) {
            return;
        }
        $file=$path_common.'/'.$class.'.php';
        if (!$file || !file_exists($file)) {
            return;
        }
        require $file;
    }
}


class ProjectCommonConfiger extends Configer
{
    const DEFAULT_OPTIONS_EX=[
        'fullpath_config_common'=>'',
    ];
    public $fullpath_config_common;

    public function init($options=[], $context=null)
    {
        $this->fullpath_config_common=isset($options['fullpath_config_common'])??'';
        return parent::init($options, $context);
    }
    protected function loadFile($basename, $checkfile=true)
    {
        $common_config=[];
        if ($this->fullpath_config_common) {
            $file=$this->fullpath_config_common.$basename.'.php';
            if (is_file($file)) {
                $common_config=(function ($file) {
                    return include($file);
                })($file);
            }
        }
        $ret=parent::loadFile($basename, $checkfile);
        $ret=array_merge($common_config, $ret);
        return $ret;
    }
}
