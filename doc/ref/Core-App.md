# Core\App

## 简介
最核心的类，其他
## 依赖关系
+ `Core\App` 
    + Trait [Core\Kernel](Core-Kernel.md)
    + Trait [Core\ThrowOn](Core-ThrowOn.md)
    + Trait [Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    + Trait [Core\SystemWrapper](Core-SystemWrapper.md)

## 选项
使用 [Core\Kernel](Core-Kernel.md) 的默认选项。

## 

public function extendComponents($class, $methods, $components): void

    //

    //
public static function On404(): void

    //
public static function OnException($ex): void

    //
public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void

## 详解
Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类

### 作为 500,404 处理的 trait

### 覆盖系统的 core_systemwrapper

### 助手类
相关代码请参考 
 + AppHelper

 
 ## 方法索引
 

    public function __construct()
    public function extendComponents($method_map, $components = []): void
    public function cloneHelpers($new_namespace, $componentClassMap = [])
    public static function On404(): void
    public static function OnDefaultException($ex): void
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    public function _On404(): void
    public function _OnDefaultException($ex): void
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public static function exit($code = 0)
    public static function set_exception_handler(callable $exception_handler)
    public static function register_shutdown_function(callable $callback, ...$args)
    public function _header($output, bool $replace = true, int $http_response_code = 0)
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public function _exit($code = 0)
    public function _set_exception_handler(callable $exception_handler)
    public function _register_shutdown_function(callable $callback, ...$args)
    public static function ExitJson($ret, $exit = true)
    public static function ExitRedirect($url, $exit = true)
    public static function ExitRedirectOutside($url, $exit = true)
    public static function ExitRouteTo($url, $exit = true)
    public static function Exit404($exit = true)
    public function _ExitJson($ret, $exit = true)
    public function _ExitRedirect($url, $exit = true)
    public function _ExitRedirectOutside($url, $exit = true)
    public static function Platform()
    public static function IsDebug()
    public static function IsRealDebug()
    public function _IsRealDebug()
    public static function InException()
    public static function Show($data = [], $view = null)
    public static function H($str)
    public static function L($str, $args = [])
    public static function HL($str, $args = [])
    public function _L($str, $args = [])
    public function _Show($data = [], $view = null)
    public function _H(&$str)
    public static function trace_dump()
    public static function var_dump(...$args)
    public function _trace_dump()
    public function _var_dump(...$args)
    public static function Domain()
    public function _Domain()
    public static function Logger($object = null)
    public static function Pager($object = null)
    public function _Pager($object = null)
    public static function PageNo()
    public static function PageSize($new_value = null)
    public static function PageHtml($total, $options = [])
    public static function IsRunning()
    public static function URL($url = null)
    public static function ShowBlock($view, $data = null)
    public static function Setting($key)
    public static function Config($key, $file_basename = 'config')
    public static function LoadConfig($file_basename)
    public static function assignPathNamespace($path, $namespace = null)
    public static function getPathInfo()
    public static function getParameters()
    public static function addRouteHook($hook, $position, $once = true)
    public static function getRouteCallingMethod()
    public static function setRouteCallingMethod(string $method)
    public static function setURLHandler($callback)
    public static function setViewWrapper($head_file = null, $foot_file = null)
    public static function assignViewData($key, $value = null)
    public static function assignExceptionHandler($classes, $callback = null)
    public static function setMultiExceptionHandler(array $classes, callable $callback)
    public static function setDefaultExceptionHandler(callable $callback)
    public static function OnException($ex)
    public static function SG($replacement_object = null)
    public static function &GLOBALS($k, $v = null)
    public static function &STATICS($k, $v = null, $_level = 1)
    public static function &CLASS_STATICS($class_name, $var_name)
    public static function session_start(array $options = [])
    public static function session_id($session_id = null)
    public static function session_destroy()
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    
    public function getStaticComponentClasses()
    public function getDynamicComponentClasses()
    public function addDynamicComponentClass($class)
    public function removeDynamicComponentClass($class)