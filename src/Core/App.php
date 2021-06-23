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
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\Logger;
use DuckPhp\Core\Route;
use DuckPhp\Core\RuntimeState;
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
    const VERSION = '1.2.12-dev';

    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    const DEFAULT_INJECTED_HELPER_MAP = '~\\Helper\\';
    
    use KernelTrait;
    use ExtendableStaticCallTrait;
    use SystemWrapperTrait;
    
    //inner trait
    use Core_Helper;
    use Core_SystemWrapper;
    use Core_Glue;
    use Core_NotImplemented;
    use Core_SuperGlobal;
    
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
    protected $cache;
    
    public function __construct()
    {
        parent::__construct();
        $this->options = array_replace_recursive(static::$options_default, $this->core_options, $this->options);
        unset($this->core_options); // not use again;
    }
    public function version()
    {
        return static::VERSION;
    }
    //////// override KernelTrait ////////
    //@override
    public function _On404(): void
    {
        $error_view = $this->options['error_404'] ?? null;
        $error_view = $this->error_view_inited?$error_view:null;
        
        static::header('404 Not Found', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        //// no error_404 setting.
        if (!$error_view) {
            $path_info = $_SERVER['PATH_INFO'] ?? '';
            echo "404 File Not Found<!--PATH_INFO: ($path_info) DuckPhp set options ['error_404'] to override me. -->\n";
            return;
        }
        
        View::G(new View())->init($this->options);
        $this->onBeforeOutput();
        View::G()->_Show([], $error_view);
    }
    //@override
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
        
        static::header('Server Error', true, 500);
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
            } else {
                echo "<!-- DuckPhp set options ['is_debug'] to show debug info>\n";
            }
            return;
        }
        
        View::G(new View())->init($this->options);
        $this->onBeforeOutput();
        View::G()->_Show($data, $error_view);
    }
    //@override
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
    //////// features
    protected function extendComponentClassMap($map, $namespace)
    {
        if (empty($map)) {
            return [];
        }
        if (is_string($map)) {
            // for helper
            $map = [
                'A' => $map . 'AdvanceHelper',
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
    
    public function addDynamicComponentClass($class)
    {
        $this->extDynamicComponentClasses[] = $class;
    }    //////// for DuckPhp\HttpServer\AppInterface

    public function skip404Handler()
    {
        $this->options['skip_404_handler'] = true;
    }
    protected function onBeforeOutput()
    {
        //if (!$this->options['close_resource_at_output']) {
        //    return;
        //}
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
    }
    public static function Show($data = [], $view = '')
    {
        return static::G()->_Show($data, $view);
    }
    public function _Show($data = [], $view = '')
    {
        $this->onBeforeOutput();
        $view = $view === '' ? Route::G()->getRouteCallingPath() : $view;
        return View::G()->_Show($data, $view);
    }
    public static function IsAjax()
    {
        return static::G()->_IsAjax();
    }
    public function _IsAjax()
    {
        $ref = $this->_SERVER('HTTP_X_REQUESTED_WITH');
        return $ref && 'xmlhttprequest' == strtolower($ref) ? true : false;
    }
    public static function CheckRunningController($self, $static)
    {
        return static::G()->_CheckRunningController($self, $static);
    }
    public function _CheckRunningController($self, $static)
    {
        if ($self === $static) {
            if ($self === Route::G()->getRouteCallingClass()) {
                static::Exit404();
            }
            return true;
        }
        return false;
    }
}

trait Core_SystemWrapper
{
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
    public static function session_start(array $options = [])
    {
        return static::G()->_session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return static::G()->_session_id($session_id);
    }
    public static function session_destroy()
    {
        return static::G()->_session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return static::G()->_session_set_save_handler($handler);
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
    ////[[[[
    public function _session_start(array $options = [])
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        return @session_start($options);
    }
    public function _session_id($session_id = null)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        if (!isset($session_id)) {
            return session_id();
        }
        return session_id($session_id);
    }
    public function _session_destroy()
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        return session_destroy();
    }
    public function _session_set_save_handler(\SessionHandlerInterface $handler)
    {
        if ($this->system_wrapper_call_check(__FUNCTION__)) {
            $this->system_wrapper_call(__FUNCTION__, func_get_args());
            return;
        }
        return session_set_save_handler($handler);
    }
    ////]]]]
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
        $this->_header('Content-Type:application/json; charset=utf-8');
        echo $this->_Json($ret);
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
        return static::G()->_Hl($str, $args);
    }
    public static function Json($data)
    {
        return static::G()->_Json($data);
    }
    public function _L($str, $args = [])
    {
        //Override for locale and so do
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
    public function _Hl($str, $args)
    {
        $t = $this->_L($str, $args);
        return $this->_H($t);
    }
    public function _Json($data)
    {
        $flag = JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK;
        if ($this->_IsDebug()) {
            $flag = $flag | JSON_PRETTY_PRINT;
        }
        return json_encode($data, $flag);
    }
    ////
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
    public static function TraceDump()
    {
        return static::G()->_TraceDump();
    }
    public static function var_dump(...$args)
    {
        return static::G()->_var_dump(...$args);
    }
    public function _TraceDump()
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
    public static function XpCall($callback, ...$args)
    {
        return static::G()->_XpCall($callback, ...$args);
    }
    public function _XpCall($callback, ...$args)
    {
        try {
            return ($callback)(...$args);
        } catch (\Exception $ex) {
            return $ex;
        }
    }
    public static function CheckException($exception_class, $message, $code = 0)
    {
        return static::G()->_CheckException($exception_class, $message, $code);
    }
    public function _CheckException($exception_class, $flag, $message, $code = 0)
    {
        if ($flag) {
            throw new $exception_class($message, $code);
        }
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
    public static function DebugLog($message, array $context = array())
    {
        return static::G()->_DebugLog($message, $context);
    }
    public function _DebugLog($message, array $context = array())
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

    public function _DbForWrite()
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _Event()
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _FireEvent($event, ...$args)
    {
        return; // do nothing. for override
    }
    public function _OnEvent($event, $callback)
    {
        return; // do nothing. for override
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
    public static function Domain($use_scheme = false)
    {
        return Route::G()->_Domain($use_scheme);
    }
    public static function Parameter($key = null, $default = null)
    {
        return Route::G()->_Parameter($key, $default);
    }
    // view static

    public static function Display($view, $data = null)
    {
        return View::G()->_Display($view, $data);
    }
    public static function Render($view, $data = null)
    {
        return View::G()->_Render($view, $data);
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
    //
    public static function runAutoLoader()
    {
        return AutoLoader::G()->runAutoLoader();
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
    public static function addRouteHook($callback, $position, $once = true)
    {
        return Route::G()->addRouteHook($callback, $position, $once);
    }
    public static function add404RouteHook($callback)
    {
        return Route::G()->add404RouteHook($callback);
    }
    public static function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
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
}
trait Core_SuperGlobal
{
    public static function GET($key = null, $default = null)
    {
        return static::G()->_Get($key, $default);
    }
    public static function POST($key = null, $default = null)
    {
        return static::G()->_POST($key, $default);
    }
    public static function REQUEST($key = null, $default = null)
    {
        return static::G()->_REQUEST($key, $default);
    }
    public static function COOKIE($key = null, $default = null)
    {
        return static::G()->_COOKIE($key, $default);
    }
    public static function SERVER($key = null, $default = null)
    {
        return static::G()->_SERVER($key, $default);
    }
    public static function SESSION($key = null, $default = null)
    {
        return static::G()->_SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return static::G()->_FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return static::G()->_SessionSet($key, $value);
    }
    public static function SessionUnset($key)
    {
        return static::G()->_SessionUnset($key);
    }
    public static function SessionGet($key, $default = null)
    {
        return static::G()->_SessionGet($key, $default);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return static::G()->_CookieSet($key, $value, $expire);
    }
    public static function CookieGet($key, $default = null)
    {
        return static::G()->_CookieGet($key, $default);
    }

    private function getSuperGlobalData($superglobal_key, $key, $default)
    {
        $data = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->$superglobal_key : ($GLOBALS[$superglobal_key] ?? []);
        
        if (isset($key)) {
            return $data[$key] ?? $default;
        } else {
            return $data ?? $default;
        }
    }
    public function _GET($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_GET', $key, $default);
    }
    public function _POST($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_POST', $key, $default);
    }
    public function _REQUEST($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_REQUEST', $key, $default);
    }
    public function _COOKIE($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_COOKIE', $key, $default);
    }
    public function _SERVER($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_SERVER', $key, $default);
    }
    public function _SESSION($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_SESSION', $key, $default);
    }
    public function _FILES($key = null, $default = null)
    {
        return $this->getSuperGlobalData('_FILES', $key, $default);
    }
    public function _SessionSet($key, $value)
    {
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            (__SUPERGLOBAL_CONTEXT)()->_SESSION[$key] = $value;
        } else {
            $_SESSION[$key] = $value;
        }
    }
    public function _SessionUnset($key)
    {
        if (defined('__SUPERGLOBAL_CONTEXT')) {
            unset((__SUPERGLOBAL_CONTEXT)()->_SESSION[$key]);
        }
        unset($_SESSION[$key]);
    }
    public function _CookieSet($key, $value, $expire)
    {
        $this->_setcookie($key, $value, $expire ? $expire + time():0);
    }
    public function _SessionGet($key, $default)
    {
        return $this->getSuperGlobalData('_SESSION', $key, $default);
    }

    public function _CookieGet($key, $default)
    {
        return $this->getSuperGlobalData('_COOKIE', $key, $default);
    }
}
