# DuckPhp\Core\App
[toc]

## 简介
Core 目录下的微框架入口
## 依赖关系
* 组件基类 [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
* 可扩展静态Trait [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
* 系统同名函数替代Trait [DuckPhp\Core\SystemWrapperTrait](Core-SystemWrapperTrait.md)
* 核心Trait [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
* 日志类 [DuckPhp\Core\Logger](Core-Logger.md)
* 自动加载类 [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
* 配置类 [DuckPhp\Core\Configer](Core-Configer.md)
* 异常管理类 [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
* 路由类 [DuckPhp\Core\Route](Core-Route.md)
* 运行时数据类 [DuckPhp\Core\RuntimeState](Core-RuntimeState.md)
* 视图类 [DuckPhp\Core\View](Core-View.md)



## 选项

### 专有选项

        'default_exception_do_log' => true,
发生异常时候记录日志

        'default_exception_self_display' => true,
发生异常的时候如有可能，调用异常类的 display() 方法。

        'close_resource_at_output' => false,
输出时候关闭资源输出（仅供第三方扩展参考

        'injected_helper_map' => '',
injected_helper_map 比较复杂待文档。和助手类映射相关。 v1.2.8-dev

        'error_404' => null,          //'_sys/error-404',
404 错误处理 的View或者回调

        'error_500' => null,          //'_sys/error-500',
500 错误处理 View或者回调

        'error_debug' => null,        //'_sys/error-debug',
调试的View或者回调

### 扩充 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 的默认选项。


详情见 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 参考文档

```php
    protected static $options_default = [
            //// not override options ////
            'use_autoloader' => false,
            'skip_plugin_mode_check' => false,
            
            //// basic config ////
            'path' => null,
            'namespace' => null,
            'override_class' => '',
            //
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
        ];
```

## 方法


### 独有的静态方法

## 详解
DuckPhp\Core\App 类 可以视为几个类的组合

### 作为内核的 App 入口类
详见 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)

### 助手函数，助手类，和本类的关系

助手类的静态方法都调用本类的静态方法实现。

相关代码请参考相应助手类方法。 

 + [AdvanceHelper](Helper-AdvanceHelper.md)
 + [BusinessHelper](Helper-BusinessHelper.md)
 + [ControllerHelper](Helper-ControllerHelper.md)
 + [ModelHelper](Helper-ModelHelper.md)
 + [ViewHelper](Helper-ViewHelper.md)



### 动态方法


    public function version()
版本，目前在 命令行中用到

    public function extendComponents($method_map, $components = [])
扩充调用方法

    public function cloneHelpers($new_namespace, $new_helper_map = [])
复制助手函数群

    public function addBeforeShowHandler($handler)
高级

    public function removeBeforeShowHandler($handler)
高级


    public function addDynamicComponentClass($class)
添加动态组件，补完 KernelTrait

    public function skip404Handler()
跳过 404 处理，用于协程类

### 接管流程的函数
    public function __construct()
构造函数

    public function _On404(): void
处理 404

    public function _OnDefaultException($ex): void
处理异常

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
处理开发期错误


### 内置 trait Core_SystemWrapper
内置 trait Core_SystemWrapper 用于替换同名函数。这些方法，和手册里的一致，只是为了兼容不同平台

```php
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public static function exit($code = 0)
    public static function set_exception_handler(callable $exception_handler)
    public static function register_shutdown_function(callable $callback, ...$args)
    public static function session_start(array $options = [])
    public static function session_id($session_id = null)
    public static function session_destroy()
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
```
SystemWrapperTrait 还有两个特殊函数

    public static function system_wrapper_replace(array $funcs)

替换系统默认函数。第三方服务器使用

    public static function system_wrapper_get_providers():array

能提供的系统默认函数列表


### 内置 trait Core_Helper
内置 trait Core_Helper 用于各种助手方法

#### 跳转
```php
    public static function ExitJson($ret, $exit = true)
    public static function ExitRedirect($url, $exit = true)
    public static function ExitRedirectOutside($url, $exit = true)
    public static function ExitRouteTo($url, $exit = true)
    public static function Exit404($exit = true)
```
#### 字符串处理
```php
    public static function H($str)
    public static function L($str, $args = [])
    public static function Hl($str, $args = [])
    public static function Json($data)
    
    public static function Render($view, $data = null)
```
#### 调试相关
```php
    public static function var_dump(...$args)
    public static function Platform()
    public static function IsDebug()
    public static function IsRealDebug()
    public static function TraceDump()
    public static function Logger($object = null)
    public static function DebugLog($message, array $context = array())
    public static function XpCall($callback, ...$args)
    public static function CheckException($exception_class, $message, $code = 0)
```
#### SQL 相关
```php
    public static function SqlForPager($sql, $pageNo, $pageSize = 10)
    public static function SqlForCountSimply($sql)
```
#### 分页
分页器默认没加载
```php
    public static function Pager($object = null)
    public static function PageNo($new_value = null)
    public static function PageSize($new_value = null)
    public static function PageHtml($total, $options = [])
```
#### 其他

    public static function Cache($object = null)
缓存对象

    public static function Show($data = [], $view = '')
Show 方法对 View::Show() 加了好些补充

    public static function IsAjax()
检查是否是 Ajax 请求

    public static function CheckRunningController($self, $static)
检查是否是当前控制器类是否运行

例子，比如你放一个父类在 控制器目录底下，不希望直接被执行的时候。 在构造方法里调用这个方法，
`C::CheckRunningController(self::class, static::class)`
如果是被调用，则 404。 如果是两者相等，则返回 true ，否则返回 false;

### 内置 trait  Core_NotImplemented
内置 trait Core_NotImplemented DuckPhp\Core\App 没实现，但 DuckPhp\DuckPhp 类实现的的方法。数据库和事件系统
```php
    public static function Db($tag = null)
    public static function DbCloseAll()
    public static function DbForWrite()
    public static function DbForRead()
    public static function Event()
    public static function FireEvent($event, ...$args)
    public static function OnEvent($event, $callback)
```

### 内置 trait Core_Glue

内置 trait Core_Glue 用于粘合其他类
大写的方法是复制相关类的静态方法。小写是动态方法
以下按粘合的类区分：

#### 来自RuntimeState
```php
    public static function isInException()
    public static function isRunning()
```
#### 来自 Configer
```php
    public static function Setting($key)
    public static function Config($key, $file_basename = 'config')
    public static function LoadConfig($file_basename)
```
#### 来自 AutoLoader
来自 AutoLoader 的两个方法，主要用于没把 composer 作为加载器使用的情况
```php 
    public static function assignPathNamespace($path, $namespace = null)
    public static function runAutoLoader()
```
#### 来自 Route
来自Route 的方法比较多。重点掌握

    public static function Route($replacement_object = null)
获得 Route 对象

    public static function Url($url = null)
获得 Url

    public static function Domain($use_scheme = false)
获得域名

    public static function Parameter($key = null, $default = null)
获取存储的 paramters 。rewrite 之后会保存在这。

    public static function getPathInfo()
获取 PathInfo

    public static function replaceControllerSingelton($old_class, $new_class)
单例模式，替换控制器类， 控制器类的 单例模式不能简单的 $old_class::G($new_class) 替换。


```php
    public static function addRouteHook($callback, $position, $once = true)
    public static function add404RouteHook($callback)
    public static function getRouteCallingMethod()
    public static function dumpAllRouteHooksAsString()
```
#### 来自 View
需要指出的是 App::Show 是对 View::G()->\_Show() 多了处理。所以不在这里
```php
    public static function Display($view, $data = null)
    public static function getViewData()
    public static function setViewHeadFoot($head_file = null, $foot_file = null)
    public static function assignViewData($key, $value = null)
```
#### 来自 ExceptionManager
```php
    public static function CallException($ex)
    public static function assignExceptionHandler($classes, $callback = null)
    public static function setMultiExceptionHandler(array $classes, callable $callback)
    public static function setDefaultExceptionHandler(callable $callback)
```

### 内置 trait Core_SuperGlobal

内置 trait Core_SuperGlobal 主要用于超全局变量处理
```php
    public static function GET($key = null, $default = null)
    public static function POST($key = null, $default = null)
    public static function REQUEST($key = null, $default = null)
    public static function COOKIE($key = null, $default = null)
    public static function SERVER($key = null, $default = null)
    public static function SESSION($key = null, $default = null)
    public static function FILES($key = null, $default = null)
```
这些对应于超全局变量 $_GET[$key]??$value; 类推。如果宏 \_\_SUPERGLOBAL_CONTEXT 被定义，那么将 获得 (\_\_SUPERGLOBAL_CONTEXT)()->\_GET 等
```php
    public static function SessionSet($key, $value)
    public static function SessionUnset($key)
    public static function SessionGet($key, $default = null)

```
因为 Session 不仅仅读取，还有写入，所以用 SessionGet  /SessionSet 对称方法 。因为 \_\_SUPERGLOBAL_CONTEXT ，还有了 SessionUnset


```php
    public static function CookieSet($key, $value, $expire = 0)
```
因为 Cookie 不仅仅读取，还有写入，所以用 CookieSet 。


    public static function CookieGet($key, $default = null)
对称， CookieGet / CookieSet

### 内部实现函数

这些都是内部没下划线前缀的静态方法的动态实现。 不用 protected 是因为想让非继承的类也能修改实现。

```php
    public function _Show($data = [], $view = '')
    public function _header($output, bool $replace = true, int $http_response_code = 0)
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public function _exit($code = 0)
    public function _set_exception_handler(callable $exception_handler)
    public function _register_shutdown_function(callable $callback, ...$args)
    public function _session_start(array $options = [])
    public function _session_id($session_id = null)
    public function _session_destroy()
    public function _session_set_save_handler(\SessionHandlerInterface $handler)
    public function _ExitJson($ret, $exit = true)
    public function _ExitRedirect($url, $exit = true)
    public function _ExitRedirectOutside($url, $exit = true)
    public function _Platform()
    public function _IsDebug()
    public function _IsRealDebug()
    public function _L($str, $args = [])
    public function _Hl($str, $args)
    public function _Json($data)
    public function _H(&$str)
    public function _TraceDump()
    public function _var_dump(...$args)
    public function _XpCall($callback, ...$args)
    public function _CheckException($exception_class, $flag, $message, $code = 0)
    public function _SqlForPager($sql, $pageNo, $pageSize = 10)
    public function _SqlForCountSimply($sql)
    public function _DebugLog($message, array $context = array())
    public function _Cache($object = null)
    public function _Pager($object = null)
    public function _DbCloseAll()
    public function _Db($tag)
    public function _DbForRead()
    public function _DbForWrite()
    public function _Event()
    public function _FireEvent($event, ...$args)

    public function _OnEvent($event, $callback)
    public function _GET($key = null, $default = null)
    public function _POST($key = null, $default = null)
    public function _REQUEST($key = null, $default = null)
    public function _COOKIE($key = null, $default = null)
    public function _SERVER($key = null, $default = null)
    public function _SESSION($key = null, $default = null)
    public function _FILES($key = null, $default = null)
    public function _SessionSet($key, $value)
    public function _SessionUnset($key)
    public function _CookieSet($key, $value, $expire)
    public function _SessionGet($key, $default)
    public function _CookieGet($key, $default)

    public function _IsAjax()
    public function _CheckRunningController($self, $static)

    
```

### 内部函数

    protected function extendComponentClassMap($map, $namespace)
    protected function fixNamespace($class, $namespace)
    protected function onBeforeOutput()
    private function getSuperGlobalData($superglobal_key, $key, $default)


## 说明

### 关于 injected_helper_map 。 有时间再详细文档。

以上



