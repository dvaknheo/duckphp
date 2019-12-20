<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;
use DuckPhp\Core\Route;

class Lazybones // @codeCoverageIgnoreStart
{
    use SingletonEx;
    
    public $options = [
        'lazy_mode' => true,
        'use_app_path' => true,
        'lazy_path' => '',//''app',
        'lazy_path_service' => 'Service',
        'lazy_path_model' => 'Model',
        'lazy_path_contorller' => 'Controller',
        
        'lazy_controller_class' => 'DNController',
        'with_controller_namespace_namespace' => true,
        'with_controller_namespace_prefix' => true,
        'with_controller_enable_paramters' => false,
    ];
    protected $lazy_path = '';
    protected $lazy_path_service = '';
    protected $lazy_path_model = '';
    protected $lazy_path_contorller = '';
    protected $lazy_controller_class;
    protected $lazy_class_prefix = '';
    
    protected $with_controller_namespace_namespace = true;
    protected $with_controller_namespace_prefix = true;
    protected $with_controller_enable_paramters = false;
    protected $error;
    
    public function __construct()
    {
    }
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        $options = $this->options;
        if (!($options['lazy_mode'] ?? false)) {
            return;
        }
        
        $this->lazy_path = $options['lazy_path'];
        $this->lazy_path_service = $options['lazy_path_service'];
        $this->lazy_path_model = $options['lazy_path_contorller'];
        $this->lazy_path_contorller = $options['lazy_path_contorller'];
        
        $this->lazy_controller_class = $options['lazy_controller_class'];
        
        $this->with_controller_namespace_namespace = $options['with_controller_namespace_namespace'];
        $this->with_controller_namespace_prefix = $options['with_controller_namespace_prefix'];
        $this->with_controller_enable_paramters = $options['with_controller_enable_paramters'];

        if ($options['use_app_path'] ?? false) {
            $this->lazy_path = $options['path'];
        }
        $this->lazy_path = rtrim($this->lazy_path, '/').'/';
        $this->lazy_path_service = $this->lazy_path.rtrim($this->lazy_path_service, '/').'/';
        $this->lazy_path_model = $this->lazy_path.rtrim($this->lazy_path_model, '/').'/';
        $this->lazy_path_contorller = $this->lazy_path.rtrim($this->lazy_path_contorller, '/').'/';
        
        Route::G()->add404Handler([$this,'runRoute']);
    }
    public function MapToService($serviceClass, $input)
    {
        try {
            $method = static::G()->context_class::G()->getRouteCallingMethod();
            $data = static::G()->context_class::G()->callAPI($serviceClass, $method, $input);
            if (!is_array($data) && !is_object($data)) {
                $data = ['result' => $data];
            }
        } catch (\Throwable $ex) {
            $data = [];
            $data['error_message'] = $ex->getMessage();
            $data['error_code'] = $ex->getCode();
        }
        static::G()->context_class::ExitJson($data);
    }
    public function explodeService($object, $namespace = null)
    {
        $namespace = $namespace ?? 'MY\\Service';
        $vars = array_keys(get_object_vars($object));
        $l = strlen('Service');
        foreach ($vars as $v) {
            if (substr($v, 0 - $l) !== 'Service') {
                continue;
            }
            $name = ucfirst($v);
            $class = $namespace.$name;
            if (class_exists($class)) {
                $object->$v = $class::G();
            }
        }
    }
    ////
    public function runRoute()
    {
        $path_info = Route::G()->path_info;
        $enable_paramters = $this->with_controller_enable_paramters;
        
        $class_blocks = explode('/', $path_info);
        $method = array_pop($class_blocks);
        $class_path = implode('/', $class_blocks);
        
        $full_class = $this->getFullClassByNoNameSpace($class_path);
        $callback = $this->getCallback($full_class, $method);
        if ($callback) {
            return $callback;
        }
        if (!$enable_paramters) {
            return null;
        }
        list($full_class, $the_method, $parameters, $calling_path) = $this->getRouteDispatchInfo($class_blocks, $method);
        if (!$full_class) {
            return null;
        }
        $method = $the_method;
        Route::G()->parameters = $parameters;
        Route::G()->calling_path = $calling_path;
        return $this->getCallback($full_class, $method);
    }
    protected function getCallback($full_class, $method)
    {
        return [new $full_class,$method];
    }
    protected function getRouteDispatchInfo($blocks, $method)
    {
        $class = null;
        $parameters = [];
        $calling_path = '';
        $p = implode('/', $blocks);
        $l = count($blocks);
        for ($i = 0;$i < $l;$i++) {
            $class_names = array_slice($blocks, 0, $l - $i);
            $parameters = $i?array_slice($blocks, -$i):[];
            $calling_path = implode('/', $class_names);
            
            $class = $this->getFullClassByNoNameSpace($calling_path, true);
            if ($class) {
                break;
            }
        }
        if (!$class) {
            return [null,$method,$parameters,$calling_path];
        }
        array_push($parameters, $method);
        $method = array_shift($parameters);
        $calling_path = $calling_path.'/'.$method;
        
        return [$class,$method,$parameters,$calling_path];
    }
    protected function getFullClassByNoNameSpace($path_class, $confirm = false)
    {
        $class = $this->checkLoadClass($path_class);
        if ($class) {
            if ($confirm) {
                return null;
            }
            return $class;
        }
        $file = $this->lazy_path_contorller.$path_class.'.php';
        
        if (!is_file($file)) {
            //if(!$confirm){ $this->error="no file to get class"; }
            return null;
        }
        $this->includeControllerFile($file);
        
        return $this->checkLoadClass($path_class);
    }
    // DNController
    // MyProject__Controller__AA__BB__CC
    // MyProject\Controller\DNController
    // MyProject\Controller\AA__BB__CC
    protected function checkLoadClass($path_class)
    {
        $namespace_controller = Route::G()->namespace_controller;    // ???

        $path_class_simple = str_replace('/', '__', $path_class);
        
        $class = ($this->lazy_controller_class)?$this->lazy_controller_class:'';
        if (class_exists($class)) {
            return $class;
        }
        $class = $this->lazy_class_prefix.$path_class_simple;
        if (class_exists($class)) {
            return $class;
        }
        $class = ($this->lazy_controller_class)?$namespace_controller.'\\'.$this->lazy_controller_class:'';
        if (class_exists($class)) {
            return $class;
        }
        $class = ($this->lazy_controller_class)?$namespace_controller.'\\'.$path_class_simple:'';
        if (class_exists($class)) {
            return $class;
        }
        return null;
    }
    // You can override it; variable indived
    protected function includeControllerFile($file)
    {
        include_once $file;
    }
    
    //// backup
    /*
    protected function getClassMethodAndParameters($blocks, $method)
    {
        $class=null;
        $paramters=[];
        $callinig_path='';
        $paramters=implode('/', $blocks);
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
    protected function getControllerByFiles()
    {
        //
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
    }
    //*/
} // @codeCoverageIgnoreEnd
