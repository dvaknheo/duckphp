<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp\Core;

use DuckPhp\Core\Kernel;

use DuckPhp\Core\ThrowOn;
use DuckPhp\Core\ExtendableStaticCallTrait;
use DuckPhp\Core\SystemWrapper;

use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\RuntimeState;
use DuckPhp\Core\View;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\Logger;

class App
{
    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    use Kernel;
    use ThrowOn;
    use ExtendableStaticCallTrait;
    use SystemWrapper;
    
    //inner trait
    use Core_Handler;
    use Core_Helper;
    use Core_SystemWrapper;
    use Core_Glue;
    use Core_Component;

    // for kernel
    protected $hanlder_for_exception_handler;
    protected $hanlder_for_exception;
    protected $hanlder_for_develop_exception;
    protected $hanlder_for_404;
    
    // for helper
    public $componentClassMap = [
        'M' => 'Helper\ModelHelper',
        'V' => 'Helper\ViewHelper',
        'C' => 'Helper\ControllerHelper',
        'S' => 'Helper\ServiceHelper',
        'A' => 'Helper\AppHelper',
    ];
    // for trait
    protected $system_handlers = [
        'header' => null,
        'setcookie' => null,
        'exit' => null,
        'set_exception_handler' => null,
        'register_shutdown_function' => null,

        'session_start' => null,
        'session_id' => null,
        'session_destroy' => null,
        'session_set_save_handler' => null,

    ];
    // for trait
    protected $extDynamicComponentClasses = [];
    protected $beforeShowHandlers = [];
    protected $pager;
    
    public function __construct()
    {
        $this->hanlder_for_exception_handler = [static::class,'set_exception_handler'];
        $this->hanlder_for_exception = [static::class,'OnDefaultException'];
        $this->hanlder_for_develop_exception = [static::class,'OnDevErrorHandler'];
        $this->hanlder_for_404 = [static::class,'On404'];
    }
    public function extendComponents($method_map, $components = []): void
    {
        static::AssignExtendStaticMethod($method_map);
        
        $a = explode('\\', get_class($this));
        array_pop($a);
        $namespace = ltrim(implode('\\', $a).'\\', '\\');  // __NAMESPACE__
        
        foreach ($components as $component) {
            $class = $this->componentClassMap[strtoupper($component)] ?? null;
            $full_class = ($class === null)?$component:$namespace.$class;
            if (!class_exists($full_class)) {
                continue;
            }
            $full_class::AssignExtendStaticMethod($method_map);
        }
    }
    public function cloneHelpers($new_namespace, $componentClassMap = [])
    {
        if (empty($componentClassMap)) {
            $componentClassMap = $this->componentClassMap;
        }
        //Get Namespace.
        $a = explode('\\', get_class($this));
        array_pop($a);
        $namespace = ltrim(implode('\\', $a).'\\', '\\');
        
        foreach ($this->componentClassMap as $name => $class) {
            $new_class = $componentClassMap[$name] ?? null;
            if (!$new_class) {
                continue;
            }
            $old_class = $namespace.$class;
            $new_class = $new_namespace.$new_class;
            if (!class_exists($new_class) || !class_exists($old_class)) {
                continue;
            }
            $new_class::AssignExtendStaticMethod($old_class::GetExtendStaticMethodList());
        }
    }
}
trait Core_Handler
{
    //protected $beforeShowHandlers = [];
    //protected $error_view_inited = false;
    
    public static function On404(): void
    {
        static::G()->_On404();
    }
    public static function OnDefaultException($ex): void
    {
        static::G()->_OnDefaultException($ex);
    }
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        static::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
    }
    public function _On404(): void
    {
        Route::G()->forceFail();
        $error_view = $this->options['error_404'] ?? null;
        $error_view = $this->error_view_inited?$error_view:null;
        
        static::header('', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        //// no error_404 setting.
        if (!$error_view) {
            echo "404 File Not Found\n<!--DuckPhp set options ['error_404'] to override me   -->\n";
            return;
        }
        
        $this->setViewWrapper(null, null);
        $this->_Show([], $error_view);
    }
    
    public function _OnDefaultException($ex): void
    {
        if ($this->options['log_error']) {
            try{
                static::Logger()->error('['.get_class($ex).']('.$ex->getMessage().')'.$ex->getMessage());
            } catch(\Throwable $ex) {
                //do nothing
            }
        }
        if (method_exists($ex, 'display')) {
            $ex->display($ex);
            $this->clear();
            return;
        }
        $error_view = $this->options['error_500'] ?? null;
        $error_view = $this->error_view_inited?$error_view:null;
        
        static::header('', true, 500);
        $data = [];
        $data['is_debug'] = $this->is_debug;
        $data['ex'] = $ex;
        $data['class'] = get_class($ex);
        $data['message'] = $ex->getMessage();
        $data['code'] = $ex->getCode();
        $data['trace'] = $ex->getTraceAsString();
        $data['file'] = $ex->getFile();
        $data['line'] = $ex->getLine();
        
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            $this->clear();
            return;
        }
        ////////default;
        if (!$error_view) {
            echo "Internal Error \n<!--DuckPhp set options ['error_500'] to override me  -->\n";
            
            if ($data['is_debug']) {
                echo "<h3>{$data['class']}({$data['code']}):{$data['message']}</h3>";
                echo "<div>{$data['file']} : {$data['line']}</div>";
                echo "\n<pre>Debug On\n\n";
                echo $data['trace'];
                echo "\n</pre>\n";
            }
            return;
        }
        
        $this->setViewWrapper(null, null);
        $this->_Show($data, $error_view);
        $this->clear();
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        if (!$this->is_debug) {
            return;
        }
        $descs = array(
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_NOTICE => 'E_NOTICE',
            E_STRICT => 'E_STRICT',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        );
        $error_shortfile = $errfile;
        if (!empty($this->options['path'])) {
            $path = $this->options['path'];
            $error_shortfile = (substr($errfile, 0, strlen($path)) == $path)?substr($errfile, strlen($path)):$errfile;
        }
        $data = array(
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            'error_desc' => $descs[$errno],
            'error_shortfile' => $error_shortfile,
        );
        $error_view = $this->options['error_debug'] ?? '';
        $error_view = $this->error_view_inited?$error_view:null;
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        $error_desc = '';
        if (!$error_view) {
            extract($data);
            echo  <<<EOT
<!--DuckPhp  set options ['error_debug']='_sys/error-debug.php' to override me -->
<fieldset class="_DNMVC_DEBUG">
	<legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
</pre>
</fieldset>

EOT;
            return;
        }
        View::G()->_Display($error_view, $data);
    }
}

trait Core_SystemWrapper
{
    // use SystemWrapper;

    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return static::G()->_exit($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return static::G()->_register_shutdown_function($callback, ...$args);
    }
    public function _header($output, bool $replace = true, int $http_response_code = 0)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        ////
        if (PHP_SAPI === 'cli') {
            return;
        }
        // @codeCoverageIgnoreStart
        if (headers_sent()) {
            return;
        }
        header($output, $replace, $http_response_code);
        return;
        // @codeCoverageIgnoreEnd
    }
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        return setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function _exit($code = 0)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        exit($code);        // @codeCoverageIgnore
    }
    public function _set_exception_handler(callable $exception_handler)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            return $this->system_wrapper_call(__FUNCTION__, func_get_args());
        }
        return set_exception_handler($exception_handler);
    }
    public function _register_shutdown_function(callable $callback, ...$args)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        register_shutdown_function($callback, ...$args);
    }
}
trait Core_Helper
{
//    protected $pager;

    public static function ExitJson($ret, $exit = true)
    {
        return static::G()->_ExitJson($ret, $exit);
    }
    public static function ExitRedirect($url, $exit = true)
    {
        return static::G()->_ExitRedirect($url, $exit);
    }
    public static function ExitRedirectOutside($url, $exit = true)
    {
        return static::G()->_ExitRedirectOutside($url, $exit);
    }
    public static function ExitRouteTo($url, $exit = true)
    {
        return static::G()->_ExitRedirect(static::URL($url), $exit);
    }
    public static function Exit404($exit = true)
    {
        static::On404();
        if ($exit) {
            static::exit();
        }
    }
    ////
    public function _ExitJson($ret, $exit = true)
    {
        static::header('Content-Type:text/json');
        
        $flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if ($this->is_debug) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        echo json_encode($ret, $flag);
        if ($exit) {
            static::exit();
        }
    }
    public function _ExitRedirect($url, $exit = true)
    {
        if (parse_url($url, PHP_URL_HOST)) {
            static::exit();
            return;
        }
        static::header('location: '.$url, true, 302);
        if ($exit) {
            static::exit();
        }
    }
    public function _ExitRedirectOutside($url, $exit = true)
    {
        static::header('location: '.$url, true, 302);
        if ($exit) {
            static::exit();
        }
    }

    // system static
    public static function Platform()
    {
        return static::G()->platform;
    }
    public static function IsDebug()
    {
        return static::G()->is_debug;
    }
    public static function IsRealDebug()
    {
        return static::G()->_IsRealDebug();
    }
    public function _IsRealDebug()
    {
        //you can override this;
        return $this->is_debug;
    }
    
    public static function InException()
    {
        return RuntimeState::G()->isInException();
    }
    ////
    
    public static function Show($data = [], $view = null)
    {
        return static::G()->_Show($data, $view);
    }
    public static function H($str)
    {
        return static::G()->_H($str);
    }
    public static function L($str, $args = [])
    {
        return static::G()->_L($str, $args);
    }
    public static function HL($str, $args = [])
    {
        return static::H(static::L($str, $args));
    }
    public function _L($str, $args = [])
    {
        //TODO locale and do
        if (empty($args)) {
            return $str;
        }
        $a = [];
        foreach ($args as $k => $v) {
            $a["{$k}"] = $v;
        }
        $ret = str_replace(array_keys($a), array_values($a), $str);
        return $ret;
    }
    ////
    public function _Show($data = [], $view = null)
    {
        $view = $view ?? Route::G()->getRouteCallingPath();
        
        if ($this->is_debug) {
            View::G()->assignViewData([
                '__is_debug' => $this->is_debug,
                '__duckphp_is_debug' => $this->is_debug,
                '__duckphp_platform' => $this->platform,
            ]);
        }
        return View::G()->_Show($data, $view);
    }
    public function _H(&$str)
    {
        if (is_string($str)) {
            $str = htmlspecialchars($str, ENT_QUOTES);
            return $str;
        }
        if (is_array($str)) {
            foreach ($str as $k => &$v) {
                static::_H($v);
            }
            return $str;
        }
        return $str;
    }
    // ViewHelper
    public static function trace_dump()
    {
        return static::G()->_trace_dump();
    }
    public static function var_dump(...$args)
    {
        return static::G()->_var_dump(...$args);
    }
    public function _trace_dump()
    {
        if (!$this->is_debug) {
            return;
        }
        echo "<pre>\n";
        echo (new \Exception('', 0))->getTraceAsString();
        echo "</pre>\n";
    }
    public function _var_dump(...$args)
    {
        if (!$this->is_debug) {
            return;
        }
        echo "<pre>\n";
        var_dump(...$args);
        echo "</pre>\n";
    }
    public static function Domain()
    {
        return static::G()->_Domain();
    }
    public function _Domain()
    {
        $scheme = static::SG()->_SERVER['REQUEST_SCHEME'] ?? '';
        $host = static::SG()->_SERVER['HTTP_HOST'] ?? (static::SG()->SERVER['SERVER_NAME'] ?? (static::SG()->_SERVER['SERVER_ADDR'] ?? ''));
        $host = $host ?? '';
        
        $port = static::SG()->_SERVER['SERVER_PORT'] ?? '';
        $port = ($port == 443 && $scheme == 'https')?'':$port;
        $port = ($port == 80 && $scheme == 'http')?'':$port;
        $port = ($port)?(':'.$port):'';

        $host = (strpos($host, ':'))? strstr($host, ':', true) : $host;
        
        $ret = $scheme.':/'.'/'.$host.$port;
        return $ret;
    }
    
    public static function SQLForPage($sql, $pageNo, $pageSize = 10)
    {
        return static::G()->_SQLForPage($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply($sql)
    {
        return static::G()->_SqlForCountSimply($sql);
    }
    public function _SqlForPage($sql, $pageNo, $pageSize = 10)
    {
        $pageSize = (int)$pageSize;
        $start = ((int)$pageNo - 1) * $pageSize;
        $start = (int)$start;
        $sql .= " LIMIT $start,$pageSize";
        return $sql;
    }
    public function _SqlForCountSimply($sql)
    {
        $sql = preg_replace_callback('/^\s*select\s(.*?)\sfrom\s/is', function ($m) {
            return 'SELECT COUNT(*) as c FROM ';
        }, $sql);
        return $sql;
    }
    
    public static function Logger($object = null)
    {
        return Logger::G($object);
    }
    public static function Pager($object = null)
    {
        return static::G()->_Pager($object);
    }
    public function _Pager($object = null)
    {
        if ($object) {
            $this->pager = $object;
        }
        return $this->pager;
    }
    
    public static function PageNo()
    {
        return static::Pager()->current();
    }
    public static function PageSize($new_value = null)
    {
        return static::Pager()->pageSize($new_value);
    }
    public static function PageHtml($total, $options = [])
    {
        return static::Pager()->render($total, $options);
    }
}


trait Core_Glue
{
    //// source is static ////
    //state
    public static function IsRunning()
    {
        return RuntimeState::G()->isRunning();
    }
    // route static
    public static function URL($url = null)
    {
        return Route::G()->_URL($url);
    }
    // view static

    public static function Display($view, $data = null)
    {
        return View::G()->_Display($view, $data);
    }
    // config static
    public static function Setting($key)
    {
        return Configer::G()->_Setting($key);
    }
    public static function Config($key, $file_basename = 'config')
    {
        return Configer::G()->_Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return Configer::G()->_LoadConfig($file_basename);
    }
    
    //// the next is dynamic ////
    //autoloader
    public static function assignPathNamespace($path, $namespace = null)
    {
        return AutoLoader::G()->assignPathNamespace($path, $namespace);
    }
    // route
    public static function getPathInfo()
    {
        return Route::G()->getPathInfo();
    }
    public static function getParameters()
    {
        return Route::G()->getParameters();
    }
    public static function addRouteHook($hook, $position, $once = true)
    {
        return Route::G()->addRouteHook($hook, $position, $once);
    }
    public static function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
    }
    public static function setRouteCallingMethod(string $method)
    {
        return Route::G()->setRouteCallingMethod($method);
    }
    public static function setURLHandler($callback)
    {
        return Route::G()->setURLHandler($callback);
    }
    //view
    public static function setViewWrapper($head_file = null, $foot_file = null)
    {
        return View::G()->setViewWrapper($head_file, $foot_file);
    }
    public static function assignViewData($key, $value = null)
    {
        return View::G()->assignViewData($key, $value);
    }
    //exception manager
    public static function assignExceptionHandler($classes, $callback = null)
    {
        return ExceptionManager::G()->assignExceptionHandler($classes, $callback);
    }
    public static function setMultiExceptionHandler(array $classes, callable $callback)
    {
        return ExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
    }
    public static function setDefaultExceptionHandler(callable $callback)
    {
        return ExceptionManager::G()->setDefaultExceptionHandler($callback);
    }
    public static function OnException($ex)
    {
        return ExceptionManager::G()->_OnException($ex);
    }
    //super global
    public static function SG($replacement_object = null)
    {
        return SuperGlobal::G($replacement_object);
    }
    public static function &GLOBALS($k, $v = null)
    {
        return SuperGlobal::G()->_GLOBALS($k, $v);
    }
    public static function &STATICS($k, $v = null, $_level = 1)
    {
        return SuperGlobal::G()->_STATICS($k, $v, $_level);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
    ////super global
    public static function session_start(array $options = [])
    {
        return SuperGlobal::G()->session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return SuperGlobal::G()->session_id($session_id);
    }
    public static function session_destroy()
    {
        return SuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SuperGlobal::G()->session_set_save_handler($handler);
    }
}
trait Core_Component
{
    //protected $componentClassMap;
    //protected $extDynamicComponentClasses = [];
    
    public function getStaticComponentClasses()
    {
        $ret = [
            self::class,
            AutoLoader::class,
            ExceptionManager::class,
            Configer::class,
            Route::class,
            Logger::class,
            View::class,
        ];
        if (!in_array(static::class, $ret)) {
            $ret[] = static::class;
        }
        return $ret;
    }
    public function getDynamicComponentClasses()
    {
        $ret = [
            RuntimeState::class,
            SuperGlobal::class,
            View::class,
        ];
        $ret = array_merge($ret, $this->extDynamicComponentClasses);
        return $ret;
    }
    public function addDynamicComponentClass($class)
    {
        $this->extDynamicComponentClasses[] = $class;
    }
    public function removeDynamicComponentClass($class)
    {
        array_filter($this->extDynamicComponentClasses, function ($v) use ($class) {
            return $v !== $class?true:false;
        });
    }
}
