<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;

class MiniRoute extends ComponentBase
{
    public $options = [
        'namespace' => '',
        'namespace_controller' => 'Controller',
        
        'controller_path_ext' => '',
        'controller_welcome_class' => 'Main',
        'controller_welcome_class_visible' => false,
        'controller_welcome_method' => 'index',
        
        'controller_class_postfix' => '',
        'controller_method_prefix' => '',
        
        'controller_class_map' => [],
        
        'controller_resource_prefix' => '',
        'controller_url_prefix' => '',
    ];

    protected $route_error = '';
    protected $calling_path = '';
    protected $calling_class = '';
    protected $calling_method = '';
    
    public static function Route()
    {
        return static::_();
    }
    public function run()
    {
        $path_info = $this->getPathInfo();
        $callback = $this->defaultGetRouteCallback($path_info);
        if (null === $callback) {
            return false;
        }
        ($callback)();
        return true;
    }
    protected function pathToClassAndMethod($path_info)
    {
        if ($this->options['controller_url_prefix'] ?? false) {
            $prefix = '/'.trim($this->options['controller_url_prefix'], '/').'/';
            $l = strlen($prefix);
            if (substr($path_info, 0, $l) !== $prefix) {
                $this->route_error = "E001: url: $path_info controller_url_prefix($prefix) error";
                return null;
            }
            $path_info = substr($path_info, $l - 1);
            $path_info = ltrim((string)$path_info, '/');
        }
        $path_info = ltrim((string)$path_info, '/');
        if (!empty($this->options['controller_path_ext']) && !empty($path_info)) {
            $l = strlen($this->options['controller_path_ext']);
            if (substr($path_info, -$l) !== $this->options['controller_path_ext']) {
                $this->route_error = "E008: path_extention error";
                return [null, null];
            }
            $path_info = substr($path_info, 0, -$l);
        }
        
        $t = explode('/', $path_info);
        $method = array_pop($t);
        $path_class = implode('/', $t);
        
        $welcome_class = $this->options['controller_welcome_class'];
        $this->calling_path = $path_class?$path_info:$welcome_class.'/'.$method;
        
        if (!$this->options['controller_welcome_class_visible'] && $path_class === $welcome_class) {
            $this->route_error = "E009: controller_welcome_class_visible! {$welcome_class}; ";
            return [null, null];
        }
        $path_class = $path_class ?: $welcome_class;

        $full_class = $this->getControllerNamespacePrefix().str_replace('/', '\\', $path_class).$this->options['controller_class_postfix'];
        $full_class = ''.ltrim($full_class, '\\');
        $full_class = $this->options['controller_class_map'][$full_class] ?? $full_class;
        
        $method = ($method === '') ? $this->options['controller_welcome_method'] : $method;
        $method = $this->options['controller_method_prefix'].$method;
        return [$full_class,$method];
    }
    public function defaultGetRouteCallback($path_info)
    {
        $this->route_error = '';
        
        list($full_class, $method) = $this->pathToClassAndMethod($path_info);
        if ($full_class === null) {
            return null;
        }
        $this->calling_class = $full_class;
        $this->calling_method = $method;
        ////////
        try {
            $ref = new \ReflectionClass($full_class);
            if ($full_class !== $ref->getName()) {
                $this->route_error = "E002: can't find class($full_class) by $path_info .";
                return null;
            }
            // my_class_action__x ?
            if (substr($method, 0, 1) === '_') {
                $this->route_error = 'E005: can not call hidden method';
                return null;
            }
            try {
                $object = $ref->newInstance();
                $ref = new \ReflectionMethod($object, $method);
                if ($ref->isStatic()) {
                    $this->route_error = "E006: can not call static method({$method})";
                    return null;
                }
            } catch (\ReflectionException $ex) {
                $this->route_error = "E007: method can not call({$method})";
                return null;
            }
        } catch (\ReflectionException $ex) {
            $this->route_error = "E003: can't Reflection class($full_class) by $path_info .". $ex->getMessage();
            return null;
        }
        return [$object,$method];
    }
    public function getControllerNamespacePrefix()
    {
        $namespace_controller = $this->options['namespace_controller'];
        if (substr($namespace_controller, 0, 1) !== '\\') {
            $namespace_controller = rtrim($this->options['namespace'], '\\').'\\'.$namespace_controller;
        }
        $namespace_controller = trim($namespace_controller, '\\').'\\';
        
        return $namespace_controller;
    }
    public function replaceController($old_class, $new_class)
    {
        $this->options['controller_class_map'][$old_class] = $new_class;
    }
    public static function PathInfo($path_info = null)
    {
        return static::_()->_PathInfo($path_info);
    }
    public function _PathInfo($path_info = null)
    {
        return isset($path_info)?static::_()->setPathInfo($path_info):static::_()->getPathInfo();
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
    public static function Url($url = null)
    {
        return static::_()->_Url($url);
    }
    public static function Res($url = null)
    {
        return static::_()->_Res($url);
    }
    public static function Domain($use_scheme = false)
    {
        return static::_()->_Domain($use_scheme);
    }
    public function _Url($url = null)
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
    public function _Res($url = null)
    {
        if (!$this->options['controller_resource_prefix']) {
            return $this->_Url($url);
        }
        //
        //   'https://cdn.site/','http://cdn.site','//cdn.site/','res/'
        $flag = preg_match('/^(https?:\/)?\//', $url ?? '');
        //TODO './' => '',
        if ($flag) {
            return $url;
        }
        $flag = preg_match('/^(https?:\/)?\//', $this->options['controller_resource_prefix'] ?? '');
        if ($flag) {
            return $this->options['controller_resource_prefix'].$url;
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
        //$document_root =  !empty($document_root)?$document_root:'/';
        $basepath = substr(rtrim($_SERVER['SCRIPT_FILENAME'], '/'), strlen($document_root));
        $basepath = ($basepath === '') ? '/' : $basepath;
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
}
