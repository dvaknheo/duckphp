<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\ExtendableStaticCallTrait;
use DuckPhp\Core\Kernel;
use DuckPhp\Core\Logger;
use DuckPhp\Core\Route;
use DuckPhp\Core\RuntimeState;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapperTrait;
use DuckPhp\Core\View;

/**
 * MAIN FILE
 * dvaknheo@github.com
 * OK，Lazy
 *
 */
class App extends ComponentBase
{
    const VERSION = '1.2.8';

    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    const DEFAULT_INJECTED_HELPER_MAP = '~\\Helper\\';
    
    use Kernel;
    use ExtendableStaticCallTrait;
    use SystemWrapperTrait;
    
    //inner trait
    use Core_Handler;
    use Core_Helper;
    use Core_SystemWrapper;
    use Core_Glue;
    use Core_NotImplemented;
    use Core_Component;
    
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
    
    // from kernel
    protected $hanlder_for_exception_handler;
    protected $hanlder_for_exception;
    protected $hanlder_for_develop_exception;
    protected $hanlder_for_404;
    
    // for trait
    protected $extDynamicComponentClasses = [];
    protected $beforeShowHandlers = [];
    protected $pager;
    protected $cache;
    
    protected $core_options = [
        'default_exception_do_log' => true,
        'default_exception_self_display' => true,
        'close_resource_at_output' => false,
        'injected_helper_map' => '',
        
        //// error handler ////
        'error_404' => null,          //'_sys/error-404',
        'error_500' => null,          //'_sys/error-500',
        'error_debug' => null,        //'_sys/error-debug',


    ];
    public function __construct()
    {
        parent::__construct();
        $this->options = array_replace_recursive(static::$options_default, $this->core_options, $this->options);
        unset($this->core_options); // not use again;
        $this->hanlder_for_exception_handler = [static::class,'set_exception_handler'];
        $this->hanlder_for_exception = [static::class,'OnDefaultException'];
        $this->hanlder_for_develop_exception = [static::class,'OnDevErrorHandler'];
        $this->hanlder_for_404 = [static::class,'On404'];
    }
    protected function extendComponentClassMap($map, $namespace)
    {
        if (empty($map)) {
            return [];
        }
        if (is_string($map)) {
            // for helper
            $map = [
                'A' => $map . 'AppHelper',
                'B' => $map . 'BusinessHelper',
                'C' => $map . 'ControllerHelper',
                'M' => $map . 'ModelHelper',
                'V' => $map . 'ViewHelper',
            ];
        }
        return $map;
    }
    protected function fixNamespace($class, $namespace)
    {
        $class = str_replace('~', $namespace, $class);
        $class = str_replace("\\\\", "\\", $class);
        return $class;
    }
    public function extendComponents($method_map, $components = [])
    {
        static::AssignExtendStaticMethod($method_map);
        self::AssignExtendStaticMethod($method_map);
        
        $this->options['injected_helper_map'] = $this->extendComponentClassMap($this->options['injected_helper_map'], $this->options['namespace']);
        foreach ($components as $component) {
            $class = $this->options['injected_helper_map'][strtoupper($component)] ?? null;
            $class = ($class === null) ? $component : $class;
            $class = $this->fixNamespace($class, $this->options['namespace']);
            
            if (!class_exists($class)) {
                continue;
            }
            $class::AssignExtendStaticMethod($method_map);
        }
    }
    public function cloneHelpers($new_namespace, $new_helper_map = [])
    {
        if (empty($new_helper_map)) {
            return;
        }
        $helperMap = $this->extendComponentClassMap($this->options['injected_helper_map'], $this->options['namespace']);

        foreach ($helperMap as $name => $old_class) {
            $new_class = $new_helper_map[$name] ?? null;
            if (!$new_class) {
                continue;
            }
            $old_class = $this->fixNamespace($old_class, $this->options['namespace']);
            $new_class = $this->fixNamespace($new_class, $new_namespace);
            if (!class_exists($old_class) || !class_exists($new_class)) {
                continue;
            }
            $new_class::AssignExtendStaticMethod($old_class::GetExtendStaticMethodList());
        }
    }
    
    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[] = $handler;
    }
    public function removeBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers = array_filter($this->beforeShowHandlers, function ($v) use ($handler) {
            return $v != $handler;
        });
    }
    public function version()
    {
        return static::VERSION;
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
        
        $this->setViewHeadFoot(null, null);
        $this->_Show([], $error_view);
    }
    
    public function _OnDefaultException($ex): void
    {
        if ($this->options['default_exception_do_log']) {
            try {
                static::Logger()->error('['.get_class($ex).']('.$ex->getMessage().')'.$ex->getMessage());
            } catch (\Throwable $ex) { // @codeCoverageIgnore
                //do nothing
            } // @codeCoverageIgnore
        }
        if ($this->options['default_exception_self_display'] && method_exists($ex, 'display')) {
            $ex->display($ex);
            return;
        }
        $error_view = $this->options['error_500'] ?? null;
        $error_view = $this->error_view_inited?$error_view:null;
        
        static::header('', true, 500);
        $data = [];
        $data['is_debug'] = $this->options['is_debug'];
        $data['ex'] = $ex;
        $data['class'] = get_class($ex);
        $data['message'] = $ex->getMessage();
        $data['code'] = $ex->getCode();
        $data['trace'] = $ex->getTraceAsString();
        $data['file'] = $ex->getFile();
        $data['line'] = $ex->getLine();
        
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
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
        
        $this->setViewHeadFoot(null, null);
        $this->_Show($data, $error_view);
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        if (!$this->_IsDebug()) {
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
<fieldset class="_DuckPhp_DEBUG">
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
        /** @var mixed */
        $handler = $exception_handler; //for phpstan
        return set_exception_handler($handler);
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
        return static::G()->_ExitRedirect(static::Url($url), $exit);
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
        if ($this->_IsDebug()) {
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
        return static::G()->_Platform();
    }
    public function _Platform()
    {
        return $this->options['platform'];
    }
    public static function IsDebug()
    {
        return static::G()->_IsDebug();
    }
    public function _IsDebug()
    {
        return static::G()->options['is_debug'];
    }
    public static function IsRealDebug()
    {
        return static::G()->_IsRealDebug();
    }
    public function _IsRealDebug()
    {
        //you can override this;
        return $this->options['is_debug'];
    }
    public static function Show($data = [], $view = '')
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
    public static function Hl($str, $args = [])
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
    protected function onBeforeOutput()
    {
        //if (!$this->options['close_resource_at_output']) {
        //    return;
        //}
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
    }
    public function _Show($data = [], $view = '')
    {
        $this->onBeforeOutput();
        $view = $view === '' ? Route::G()->getRouteCallingPath() : $view;
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
        if (!$this->options['is_debug']) {
            return;
        }
        echo "<pre>\n";
        echo (new \Exception('', 0))->getTraceAsString();
        echo "</pre>\n";
    }
    public function _var_dump(...$args)
    {
        if (!$this->options['is_debug']) {
            return;
        }
        echo "<pre>\n";
        var_dump(...$args);
        echo "</pre>\n";
    }
    public static function XCall($callback, ...$args)
    {
        return static::G()->_XCall($callback, ...$args);
    }
    public function _XCall($callback, ...$args)
    {
        try {
            return ($callback)(...$args);
        } catch (\Exception $ex) {
            return $ex;
        }
    }
    public static function Domain()
    {
        return static::G()->_Domain();
    }
    public function _Domain()
    {
        $scheme = SuperGlobal::G()->_SERVER['REQUEST_SCHEME'] ?? '';
        $host = SuperGlobal::G()->_SERVER['HTTP_HOST'] ?? (SuperGlobal::G()->SERVER['SERVER_NAME'] ?? (SuperGlobal::G()->_SERVER['SERVER_ADDR'] ?? ''));
        $host = $host ?? '';
        
        $port = SuperGlobal::G()->_SERVER['SERVER_PORT'] ?? '';
        $port = ($port == 443 && $scheme == 'https')?'':$port;
        $port = ($port == 80 && $scheme == 'http')?'':$port;
        $port = ($port)?(':'.$port):'';

        $host = (strpos($host, ':'))? strstr($host, ':', true) : $host;
        
        $ret = $scheme.':/'.'/'.$host.$port;
        return $ret;
    }
    public static function ThrowOn($flag, $message, $code = 0, $exception_class = null)
    {
        return static::G()->_ThrowOn($flag, $message, $code, $exception_class);
    }
    public function _ThrowOn($flag, $message, $code = 0, $exception_class = null)
    {
        if (!$flag) {
            return;
        }
        $exception_class = $exception_class?:\Exception::class;
        throw new $exception_class($message, $code);
    }
    ////
    public static function SqlForPager($sql, $pageNo, $pageSize = 10)
    {
        return static::G()->_SqlForPager($sql, $pageNo, $pageSize);
    }
    public static function SqlForCountSimply($sql)
    {
        return static::G()->_SqlForCountSimply($sql);
    }
    public function _SqlForPager($sql, $pageNo, $pageSize = 10)
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
    public static function debug_log($message, array $context = array())
    {
        return static::G()->_debug_log($message, $context);
    }
    public function _debug_log($message, array $context = array())
    {
        if ($this->options['is_debug']) {
            return Logger::G()->debug($message, $context);
        }
        return false;
    }
    public static function Cache($object = null)
    {
        return static::G()->_Cache($object);
    }
    public function _Cache($object = null)
    {
        if ($object) {
            $this->cache = $object;
        }
        return $this->cache;
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
    public static function PageNo($new_value = null)
    {
        return static::Pager()->current($new_value);
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
trait Core_NotImplemented
{
    public static function Db($tag = null)
    {
        return static::G()->_Db($tag);
    }
    public static function DbCloseAll()
    {
        return static::G()->_DbCloseAll();
    }
    public static function DbForWrite()
    {
        return static::G()->_DbForWrite();
    }
    public static function DbForRead()
    {
        return static::G()->_DbForRead();
    }
    public static function Event()
    {
        return static::G()->_Event();
    }
    public static function FireEvent($event, ...$args)
    {
        return static::G()->_FireEvent($event, ...$args);
    }
    public static function OnEvent($event, $callback)
    {
        return static::G()->_OnEvent($event, $callback);
    }
    
    public function _DbCloseAll()
    {
        return; // do nothing. for override
    }
    public function _Db($tag)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _DbForRead()
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _Event()
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _DbForWrite()
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _FireEvent($event, ...$args)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _OnEvent($event, $callback)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
}


trait Core_Glue
{
    //// source is static ////
    //runtime state
    public static function isInException()
    {
        return RuntimeState::G()->isInException();
    }
    public static function isRunning()
    {
        return RuntimeState::G()->isRunning();
    }
    // route static
    public static function Url($url = null)
    {
        return Route::G()->_Url($url);
    }
    public static function Parameter($key, $default = null)
    {
        return Route::G()->_Parameter($key, $default);
    }
    // view static

    public static function Display($view, $data = null)
    {
        return View::G()->_Display($view, $data);
    }
    public static function getViewData()
    {
        return View::G()->getViewData();
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
    public static function Route($replacement_object = null)
    {
        return Route::G($replacement_object);
    }
    public static function replaceControllerSingelton($old_class, $new_class)
    {
        return Route::G()->replaceControllerSingelton($old_class, $new_class);
    }
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
    public static function add404RouteHook($callback)
    {
        return Route::G()->add404RouteHook($callback);
    }
    public static function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
    }
    public static function setRouteCallingMethod(string $method)
    {
        return Route::G()->setRouteCallingMethod($method);
    }
    public static function setUrlHandler($callback)
    {
        return Route::G()->setUrlHandler($callback);
    }
    public static function dumpAllRouteHooksAsString()
    {
        return Route::G()->dumpAllRouteHooksAsString();
    }
    //view
    public static function setViewHeadFoot($head_file = null, $foot_file = null)
    {
        return View::G()->setViewHeadFoot($head_file, $foot_file);
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
    public static function CallException($ex)
    {
        return ExceptionManager::G()->_CallException($ex);
    }
    //super global
    public static function SuperGlobal($replacement_object = null)
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
    
    public static function GET($key = null, $default = null)
    {
        if (isset($key)) {
            return SuperGlobal::G()->_GET[$key] ?? $default;
        } else {
            return SuperGlobal::G()->_GET ?? $default;
        }
    }
    public static function POST($key = null, $default = null)
    {
        if (isset($key)) {
            return SuperGlobal::G()->_POST[$key] ?? $default;
        } else {
            return SuperGlobal::G()->_POST ?? $default;
        }
    }
    public static function REQUEST($key = null, $default = null)
    {
        if (isset($key)) {
            return SuperGlobal::G()->_REQUEST[$key] ?? $default;
        } else {
            return SuperGlobal::G()->_REQUEST ?? $default;
        }
    }
    public static function COOKIE($key = null, $default = null)
    {
        if (isset($key)) {
            return SuperGlobal::G()->_COOKIE[$key] ?? $default;
        } else {
            return SuperGlobal::G()->_COOKIE ?? $default;
        }
    }
    public static function SERVER($key = null, $default = null)
    {
        if (isset($key)) {
            return SuperGlobal::G()->_SERVER[$key] ?? $default;
        } else {
            return SuperGlobal::G()->_SERVER ?? $default;
        }
    }
}
trait Core_Component
{
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
        foreach ($this->options['ext'] as $class => $v) {
            $ret[] = $class;
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
