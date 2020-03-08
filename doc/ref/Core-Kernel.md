# Core\App

## 简介
最核心的类，其他
## 选项

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
    'skip_plugin_mode_check' => false,
    'skip_exception_check' => false,
    'skip_fix_path_info' => false,
    
    //// error handler ////
    'handle_all_dev_error' => true,
    'handle_all_exception' => true,
    'error_404' => null,          //'_sys/error-404',
    'error_500' => null,          //'_sys/error-500',
    'error_debug' => null,        //'_sys/error-debug',
    
    
    'skip_app_autoload' => false,
    


    
## 公开方法
public static function RunQuickly(array $options=[], callable $after_init=null): bool

    //
public function init(array $options=[], object $context=null)

    //
public function run(): bool

    //
public function clear(): void

    //
public function cleanAll()

    //
public function addBeforeShowHandler($handler)

    //
public function extendComponents($class, $methods, $components): void

    //
public function getStaticComponentClasses()

    //
public function getDynamicComponentClasses()

    //
public function addDynamicComponentClass($class)

    //
public function deleteDynamicComponentClass($class)

    //
public static function On404(): void

    //
public static function OnException($ex): void

    //
public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void

    //
public static function header($output, bool $replace = true, int $http_response_code=0)

    //
public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = ### ', string $domain  = '', bool $secure = false, bool $httponly = false)

    //
public static function exit_system($code=0)

    //
public static function set_exception_handler(callable $exception_handler)

    //
public static function register_shutdown_function(callable $callback, ...$args)

    //
public static function ExitJson($ret, $exit=true)

    //
public static function ExitRedirect($url, $exit=true)

    //
public static function ExitRedirectOutside($url, $exit=true)

    //
public static function ExitRouteTo($url, $exit=true)

    //
public static function Exit404($exit=true)

    //
public static function Platform()

    //
public static function IsDebug()

    //
public static function IsRealDebug()

    //
public static function IsInException()

    //
public static function Show($data=[], $view=null)

    //
public static function H($str)

    //
public static function L($str, $args=[])

    //
public static function HL($str, $args=[])

    //
public static function DumpTrace()

    //
public static function var_dump(...$args)

    //
public static function IsRunning()

    //
public static function URL($url=null)

    //
public static function Parameters()

    //
public static function ShowBlock($view, $data=null)

    //
public static function Setting($key)

    //
public static function Config($key, $file_basename='config')

    //
public static function LoadConfig($file_basename)

    //
public function assignPathNamespace($path, $namespace=null)

    //
public function addRouteHook($hook, $append=true, $outter=true, $once=true)

    //
public static function getRouteCallingMethod()

    //
public static function setRouteCallingMethod(string $method)

    //
public static function setViewWrapper($head_file=null, $foot_file=null)

    //
public static function assignViewData($key, $value=null)

    //
public static function assignExceptionHandler($classes, $callback=null)

    //
public static function setMultiExceptionHandler(array $classes, callable $callback)

    //
public static function setDefaultExceptionHandler(callable $callback)

    //
public static function SG(object $replacement_object=null)

    //
public static function &GLOBALS($k, $v=null)

    //
public static function &STATICS($k, $v=null, $_level=1)

    //
public static function &CLASS_STATICS($class_name, $var_name)

    //
public static function session_start(array $options=[])

    //
public static function session_id($session_id=null)

    //
public static function session_destroy()

    //
public static function session_set_save_handler(\SessionHandlerInterface $handler)

## 详解
Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类

### 作为 500,404 处理的 trait

### 覆盖系统的 core_systemwrapper

### 助手类
Core_Glue

Core_Component

