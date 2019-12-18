<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonEx;
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

class App
{
    const VERSION = '1.2.1';
    
    use SingletonEx;
    use ThrowOn;
    use ExtendableStaticCallTrait;
    use SystemWrapper;
    
    use Core_Component;
    use Core_Handler;
    use Core_Helper;
    use Core_Redirect;
    use Core_SystemWrapper;
    use Core_Glue;
    
    public $options = [
            //// basic config ////
            'path' => null,
            'namespace' => 'MY',
            'path_namespace' => 'app',
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'override_class' => 'Base\App',
            'reload_for_flags' => true,
            'use_super_global' => false,
            'skip_view_notice_error' => true,
            'skip_404_handler' => false,
            'skip_plugin_check' => false,
            
            //// error handler ////
            'handle_all_dev_error' => true,
            'handle_all_exception' => true,
            'error_404' => null,          //'_sys/error-404',
            'error_500' => null,          //'_sys/error-500',
            'error_exception' => null,    //'_sys/error-exception',
            'error_debug' => null,        //'_sys/error-debug',
            
            //// Class Autoloader ////
            // 'path'=>null,
            // 'namespace'=>'MY',
            // 'path_namespace'=>'app',
            // 'skip_system_autoload'=>true,
            'skip_app_autoload' => false,
            //'enable_cache_classes_in_cli'=>true,

            //// Class Configer ////
            // 'path'=>null,
            // 'path_config'=>'config',
            // 'all_config'=>[],
            // 'setting'=>[],
            // 'setting_file'=>'setting',
            // 'skip_setting_file'=>false,
            
            //// Class View Class ////
            // 'path'=>null,
            // 'path_view'=>'view',
            

            //// Class Route ////
            // 'path'=>null,
            // 'namespace'=>'MY',
            // 'namespace_controller'=>'Controller',
            // 'controller_base_class'=>null,
            // 'controller_welcome_class'=>'Main',
            // 'controller_hide_boot_class'=>false,
            // 'controller_methtod_for_miss'=>null,
            // 'controller_prefix_post'=>'do_',
            // 'controller_postfix'=>'',
        ];

    public $is_debug = true;
    public $platform = '';
    public $override_from = ''; // for inner usage;
    
    protected $beforeRunHandlers = [];
    protected $error_view_inited = false;
    
    protected $extDynamicComponentClasses = [];

    //const;
    protected $componentClassMap = [
            'M' => 'Helper\ModelHelper',
            'V' => 'Helper\ViewHelper',
            'C' => 'Helper\ControllerHelper',
            'S' => 'Helper\ServiceHelper',
    ];
    //system handler replacer
    protected $system_handlers = [
        'header' => null,
        'setcookie' => null,
        'exit_system' => null,
        'set_exception_handler' => null,
        'register_shutdown_function' => null,
    ];
    public function __construct()
    {
    }
    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    {
        $instance = static::G()->init($options);
        if (!$instance) {
            return true;
        }
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    protected function initOptions($options = [])
    {
        if (!isset($options['path']) || !$options['path']) {
            $path = realpath($_SERVER['SCRIPT_FILENAME'].'/../');
            $options['path'] = (string)$path;
        }
        $options['path'] = rtrim($options['path'], '/').'/';
        $this->options = array_replace_recursive($this->options, $options);
        
        $this->is_debug = $this->options['is_debug'];
        $this->platform = $this->options['platform'];
    }
    protected function checkOverride($options)
    {
        // TODO static::DEFAULT_OPTIONSEX
        $override_class = $options['override_class'] ?? $this->options['override_class'];
        $namespace = $options['namespace'] ?? $this->options['namespace'];
        
        if (substr($override_class, 0, 1) !== '\\') {
            $override_class = $namespace.'\\'.$override_class;
        }
        $override_class = ltrim($override_class, '\\');
        if (!$override_class || !class_exists($override_class)) {
            return null;
        }
        if (static::class === $override_class) {
            return null;
        }
        return $override_class::G();
    }
    //init
    public function init(array $options, object $context = null)
    {
        if (!($options['skip_plugin_check'] ?? false) && isset($context)) {
            return $this->initAsPlugin($options, $context);
        }
        AutoLoader::G()->init($options, $this)->run();
        
        $handle_all_dev_error = $options['handle_all_dev_error'] ?? $this->options['handle_all_dev_error'];
        $handle_all_exception = $options['handle_all_exception'] ?? $this->options['handle_all_exception'];

        $exception_options = [
            'handle_all_dev_error' => $handle_all_dev_error,
            'handle_all_exception' => $handle_all_exception,
            
            'system_exception_handler' => [static::class,'set_exception_handler'],
            
            'default_exception_handler' => [static::class,'OnException'],
            'dev_error_handler' => [static::class,'OnDevErrorHandler'],
        ];
        ExceptionManager::G()->init($exception_options, $this)->run();
        
        $object = $this->checkOverride($options);
        
        $override_from = $object?static::class:'';
        $object = $object ?? $this;
        
        (self::class)::G($object);
        static::G($object);
        
        $object->override_from = $override_from;
        $object->initOptions($options);
        return $object->onInit();
    }
    //for override
    protected function initAsPlugin(array $options, object $context = null)
    {
        static::ThrowOn(true, 'DuckPhp, only for override');
        return $this; // @codeCoverageIgnore
    }
    //for override
    protected function onInit()
    {
        Configer::G()->init($this->options, $this);
        $this->reloadFlags();
        
        View::G()->init($this->options, $this);
        $this->error_view_inited = true;
        
        Route::G()->init($this->options, $this);
        
        $this->initExtentions($this->options['ext']);
        
        return $this;
    }
    protected function reloadFlags(): void
    {
        if (!$this->options['reload_for_flags']) {
            return;
        }
        $is_debug = Configer::G()->_Setting('duckphp_is_debug');
        $platform = Configer::G()->_Setting('duckphp_platform');
        if (isset($is_debug)) {
            $this->is_debug = $is_debug;
        }
        if (isset($platform)) {
            $this->platform = $platform;
        }
    }
    protected function initExtentions(array $exts): void
    {
        foreach ($exts as $class => $options) {
            $options = ($options === true)?$this->options:$options;
            $options = is_string($options)?$this->options[$options]:$options;
            if ($options === false) {
                continue;
            }
            $class = (string)$class;
            if (!class_exists($class)) {
                continue;
            }
            $class::G()->init($options, $this);
        }
        return;
    }
    
    //for override
    protected function onRun()
    {
        return;
    }
    public function run(): bool
    {
        try {
            foreach ($this->beforeRunHandlers as $v) {
                ($v)();
            }
            
            $this->onRun();
            
            RuntimeState::ReCreateInstance()->begin();
            
            $route = Route::G();
            if ($this->options['use_super_global'] ?? false) {
                $route->bindServerData(SuperGlobal::G()->_SERVER);
            }
            
            $ret = $route->run();
            
            if (!$ret && !$this->options['skip_404_handler']) {
                static::On404();
            }
            $this->clear();
            
            return $ret;
        } catch (\Throwable $ex) {
            RuntimeState::G()->is_in_exception = true;
            ExceptionManager::G()->on_exception($ex);
            return true;
        }
    }
    // 这里我们要做好些清理判断。对资源的释放处理
    public function clear(): void
    {
        if (! RuntimeState::G()->is_before_show_done) {
            foreach ($this->beforeShowHandlers as $v) {
                ($v)();
            }
            RuntimeState::G()->is_before_show_done = true;
        }
        RuntimeState::G()->end();
        
        //
    }
    public function cleanAll()
    {
        $this->clear();
        $classes = $this->getDynamicComponentClasses();
        foreach ($classes as $class) {
            $this->cleanClass($class);
        }
        $classes = $this->getStaticComponentClasses();
        foreach ($classes as $class) {
            $this->cleanClass($class);
        }
    }
    protected function cleanClass($input_class)
    {
        $current_class = get_class($input_class::G());
        $input_class::G(new $input_class());
        if ($current_class != $input_class) {
            $this->cleanClass($current_class); // @codeCoverageIgnore
        }
    }
    //main produce end
    
    ////////////////////////
    
    protected function addBeforeRunHandler(?callable $handler): void
    {
        $this->beforeRunHandlers[] = $handler;
    }
    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[] = $handler;
    }
    ////////////////////////
    // @provider output.
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
            $new_class::AssignExtendStaticMethod($old_class::GetExtendStaticStaticMethodList());
        }
    }
}
trait Core_Component
{
    public function getStaticComponentClasses()
    {
        $ret = [
            self::class,
            AutoLoader::class,
            ExceptionManager::class,
            Configer::class,
            Route::class,
        ];
        if (!in_array(static::class, $ret)) {
            $ret[] = static::class;
        }
        if ($this->override_from && !in_array($this->override_from, $ret)) {
            $ret[] = $this->override_from;
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
        return $ret;
    }
    public function addDynamicComponentClass($class)
    {
        $this->extDynamicComponentClasses[] = $class;
    }
    public function deleteDynamicComponentClass($class)
    {
        array_filter($this->extDynamicComponentClasses, function ($v) use ($class) {
            return $v !== $class?true:false;
        });
    }
}
trait Core_Handler
{
    protected $beforeShowHandlers = [];
    protected $error_view_inited = false;
    
    public static function On404(): void
    {
        static::G()->_On404();
    }
    public static function OnException($ex): void
    {
        static::G()->_OnException($ex);
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
        
        $this->setViewWrapper(null, null);
        $this->_Show([], $error_view);
    }
    
    public function _OnException($ex): void
    {
        $is_error = is_a($ex, 'Error') || is_a($ex, 'ErrorException')?true:false;
        $error_view = $is_error?($this->options['error_500'] ?? null):($this->options['error_exception'] ?? null);
        $error_view = $this->error_view_inited?$error_view:null;
        
        static::header('', true, 500);
        $data = [];
        $data['is_debug'] = $this->is_debug;
        $data['ex'] = $ex;
        $data['class'] = get_class($ex);
        $data['message'] = $ex->getMessage();
        $data['code'] = $ex->getCode();
        $data['trace'] = $ex->getTraceAsString();
        
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            $this->clear();
            return;
        }
        ////////  no  error_500 or error_exception setting
        if (!$error_view) {
            $desc = $is_error?'Internal Error':'Internal Exception';
            $error_config = $is_error?'error_500':'error_exception';
            echo "$desc \n<!--DuckPhp set options ['{$error_config}'] to override me  -->\n";
            
            if ($data['is_debug']) {
                echo "<h3>{$data['class']}({$data['code']}):{$data['message']}</h3>";
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
        View::G()->_ShowBlock($error_view, $data);
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
    public static function exit_system($code = 0)
    {
        return static::G()->_exit_system($code);
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

    public function _exit_system($code = 0)
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
trait Core_Redirect
{
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
            static::exit_system();
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
            static::exit_system();
        }
    }
    public function _ExitRedirect($url, $exit = true)
    {
        if (parse_url($url, PHP_URL_HOST)) {
            static::exit_system();
            return;
        }
        static::header('location: '.$url, true, 302);
        if ($exit) {
            static::exit_system();
        }
    }
    public function _ExitRedirectOutside($url, $exit = true)
    {
        static::header('location: '.$url, true, 302);
        if ($exit) {
            static::exit_system();
        }
    }
}

trait Core_Helper
{

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
    
    public static function IsInException()
    {
        return RuntimeState::G()->is_in_exception;
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
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
        RuntimeState::G()->is_before_show_done = true;
        if ($this->options['skip_view_notice_error'] ?? false) {
            RuntimeState::G()->skipNoticeError();
        }
        
        View::G()->assignViewData([
            '__is_debug' => $this->is_debug,
            '__duckphp_is_debug' => $this->is_debug,
            '__duckphp_platform' => $this->platform,
        ]);
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
    public static function DumpTrace()
    {
        return static::G()->_DumpTrace();
    }
    public static function var_dump(...$args)
    {
        return static::G()->_var_dump(...$args);
    }
    public function _DumpTrace()
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
    public static function Parameters()
    {
        return Route::G()->_Parameters();
    }
    // view static

    public static function ShowBlock($view, $data = null)
    {
        return View::G()->_ShowBlock($view, $data);
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
    //super global
    public static function SG(object $replacement_object = null)
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
