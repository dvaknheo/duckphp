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
        
        'controller_base_class' => '',
        'controller_hide_boot_class' => true,
        'controller_prefix_post' => 'do_',
        'controller_class_postfix' => '',
        'controller_path_ext' => '',
        'controller_welcome_class' => 'Main',
        
        'controller_class_map' => [],
        
        'controller_resource_prefix' => '',
        'controller_url_prefix' => '',
        
    ];

    public $pre_run_hook_list = [];
    public $post_run_hook_list = [];
    
    protected $parameters = [];
    
    //calculated options;
    protected $namespace_prefix = '';
    protected $index_method = 'index'; //const

    //properties
    protected $route_error = '';
    protected $calling_path = '';
    protected $calling_class = '';
    protected $calling_method = '';
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
    public static function Parameter($key = null, $default = null)
    {
        return static::G()->_Parameter($key, $default);
    }
    public function _Parameter($key = null, $default = null)
    {
        if (isset($key)) {
            return $this->parameters[$key] ?? $default;
        } else {
            return  $this->parameters;
        }
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
    }
    public function reset()
    {
        $this->is_failed = false;
        $this->enable_default_callback = true;
        
        $this->route_error = '';
        $this->calling_path = '';
        $this->calling_class = '';
        $this->calling_method = '';
        return $this;
    }
    public function bind($path_info, $request_method = 'GET')
    {
        $this->reset();

        $path_info = parse_url($path_info, PHP_URL_PATH);
        $this->setPathInfo($path_info);
        if (isset($request_method)) {
            $_SERVER['REQUEST_METHOD'] = $request_method;
            if (defined('__SUPERGLOBAL_CONTEXT')) {
                (__SUPERGLOBAL_CONTEXT)()->_SERVER = $_SERVER;
            }
        }
        return $this;
    }
    public function run()
    {
        $this->reset();
        
        foreach ($this->pre_run_hook_list as $callback) {
            $flag = ($callback)($this->getPathInfo());
            if ($flag) {
                return $this->getRunResult();
            }
        }
        
        if ($this->enable_default_callback) {
            $flag = $this->defaultRunRouteCallback($this->getPathInfo());
            if ($flag && (!$this->is_failed)) {
                return $this->getRunResult();
            }
        } else {
            $this->enable_default_callback = true;
        }
        
        foreach ($this->post_run_hook_list as $callback) {
            $flag = ($callback)($this->getPathInfo());
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
    protected function pathToClassAndMethod($path_info)
    {
        $path_info = ltrim((string)$path_info, '/');
        if (!empty($this->options['controller_path_ext']) && !empty($path_info)) {
            $l = strlen($this->options['controller_path_ext']);
            if (substr($path_info, -$l) !== $this->options['controller_path_ext']) {
                $this->route_error = "path_extention error";
                return [null, null];
            }
            $path_info = substr($path_info, 0, -$l);
        }
        
        $t = explode('/', $path_info);
        $method = array_pop($t);
        $path_class = implode('/', $t);
        
        $welcome_class = $this->options['controller_welcome_class'];
        $this->calling_path = $path_class?$path_info:$welcome_class.'/'.$method;
        
        if ($this->options['controller_hide_boot_class'] && $path_class === $welcome_class) {
            $this->route_error = "controller_hide_boot_class! {$welcome_class}; ";
            return [null, null];
        }
        $path_class = $path_class ?: $welcome_class;

        $full_class = $this->namespace_prefix.str_replace('/', '\\', $path_class).$this->options['controller_class_postfix'];
        $full_class = ''.ltrim($full_class, '\\');
        
        return [$full_class,$method];
    }
    public function defaultGetRouteCallback($path_info)
    {
        if ($this->options['controller_url_prefix'] ?? false) {
            $prefix = '/'.trim($this->options['controller_url_prefix'], '/').'/';
            $l = strlen($prefix);
            if (substr($path_info, 0, $l) !== $prefix) {
                $this->route_error = "path_prefix error";
                return null;
            }
            $path_info = substr($path_info, $l - 1);
            $path_info = ltrim((string)$path_info, '/');
        }
        $this->route_error = '';
        list($full_class, $method) = $this->pathToClassAndMethod($path_info);
        if ($full_class === null) {
            return null;
        }
        $this->calling_class = $full_class;
        $this->calling_method = !empty($method)?$method:$this->index_method;
        
        ////////////////////////
        try {
            /** @var class-string */ $class = $full_class;
            if ($full_class !== (new \ReflectionClass($class))->getName()) {
                $this->route_error = "can't find class($full_class) by $path_info .";
                return null;
            }
        } catch (\ReflectionException $ex) {
            $this->route_error = "can't Reflection class($full_class) by $path_info .";
            return null;
        }
        /** @var string */ $base_class = str_replace('~', $this->namespace_prefix, $this->options['controller_base_class']);
        if (!empty($base_class)) {
            if (!is_subclass_of($full_class, $base_class)) {
                $this->route_error = "no the controller_base_class! {$base_class} ";
                return null;
            }
        }
        
        $object = $this->createControllerObject($full_class);
        $route_error = $this->route_error;
        // @phpstan-ignore-next-line
        if ($route_error) {
            return null;
        }
        return $this->getMethodToCall($object, $method);
    }
    protected function createControllerObject($full_class)
    {
        $full_class = $this->options['controller_class_map'][$full_class] ?? $full_class;
        return new $full_class();
    }
    protected function getMethodToCall($object, $method)
    {
        $method = ($method === '') ? $this->index_method : $method;
        if (substr($method, 0, 2) == '__') {
            $this->route_error = 'can not call hidden method';
            return null;
        }
        if ($this->options['controller_prefix_post']) {
            $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
            $request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if ($request_method === 'POST' && method_exists($object, $this->options['controller_prefix_post'].$method)) {
                $method = $this->options['controller_prefix_post'].$method;
            }
        }
        try {
            $ref = new \ReflectionMethod($object, $method);
            if ($ref->isStatic()) {
                $this->route_error = 'can not call static function';
                return null;
            }
        } catch (\ReflectionException $ex) {
            $this->route_error = 'method can not call';
            return null;
        }
        return [$object,$method];
    }
}
trait Route_Helper
{
    ////
    public static function PathInfo($path_info = null)
    {
        return static::G()->_PathInfo($path_info);
    }
    public function _PathInfo($path_info = null)
    {
        return isset($path_info)?static::G()->setPathInfo($path_info):static::G()->getPathInfo();
    }
    protected function getPathInfo()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        return $_SERVER['PATH_INFO'] ?? '';
    }
    protected function setPathInfo($path_info)
    {
        // TODO protected
        $_SERVER['PATH_INFO'] = $path_info;
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            (__SUPERGLOBAL_CONTEXT)()->_SERVER = $_SERVER;
        }
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
    public function getControllerNamespacePrefix()
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
    public function replaceController($old_class, $new_class)
    {
        //$old_class::G((new \ReflectionClass($new_class))->newInstanceWithoutConstructor());
        $this->options['controller_class_map'][$old_class] = $new_class;
    }
}
trait Route_UrlManager
{
    protected $url_handler = null;
    public static function Url($url = null)
    {
        return static::G()->_Url($url);
    }
    public static function Res($url = null)
    {
        return static::G()->_Res($url);
    }
    public static function Domain($use_scheme = false)
    {
        return static::G()->_Domain($use_scheme);
    }
    public function _Url($url = null)
    {
        if ($this->url_handler) {
            return ($this->url_handler)($url);
        }
        return $this->defaultUrlHandler($url);
    }
    public function _Res($url = null)
    {
        if (!$this->options['controller_resource_prefix']) {
            return $this->_Url($url);
        }
        //
        //   'https://cdn.site/','http://cdn.site','//cdn.site/','res/'
        $flag = preg_match('/^(https?:\/)\//', $url ?? '');
        if ($flag) {
            return $url;
        }
        return $this->_Url('').'/'.$this->options['controller_resource_prefix'].$url;
    }
    public function _Domain($use_scheme = false)
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $scheme = $_SERVER['REQUEST_SCHEME'] ?? '';
        //$scheme = $use_scheme ? $scheme :'';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? ($_SERVER['SERVER_ADDR'] ?? ''));
        $host = $host ?? '';
        
        $port = $_SERVER['SERVER_PORT'] ?? '';
        $port = ($port == 443 && $scheme == 'https')?'':$port;
        $port = ($port == 80 && $scheme == 'http')?'':$port;
        $port = ($port)?(':'.$port):'';

        $host = (strpos($host, ':'))? strstr($host, ':', true) : $host;
        
        $ret = $scheme.':/'.'/'.$host.$port;
        if (!$use_scheme) {
            $ret = substr($ret, strlen($scheme) + 1);
        }
        return $ret;
    }
    protected function getUrlBasePath()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        //get basepath.
        $document_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $basepath = substr(rtrim($_SERVER['SCRIPT_FILENAME'], '/'), strlen($document_root));

        /* something wrong ?
        if (substr($basepath, -strlen('/index.php'))==='/index.php') {
            $basepath=substr($basepath, 0, -strlen('/index.php'));
        }
        */
        if ($basepath === '/index.php') {
            $basepath = '/';
        }
        $prefix = $this->options['controller_url_prefix']? trim('/'.$this->options['controller_url_prefix'], '/') : '';
        $basepath .= $prefix;
        return $basepath;
    }
    public function defaultUrlHandler($url = null)
    {
        if (isset($url) && strlen($url) > 0 && substr($url, 0, 1) === '/') {
            return $url;
        }
        $basepath = $this->getUrlBasePath();
        $path_info = $this->getPathInfo();

        if ('' === $url) {
            return $basepath;
        }
        if (isset($url) && '?' === substr($url, 0, 1)) {
            return $basepath.$path_info.$url;
        }
        if (isset($url) && '#' === substr($url, 0, 1)) {
            return $basepath.$path_info.$url;
        }
        
        return rtrim($basepath, '/').'/'.ltrim(''.$url, '/');
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
