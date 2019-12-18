<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonEx;

class Route
{
    use SingletonEx;
    
    public $options = [
            'namespace' => 'MY',
            'namespace_controller' => 'Controller',
            
            'controller_base_class' => null,
            'controller_welcome_class' => 'Main',
            
            'controller_hide_boot_class' => false,
            'controller_methtod_for_miss' => '_missing',
            'controller_prefix_post' => 'do_',
            'controller_postfix' => '',
        ];
    
    public $parameters = [];
    public $urlHandler = null;
    
    public $namespace_controller = '';
    protected $controller_welcome_class = 'Main';
    protected $controller_index_method = 'index';
    protected $controller_base_class = null;
    
    protected $controller_hide_boot_class = false;
    protected $controller_methtod_for_miss = null;
    protected $controller_prefix_post = 'do_';
    
    public $path_info = '';
    public $request_method = '';
    
    public $script_filename = '';
    public $document_root = '';

    public $error = '';
    public $calling_path = '';
    public $calling_class = '';
    public $calling_method = '';
    
    protected $has_bind_server_data = false;
    protected $prependedCallbackList = [];
    protected $appendedCallbackList = [];
    protected $enable_default_callback = true;
    protected $is_failed = false;
    
    public function __construct()
    {
    }
    public static function RunQuickly(array $options = [], callable $after_init = null)
    {
        $instance = static::G()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    public static function URL($url = null)
    {
        return static::G()->_URL($url);
    }
    public static function Parameters()
    {
        return static::G()->_Parameters();
    }
    ////
    public function _URL($url = null)
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
    public function defaultURLHandler($url = null)
    {
        if (isset($url) && strlen($url) > 0 && substr($url, 0, 1) === '/') {
            return $url;
        }
        
        //get basepath.
        $document_root = rtrim($this->document_root, '/');
        $basepath = substr(rtrim($this->script_filename, '/'), strlen($document_root));
        
        /*
        if (substr($basepath, -strlen('/index.php'))==='/index.php') {
            $basepath=substr($basepath, 0, -strlen('/index.php'));
        }
        */
        if ($basepath === '/index.php') {
            $basepath = '/';
        }
        if ('' === $url) {
            return $basepath.'/';
        }
        if ('?' == $url{0}) {
            $path_info = $this->path_info;
            return $basepath.$path_info.$url;
        }
        if ('#' == $url{0}) {
            $path_info = $this->path_info;
            return $basepath.$path_info.$url;
        }
        $url = '/'.$url;
        
        return $basepath.$url;
    }

    
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        $this->controller_prefix_post = $this->options['controller_prefix_post'];
        
        $this->controller_hide_boot_class = $this->options['controller_hide_boot_class'];
        $this->controller_methtod_for_miss = $this->options['controller_methtod_for_miss'];
        
        $this->controller_welcome_class = $this->options['controller_welcome_class'];
        
        
        $namespace = $this->options['namespace'];
        $namespace_controller = $this->options['namespace_controller'];
        if (substr($namespace_controller, 0, 1) !== '\\') {
            $namespace_controller = rtrim($namespace, '\\').'\\'.$namespace_controller;
        }
        $namespace_controller = trim($namespace_controller, '\\');
        $this->namespace_controller = $namespace_controller;
        
        $this->controller_base_class = $this->options['controller_base_class'];
        if ($this->controller_base_class && substr($this->controller_base_class, 0, 1) !== '\\') {
            $this->controller_base_class = rtrim($namespace, '\\').'\\'.$this->controller_base_class;
        }
        
        return $this;
    }
    public function setURLHandler($callback)
    {
        $this->urlHandler = $callback;
    }
    public function getURLHandler()
    {
        return $this->urlHandler;
    }
    
    public function bindServerData($server)
    {
        $this->script_filename = $server['SCRIPT_FILENAME'] ?? '';
        $this->document_root = $server['DOCUMENT_ROOT'] ?? '';
        $this->request_method = $server['REQUEST_METHOD'] ?? 'GET';
        if (isset($server['PATH_INFO'])) {
            $this->path_info = $server['PATH_INFO'];
        } elseif (PHP_SAPI === 'cli' && empty($this->path_info)) {
            $argv = $server['argv'] ?? [];
            if (count($argv) >= 2) {
                $this->path_info = $argv[1];
                array_shift($argv);
                array_shift($argv);
                $this->parameters = $argv;
            }
        }
        
        $this->has_bind_server_data = true;
        return $this;
    }
    public function bind($path_info, $request_method = 'GET')
    {
        $path_info = parse_url($path_info, PHP_URL_PATH);
        
        if (!$this->has_bind_server_data) {
            $this->bindServerData($_SERVER);
        }
        $this->path_info = $path_info;
        
        if (isset($request_method)) {
            $this->request_method = $request_method;
        }
        return $this;
    }
    protected function beforeRun()
    {
        if (!$this->has_bind_server_data) {
            $this->bindServerData($_SERVER);
        }
    }
    public function run()
    {
        $this->is_failed = false;
        $this->beforeRun();
        
        foreach ($this->prependedCallbackList as $callback) {
            $flag = ($callback)($this->path_info);
            if ($flag) {
                return $this->getRunResult();
            }
        }
        
        if ($this->enable_default_callback) {
            $flag = $this->defaultRunRouteCallback($this->path_info);
            if ($flag) {
                return true;
            }
        } else {
            $this->enable_default_callback = true;
        }
        
        foreach ($this->appendedCallbackList as $callback) {
            $flag = ($callback)($this->path_info);
            if ($flag) {
                return $this->getRunResult();
            }
        }
        return false;
    }
    protected function getRunResult()
    {
        if ($this->is_failed) {
            return false;
        }
        return true;
    }
    public function forceFail()
    {
        $this->is_failed = true;
    }
    public function addRouteHook($callback, $position, $once = true)
    {
        if ($once) {
            if (($position === 'prepend-outter' || $position === 'prepend-inner') && in_array($callback, $this->prependedCallbackList)) {
                return false;
            }
            if (($position === 'append-inner' || $position === 'append-outter') && in_array($callback, $this->appendedCallbackList)) {
                return false;
            }
        }
        switch ($position) {
            case 'prepend-outter':
                array_unshift($this->prependedCallbackList, $callback);
                break;
            case 'prepend-inner':
                array_push($this->prependedCallbackList, $callback);
                break;
            case 'append-inner':
                array_unshift($this->appendedCallbackList, $callback);
                break;
            case 'append-outter':
                array_push($this->appendedCallbackList, $callback);
                break;
            default:
                return false;
        }
        return true;
    }
    public function add404Handler($callback)
    {
        return $this->addRouteHook($callback, 'append-outter', false);
    }
    public function defaulToggleRouteCallback($enable = true)
    {
        $this->enable_default_callback = $enable;
    }
    public function defaultRunRouteCallback($path_info = null)
    {
        $callback = $this->defaultGetRouteCallback($path_info);
        if (null === $callback) {
            return false;
        }
        ($callback)();
        return true;
    }
    public function defaultGetRouteCallback($path_info)
    {
        $path_info = ltrim($path_info, '/');
        $t = explode('/', $path_info);
        $method = array_pop($t);
        $path_class = implode('/', $t);
        
        $this->calling_path = $path_class?$path_info:$this->controller_welcome_class.'/'.$method;
        $this->error = '';
        
        if ($this->controller_hide_boot_class && $path_class === $this->controller_welcome_class) {
            $this->error = "controller_hide_boot_class! {$this->controller_welcome_class} ";
            return null;
        }
        $path_class = $path_class?:$this->controller_welcome_class;
        $full_class = $this->namespace_controller.'\\'.str_replace('/', '\\', $path_class).$this->options['controller_postfix'];
        if (!class_exists($full_class)) {
            $this->error = "can't find class($full_class) by $path_class ";
            return null;
        }
        
        $this->calling_class = $full_class;
        $this->calling_method = !empty($method)?$method:'index';
        
        if ($this->controller_base_class && !is_subclass_of($full_class, $this->controller_base_class)) {
            $this->error = "no the controller_base_class! {$this->controller_base_class} ";
            return null;
        }
        $object = $this->createControllerObject($full_class);
        return $this->getMethodToCall($object, $method);
    }
    protected function createControllerObject($full_class)
    {
        // OK, you may use other mode.
        return new $full_class();
    }
    protected function getMethodToCall($object, $method)
    {
        $method = $method === ''?$this->controller_index_method:$method;
        if (substr($method, 0, 2) == '__') {
            $this->error = 'can not call hidden method';
            return null;
        }
        if ($this->controller_prefix_post && $this->request_method === 'POST' && method_exists($object, $this->controller_prefix_post.$method)) {
            $method = $this->controller_prefix_post.$method;
        }
        if ($this->controller_methtod_for_miss) {
            if ($method === $this->controller_methtod_for_miss) {
                $this->error = 'can not direct call controller_methtod_for_miss ';
                return null;
            }
            if (!method_exists($object, $method)) {
                $method = $this->controller_methtod_for_miss;
            }
        }
        if (!is_callable([$object,$method])) {
            $this->error = 'method can not call';
            return null;
        }
        return [$object,$method];
    }
    
    ////
    public function getPathInfo()
    {
        return $this->path_info;
    }
    public function setPathInfo($path_info)
    {
        $this->path_info = $path_info;
    }
    public function getRouteError()
    {
        return $this->error;
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
        $this->calling_method = $calling_method;
    }
}
