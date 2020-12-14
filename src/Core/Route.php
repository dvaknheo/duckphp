<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;

class Route extends ComponentBase
{
    use Route_UrlManager;
    use Route_Helper;
    
    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    public $options = [
            'namespace' => '',
            'namespace_controller' => 'Controller',
            
            'controller_base_class' => null,
            'controller_welcome_class' => 'Main',
            
            'controller_hide_boot_class' => false,
            'controller_methtod_for_miss' => '_missing',
            'controller_prefix_post' => 'do_',
            'controller_class_postfix' => '',
            'controller_enable_slash' => false,
            'controller_path_ext' => '',
            'controller_use_singletonex' => false,
            'controller_stop_g_method' => false,
            'controller_stop_static_method' => false,
            'skip_fix_path_info' => false,
        ];
    //public input;
    public $request_method = '';

    public $pre_run_hook_list = [];
    public $post_run_hook_list = [];
    
    //input
    protected $path_info = '';
    protected $parameters = [];

    //calculated options;
    protected $namespace_prefix = '';
    protected $index_method = 'index'; //const

    //properties
    protected $route_error = '';
    protected $calling_path = '';
    protected $calling_class = '';
    protected $calling_method = '';
    
    //flags
    protected $has_bind_server_data = false;
    protected $enable_default_callback = true;
    protected $is_failed = false;

    public static function RunQuickly(array $options = [], callable $after_init = null)
    {
        $instance = static::G()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    public static function Route()
    {
        return static::G();
    }
    public static function Parameter($key, $default = null)
    {
        return static::G()->_Parameter($key, $default);
    }
    public function _Parameter($key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }
    //@override
    protected function initOptions(array $options)
    {
        $namespace = $this->options['namespace'];
        $namespace_controller = $this->options['namespace_controller'];
        if (substr($namespace_controller, 0, 1) !== '\\') {
            $namespace_controller = rtrim($namespace, '\\').'\\'.$namespace_controller;
        }
        $namespace_controller = trim($namespace_controller, '\\');
        $this->namespace_prefix = $namespace_controller.'\\';
        

        $this->path_info = $_SERVER['PATH_INFO'] ?? '';
        $this->request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    public function reset()
    {
        $this->request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path_info = $this->fixPathInfo($_SERVER, $this->path_info);
        $this->has_bind_server_data = true;
        $this->is_failed = false;

        return $this;
    }
    // TODO move to other extend
    protected function fixPathInfo($serverData, $default)
    {
        if ($this->options['skip_fix_path_info']) {
            return $serverData['PATH_INFO'] ?? $default;
        }

        if (!empty($serverData['PATH_INFO'])) {
            return $serverData['PATH_INFO'] ?? $default;
        }
        if (!isset($serverData['REQUEST_URI'])) {
            return $serverData['PATH_INFO'] ?? $default;
        }
        $request_path = (string)parse_url($serverData['REQUEST_URI'], PHP_URL_PATH);
        $request_file = substr($serverData['SCRIPT_FILENAME'], strlen($serverData['DOCUMENT_ROOT']));
        
        if ($request_file === '/index.php' && substr($request_path, 0, strlen($request_file)) !== '/index.php') {
            $path_info = $request_path;
        } else {
            $path_info = substr($request_path, strlen($request_file));
        }
        
        return $path_info;
    }
    public function bind($path_info, $request_method = 'GET')
    {
        //TODO  Remove
        $path_info = parse_url($path_info, PHP_URL_PATH);
        
        if (!$this->has_bind_server_data) {
            $this->reset();
        }
        $this->path_info = $path_info;
        
        if (isset($request_method)) {
            $this->request_method = $request_method;
        }
        return $this;
    }
    protected function beforeRun()
    {
        $this->is_failed = false;
        if (!$this->has_bind_server_data) {
            $this->reset();
        }
    }
    public function run()
    {
        $this->beforeRun();
        foreach ($this->pre_run_hook_list as $callback) {
            $flag = ($callback)($this->path_info);
            if ($flag) {
                return $this->getRunResult();
            }
        }
        
        if ($this->enable_default_callback) {
            $flag = $this->defaultRunRouteCallback($this->path_info);
            if ($flag && (!$this->is_failed)) {
                return $this->getRunResult();
            }
        } else {
            $this->enable_default_callback = true;
        }
        
        foreach ($this->post_run_hook_list as $callback) {
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
        // TODO . force result ?
        $this->is_failed = true;
    }
    public function addRouteHook($callback, $position, $once = true)
    {
        if ($once) {
            if (($position === 'prepend-outter' || $position === 'prepend-inner') && in_array($callback, $this->pre_run_hook_list)) {
                return false;
            }
            if (($position === 'append-inner' || $position === 'append-outter') && in_array($callback, $this->post_run_hook_list)) {
                return false;
            }
        }
        switch ($position) {
            case 'prepend-outter':
                array_unshift($this->pre_run_hook_list, $callback);
                break;
            case 'prepend-inner':
                array_push($this->pre_run_hook_list, $callback);
                break;
            case 'append-inner':
                array_unshift($this->post_run_hook_list, $callback);
                break;
            case 'append-outter':
                array_push($this->post_run_hook_list, $callback);
                break;
            default:
                return false;
        }
        return true;
    }
    public function add404RouteHook($callback)
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
        if ($this->options['controller_enable_slash']) {
            $path_info = rtrim($path_info, '/');
        }
        if (!empty($this->options['controller_path_ext']) && !empty($path_info)) {
            $l = strlen($this->options['controller_path_ext']);
            if (substr($path_info, -$l) !== $this->options['controller_path_ext']) {
                $this->route_error = "path_extention error";
                return false;
            }
            $path_info = substr($path_info, 0, -$l);
        }
        
        $t = explode('/', $path_info);
        $method = array_pop($t);
        $path_class = implode('/', $t);
        
        $this->calling_path = $path_class?$path_info:$this->options['controller_welcome_class'].'/'.$method;
        $this->route_error = '';
        
        if ($this->options['controller_hide_boot_class'] && $path_class === $this->options['controller_welcome_class']) {
            $this->route_error = "controller_hide_boot_class! {$this->options['controller_welcome_class']} ";
            return null;
        }
        $path_class = $path_class ?: $this->options['controller_welcome_class'];
        $full_class = $this->namespace_prefix.str_replace('/', '\\', $path_class).$this->options['controller_class_postfix'];
        if (!class_exists($full_class)) {
            $this->route_error = "can't find class($full_class) by $path_class ";
            return null;
        }
        
        $this->calling_class = $full_class;
        $this->calling_method = !empty($method)?$method:'index';
        
        /** @var string */
        $base_class = str_replace('~', $this->namespace_prefix, $this->options['controller_base_class']);
        /** @var mixed */ $class = $full_class; // phpstan
        if (!empty($base_class) && !is_subclass_of($class, $base_class)) {
            $this->route_error = "no the controller_base_class! {$base_class} ";
            return null;
        }
        $object = $this->createControllerObject($full_class);
        return $this->getMethodToCall($object, $method);
    }

    protected function createControllerObject($full_class)
    {
        if (!$this->options['controller_use_singletonex'] || !is_callable([$full_class,'G'])) {
            return new $full_class();
        }
        $object = $full_class::G();
        $class_name = get_class($object);
        if ($class_name == $full_class) {
            $full_class::G(new \stdClass);
            return $object;
        }
        if ($class_name === 'stdClass') {
            return new $full_class();
        }
        $object = new $class_name();
        return $object;
    }
    protected function getMethodToCall($object, $method)
    {
        $method = ($method === '') ? $this->index_method : $method;
        if (substr($method, 0, 2) == '__') {
            $this->route_error = 'can not call hidden method';
            return null;
        }
        if (($this->options['controller_use_singletonex'] || $this->options['controller_stop_g_method']) && $method === 'G') {
            $this->route_error = 'can not call G()';
            return null;
        }
        if ($this->options['controller_prefix_post'] && $this->request_method === 'POST' && method_exists($object, $this->options['controller_prefix_post'].$method)) {
            $method = $this->options['controller_prefix_post'].$method;
        }
        if ($this->options['controller_methtod_for_miss']) {
            if ($method === $this->options['controller_methtod_for_miss']) {
                $this->route_error = 'can not direct call controller_methtod_for_miss ';
                return null;
            }
            if (!method_exists($object, $method)) {
                $method = $this->options['controller_methtod_for_miss'];
            }
        }
        if ($this->options['controller_stop_static_method']) {
            $ref = new \ReflectionMethod($object, $method);
            if ($ref->isStatic()) {
                $this->route_error = 'can not call static function';
                return null;
            }
        }
        if (!is_callable([$object,$method])) {
            $this->route_error = 'method can not call';
            return null;
        }
        return [$object,$method];
    }
}
trait Route_Helper
{
    ////
    public function getPathInfo()
    {
        return $this->path_info;
    }
    public function setPathInfo($path_info)
    {
        $this->path_info = $path_info;
    }
    public function getParameters()
    {
        return $this->parameters;
    }
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }
    public function getRouteError()
    {
        return $this->route_error;
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
    public function getNamespacePrefix()
    {
        return $this->namespace_prefix;
    }
    public function dumpAllRouteHooksAsString()
    {
        $ret = "-- pre run --\n";
        $ret .= var_export($this->pre_run_hook_list, true);
        $ret .= "\n-- run --\n";
        $ret .= var_export($this->post_run_hook_list, true);
        $ret .= "\n-- post run --\n";
        return $ret;
    }
    public function replaceControllerSingelton($old_class, $new_class)
    {
        $old_class::G((new \ReflectionClass($new_class))->newInstanceWithoutConstructor());
    }
}
trait Route_UrlManager
{
    //protected $path_info = '';  // shared
    protected $script_filename = null; // need a setter
    protected $document_root = null;   // need a setter
    
    protected $url_handler = null;
    public static function Url($url = null)
    {
        return static::G()->_Url($url);
    }
    public function _Url($url = null)
    {
        if ($this->url_handler) {
            return ($this->url_handler)($url);
        }
        return $this->defaultUrlHandler($url);
    }
    public function defaultUrlHandler($url = null)
    {
        if (isset($url) && strlen($url) > 0 && substr($url, 0, 1) === '/') {
            return $url;
        }
        
        //get basepath.
        $document_root = rtrim($this->document_root ?? $_SERVER['DOCUMENT_ROOT'], '/');
        $basepath = substr(rtrim($this->script_filename ?? $_SERVER['SCRIPT_FILENAME'], '/'), strlen($document_root));
        
        /* something wrong ?
        if (substr($basepath, -strlen('/index.php'))==='/index.php') {
            $basepath=substr($basepath, 0, -strlen('/index.php'));
        }
        */
        if ($basepath === '/index.php') {
            $basepath = '/';
        }
        if ('' === $url) {
            return $basepath;
        }
        if (isset($url) && '?' === substr($url, 0, 1)) {
            $path_info = $this->path_info;
            return $basepath.$path_info.$url;
        }
        if (isset($url) && '#' === substr($url, 0, 1)) {
            $path_info = $this->path_info;
            return $basepath.$path_info.$url;
        }
        // ugly.
        $basepath = rtrim($basepath, '/');
        $url = '/'.$url;
        
        return $basepath.$url;
    }
    public function setUrlHandler($callback)
    {
        $this->url_handler = $callback;
    }
    public function getUrlHandler()
    {
        return $this->url_handler;
    }
}
