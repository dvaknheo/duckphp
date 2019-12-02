# 参考手册
这个文档是 DNMVCS 系统所有类的参考。

本文档作为参考将覆盖所有类

整体架构图


## Core 内核

### 依赖关系

### Core/App
DNMVCS\Core\App 是主要的类。聚合了其他类
#### 选项

#### 方法
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

    //
#### 说明
### Core/AutoLoader
说明
#### 选项
#### 方法
public function init($options=[], $context=null)

    //
public function run()

    //
public function _autoload($class)

    //
public function assignPathNamespace($input_path, $namespace=null)

    //
public function cacheClasses()

    //
public function cacheNamespacePath($path)

    //
public function clear()

    //
### Core/Configer
    public function init($options=[], $context=null)
    public function _Setting($key)
    public function _Config($key, $file_basename='config')
    public function _LoadConfig($file_basename='config')
    public function prependConfig($name, $data)
### Core/ExceptionManager
    public function __construct()
    public function setDefaultExceptionHandler($default_exception_handler)
    public function assignExceptionHandler($class, $callback=null)
    public function setMultiExceptionHandler(array $classes, $callback)
    public function on_error_handler($errno, $errstr, $errfile, $errline)
    public function on_exception($ex)
    public function init($options=[], $context=null)
    public function run()
    public function clear()

### ExtendableStaticCallTrait
    public static function AssignExtendStaticMethod($key, $value=null)
    public static function GetExtendStaticStaticMethodList()
    public static function __callStatic($name, $arguments)
### HookChain
#### 说明
这是个特殊类。并没有引用
#### 方法
    public function __invoke()
    public static function Hook(&$var, $callable, $append = true, $once = true)
    public function add($callable, $append, $once)
    public function remove($callable)
    public function has($callable)
    public function all()
    public function offsetSet($offset, $value)
    public function offsetExists($offset)
    public function offsetUnset($offset)
    public function offsetGet($offset)
### HttpServcer
    public function __construct()
    public static function RunQuickly($options)
    public function init($options=[], $context=null)
    public function run()
    public function getPid()
    public function close()
### Route
    public function __construct()
    public static function RunQuickly(array $options=[], callable $after_init=null)
    public static function URL($url=null)
    public static function Parameters()
    public function _URL($url=null)
    public function _Parameters()
    public function defaultURLHandler($url=null)
    public function init($options=[], $context=null)
    public function setURLHandler($callback)
    public function getURLHandler()
    public function bindServerData($server)
    public function bind($path_info, $request_method='GET')
    public function run()
    public function forceFail()
    public function addRouteHook($callback, $position, $once=true)
    public function add404Handler($callback)
    public function defaulToggleRouteCallback($enable=true)
    public function defaultRunRouteCallback($path_info=null)
    public function defaultGetRouteCallback($path_info)
    public function setPathInfo($path_info)
    public function getRouteError()
    public function getRouteCallingPath()
    public function getRouteCallingClass()
    public function getRouteCallingMethod()
    public function setRouteCallingMethod($calling_method)
### RuntimeState
    public function __construct()
    public function isRunning()
    public static function ReCreateInstance()
    public function begin()
    public function end()
    public function skipNoticeError()
### SingletonEx
    public static function G($object=null)
### SuperGlobal
    public function __construct()
    public function init()
    public function session_start(array $options=[])
    public function session_id($session_id)
    public function session_destroy()
    public function session_set_save_handler($handler)
    public function &_GLOBALS($k, $v=null)
    public function &_STATICS($name, $value=null, $parent=0)
    public function &_CLASS_STATICS($class_name, $var_name)

### SystemWrapper
    public static function system_wrapper_replace(array $funcs)
    public static function system_wrapper_get_providers():array

### ThrowOn

    public static function ThrowOn($flag, $message, $code=0, $exception_class=null)
### View
    public function __construct()
    public function init($options=[], $context=null)
    public function _Show($data=[], $view)
    public function _ShowBlock($view, $data=null)
    public function setViewWrapper($head_file, $foot_file)
    public function assignViewData($key, $value=null)
    public function setOverridePath($path)
### Helper/ControllerHelper.php
    public static function Setting($key)
    public static function Config($key, $file_basename='config')
    public static function LoadConfig($file_basename)
    public static function H($str)
    public static function URL($url=null)
    public static function Parameters()
    public function getRouteCallingMethod()
    public function setRouteCallingMethod($method)
    public static function Show($data=[], $view=null)
    public static function ShowBlock($view, $data=null)
    public function setViewWrapper($head_file=null, $foot_file=null)
    public function assignViewData($key, $value=null)
    public static function ExitRedirect($url, $exit=true)
    public static function ExitRedirectOutside($url, $exit=true)
    public static function ExitRouteTo($url, $exit)
    public static function Exit404($exit=true)
    public static function ExitJson($ret, $exit=true)
    public static function header($output, bool $replace = true, int $http_response_code=0)
    public static function exit_system($code=0)
    public function assignExceptionHandler($classes, $callback=null)
    public function setMultiExceptionHandler(array $classes, $callback)
    public function setDefaultExceptionHandler($callback)
    public static function SG()
    public static function &GLOBALS($k, $v=null)
    public static function &STATICS($k, $v=null)
    public static function &CLASS_STATICS($class_name, $var_name)
    public static function session_start(array $options=[])
    public function session_id($session_id=null)
    public static function session_destroy()
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
### Helper/HelperTrait.php
    public static function IsDebug()
    public static function Platform()
    public static function DumpTrace()
    public static function var_dump(...$args)
### Helper/ModelHelper.php
### Helper/ServiceHelper.php
    public static function Setting($key)
    public static function Config($key, $file_basename='config')
    public static function LoadConfig($file_basename)
### Helper/ViewHelper.php
    public static function H($str)
    public static function L($str, $args=[])
    public static function HL($str, $args=[])
    public static function ShowBlock($view, $data=null)

## 主类
### DNMVCS
### HttpServer
### SigletonEx
## 数据库
### DB
### DBAdvance
### DBInterface
## 基类
### StrictModel
### StrictServiceTrait
### AppPluginTrait

## Helper 助手类
### ControllerHelper
### ModelHelper
### ServiceHelper
### ViewHelper
## 扩展
### CallableView
### DBManager
### DBReusePoolProxy
### FacadesAutoLoader
### FacadesBase
## JsonRpcClientBase
### Json RpcExt
### Misc
### Pager
### RedisManager
### RedisSimpleCache
### RouteHookDirectoryMode
### RouteHookRouteMap
### SimpleLogger
### StrictCheck
