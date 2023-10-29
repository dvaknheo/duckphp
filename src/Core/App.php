<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\Logger;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;

/**
 * MAIN FILE
 * dvaknheo@github.com
 * OK，Lazy
 *
 */
class App extends ComponentBase
{
    use KernelTrait;
    const VERSION = '1.2.13-dev';

    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    
    
    //inner trait
    //use Core_Helper;
    //use Core_Glue;
    //use Core_NotImplemented;
    
    protected $core_options = [
        'path_runtime' => 'runtime',
        'alias' => null,
        
        'default_exception_do_log' => true,
        'default_exception_self_display' => true,
        'close_resource_at_output' => false,
        
        //// error handler ////
        'error_404' => null,          //'_sys/error-404',
        'error_500' => null,          //'_sys/error-500',
        'error_debug' => null,        //'_sys/error-debug',
    ];
    protected $common_options = [];

    // for trait
    protected $beforeShowHandlers = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->options = array_replace_recursive($this->kernel_options, $this->core_options, $this->common_options, $this->options);
        unset($this->kernel_options); // not use again;
        unset($this->core_options); // not use again;
        unset($this->common_options); // not use again;
    }
    public function version()
    {
        return static::VERSION;
    }
    protected function doInitComponents()
    {
        Logger::G()->init($this->options, $this);
        View::G()->init($this->options, $this);
        
        if ($this->is_root && $this->_Phase()) {
            $this->getContainer()->addPublicClasses([ Logger::class,]);
        }
    }
    //////// override KernelTrait ////////
    //@override
    public function _On404(): void
    {
        $error_view = $this->options['error_404'] ?? null;
        $error_view = $this->is_inited?$error_view:null;
        
        static::header('404 Not Found', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        //// no error_404 setting.
        if (!$error_view) {
            $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
            $path_info = $_SERVER['PATH_INFO'] ?? '';
            echo "404 File Not Found<!--PATH_INFO: ($path_info) DuckPhp set options ['error_404'] to override me. -->\n";
            if ($this->options['is_debug']) {
                echo "<!-- Route Error Info: ".Route::G()->getRouteError()."-->\n";
            }
            return;
        }
        
        //TODO   recreateobject
        View::G(new View())->init($this->options, $this);
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
            $ex->display($ex); // 这里要改
            return;
        }
        $error_view = $this->options['error_500'] ?? null;
        $error_view = $this->is_inited?$error_view:null;
        
        static::header('Server Error', true, 500);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            return;
        }
        
        $data = [];
        $data['is_debug'] = $this->_IsDebug();
        $data['ex'] = $ex;
        $data['class'] = get_class($ex);
        $data['message'] = $ex->getMessage();
        $data['code'] = $ex->getCode();
        $data['trace'] = $ex->getTraceAsString();
        $data['file'] = $ex->getFile();
        $data['line'] = $ex->getLine();
        
        ////////default;
        if (!$error_view) {
            echo "Internal Error \n<!--DuckPhp set options['error_500'] to override me  -->\n";
            if (!$this->is_inited) {
                echo "<div>error trigger before init, options['error_500'] ignore. </div>";
            }
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
        
        View::G(new View())->init($this->options, $this);
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
        $error_view = $this->is_inited?$error_view:null;
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        $error_desc = '';
        $ext = ($this->is_inited)? '':"<div>error trigger before init, options['error_debug'] ignore.";
        if (!$error_view) {
            extract($data);
            echo  <<<EOT
<!--DuckPhp  set options ['error_debug']='_sys/error-debug.php' to override me -->
<fieldset class="_DuckPhp_DEBUG">
    <legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
{$ext}
</pre>
</fieldset>

EOT;
            return;
        }
        View::G()->_Display($error_view, $data);
    }
    public function getProjectPath()
    {
        return static::Root()->options['path'];
    }
    public function getRuntimePath()
    {
        $path = static::SlashDir(static::Root()->options['path']);
        $path_runtime = static::SlashDir(static::Root()->options['path_runtime']);
        return static::IsAbsPath($path_runtime) ? $path_runtime : $path.$path_runtime;
    }
    
    public function getOverrideableFile($path_sub, $file)
    {
        if (static::IsAbsPath($file)) {
            return $file;
        }
        if (static::IsAbsPath($path_sub)) {
            return static::SlashDir($path_sub) . $file;
        }
        if (!$this->is_root) {
            $path_main = static::Root()->options['path'];
            $name = $this->options['alias'] ?? str_replace("\\", '/', $this->options['namespace']);
            
            $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub). static::SlashDir($name) . $file;
            if (!file_exists($full_file)) {
                $path_main = $this->options['path'];
                $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub).$file;
            }
        } else {
            $path_main = $this->options['path'];
            $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub) . $file;
        }
        
        return $full_file;
    }
    //////// features

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
    public function skip404Handler()
    {
        $this->options['skip_404_handler'] = true;
    }
    public function onBeforeOutput()
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
        return View::G()->_Show($data, $view);
    }
    public function adjustViewFile($view)
    {
        return $view === '' ? Route::G()->getRouteCallingPath() : $view;
    }
    //}
    //trait Core_Helper
    //{
    ////Exit;
    public static function ExitJson($ret, $exit = true)
    {
        return static::_()->_ExitJson($ret, $exit);
    }
    public static function ExitRedirect($url, $exit = true)
    {
        return static::_()->_ExitRedirect($url, $exit);
    }
    public static function ExitRedirectOutside($url, $exit = true)
    {
        return static::_()->_ExitRedirectOutside($url, $exit);
    }
    public static function ExitRouteTo($url, $exit = true)
    {
        return static::_()->_ExitRedirect(static::Url($url), $exit);
    }
    public static function Exit404($exit = true)
    {
        static::On404();
        if ($exit) {
            static::exit();
        }
    }
    
    public function _ExitJson($ret, $exit = true)
    {
        SystemWrapper::G()->_header('Content-Type:application/json; charset=utf-8');
        echo Runtime::G()->_Json($ret);
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
    ///////
    public static function Platform()
    {
        return static::_()->_Platform();
    }
    public function _Platform()
    {
        return $this->options['platform'];
    }
    public static function IsDebug()
    {
        return static::_()->_IsDebug();
    }
    public function _IsDebug()
    {
        return static::_()->options['is_debug'];
    }
    public static function IsRealDebug()
    {
        return static::_()->_IsRealDebug();
    }
    public function _IsRealDebug()
    {
        //you can override this;
        return $this->options['is_debug'];
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
    public static function PhaseCall($phase, $callback, ...$args)
    {
        return static::G()->_PhaseCall($phase, $callback, ...$args);
    }
    public function _PhaseCall($phase, $callback, ...$args)
    {
        $phase = is_object($phase) ? get_class($phase) : $phase;
        $current = $this->_Phase();
        if (!$phase || !$current) {
            return ($callback)(...$args);
        }
        
        $this->_Phase($phase);
        $ret = ($callback)(...$args);
        $this->_Phase($current);
        return $ret;
    }
    
    ///////////////
    
    public static function Pager($object = null)
    {
        return static::_()->_Pager($object);
    }
    public static function Cache($object = null)
    {
        return static::_()->_Cache($object);
    }
    public function _Cache($object = null)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public function _Pager($object = null)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
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
    
    public static function IsAjax()
    {
        return Runtime::_()->_IsAjax();
    }
    public static function var_dump(...$args)
    {
        return Runtime::_()->_var_dump(...$args);
    }
    public static function Logger($object = null)
    {
        return Logger::G($object);
    }
    public static function DebugLog($message, array $context = array())
    {
        return Runtime::_()->_DebugLog($message, $context);
    }
    // system static
    
    public static function H($str)
    {
        return Runtime::_()->_H($str);
    }
    public static function L($str, $args = [])
    {
        return Runtime::_()->_L($str, $args);
    }
    public static function Hl($str, $args = [])
    {
        return Runtime::_()->_Hl($str, $args);
    }
    public static function Json($data)
    {
        return Runtime::_()->_Json($data);
    }
    public static function TraceDump()
    {
        return Runtime::_()->_TraceDump();
    }
    public static function VarLog($var)
    {
        return Runtime::_()->_VarLog($var);
    }
    //}
    //trait Core_NotImplemented
    //{
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
    //}
    //trait Core_Glue
    //{
    //// source is static ////
    //runtime state
    public static function isInException()
    {
        return Runtime::G()->isInException();
    }
    public static function isRunning()
    {
        return Runtime::G()->isRunning();
    }
    // route static
    public static function Url($url = null)
    {
        return Route::G()->_Url($url);
    }
    public static function Res($url = null)
    {
        return Route::G()->_Res($url);
    }
    public static function Domain($use_scheme = false)
    {
        return Route::G()->_Domain($use_scheme);
    }
    public static function Parameter($key = null, $default = null)
    {
        return Route::G()->_Parameter($key, $default);
    }
    public static function PathInfo()
    {
        return Route::G()->_PathInfo();
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
        return static::G()->_Setting($key);
    }
    public static function Config($file_basename, $key = null, $default = null)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    
    //// the next is dynamic ////
    // route
    public static function Route($replacement_object = null)
    {
        return Route::G($replacement_object);
    }
    public static function replaceController($old_class, $new_class)
    {
        return Route::G()->replaceController($old_class, $new_class);
    }
    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
    {
        return Route::G()->addRouteHook($callback, $position, $once);
    }
    public static function getRouteCallingClass()
    {
        return Route::G()->getRouteCallingClass();
    }
    public static function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
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
    public static function GET($key = null, $default = null)
    {
        return SuperGlobal::_()->_Get($key, $default);
    }
    public static function POST($key = null, $default = null)
    {
        return SuperGlobal::_()->_POST($key, $default);
    }
    public static function REQUEST($key = null, $default = null)
    {
        return SuperGlobal::_()->_REQUEST($key, $default);
    }
    public static function COOKIE($key = null, $default = null)
    {
        return SuperGlobal::_()->_COOKIE($key, $default);
    }
    public static function SERVER($key = null, $default = null)
    {
        return SuperGlobal::_()->_SERVER($key, $default);
    }
    public static function SESSION($key = null, $default = null)
    {
        return SuperGlobal::_()->_SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return SuperGlobal::_()->_FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return SuperGlobal::_()->_SessionSet($key, $value);
    }
    public static function SessionUnset($key)
    {
        return SuperGlobal::_()->_SessionUnset($key);
    }
    public static function SessionGet($key, $default = null)
    {
        return SuperGlobal::_()->_SessionGet($key, $default);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return SuperGlobal::_()->_CookieSet($key, $value, $expire);
    }
    public static function CookieGet($key, $default = null)
    {
        return SuperGlobal::_()->_CookieGet($key, $default);
    }
    ////////////////////////////
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return SystemWrapper::_()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return SystemWrapper::_()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return SystemWrapper::_()->_exit($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return SystemWrapper::_()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return SystemWrapper::_()->_register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options = [])
    {
        return SystemWrapper::_()->_session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return SystemWrapper::_()->_session_id($session_id);
    }
    public static function session_destroy()
    {
        return SystemWrapper::_()->_session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SystemWrapper::_()->_session_set_save_handler($handler);
    }
    public static function mime_content_type($file)
    {
        return SystemWrapper::_()->_mime_content_type($file);
    }
    public static function system_wrapper_replace(array $funcs)
    {
        return SystemWrapper::_()->_system_wrapper_replace($funcs);
    }
    public static function system_wrapper_get_providers():array
    {
        return SystemWrapper::_()->_system_wrapper_get_providers();
    }
}
