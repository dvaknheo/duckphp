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
"injected_helper_map" =>'', 

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
详见 DuckPhp\Core\KernelTrait

### 助手类引用的静态方法

助手类的静态方法都调用本类的静态方法实现。

为了避免重复，请在相关助手类里查看参考

相关代码请参考 

 + [HelperTrait](Helper-AppHelper.md)
 + [AppHelper](Helper-AppHelper.md)
 + [BusinessHelper](Helper-BusinessHelper.md)
 + [ControllerHelper](Helper-ControllerHelper.md)
 + [ModelHelper](Helper-ModelHelper.md)
 + [ViewHelper](Helper-ViewHelper.md)

或者，按分类
### override 重写的方法


### 主要的动态方法
```php
public function version()
public function extendComponents($method_map, $components = [])
public function cloneHelpers($new_namespace, $new_helper_map = [])
public function addBeforeShowHandler($handler)
public function removeBeforeShowHandler($handler)

public function getDynamicComponentClasses()
public function addDynamicComponentClass($class)
public function addDynamicComponentClass($class)
public function skip404Handler()
```

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
system_wrapper_replace() 替换系统默认函数

system_wrapper_get_providers() 能提供的系统默认函数列表


### 内置 trait Core_Helper
内置 trait Core_Helper 用于各种助手方法

#### 退出类：
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
    public static function Domain()
```
#### 调试相关
```php
    public static function var_dump(...$args)
    public static function Platform()
    public static function IsDebug()
    public static function IsRealDebug()
    public static function TraceDump()
    public static function XpCall($callback, ...$args)
    public static function CheckException($exception_class, $message, $code = 0)
    public static function Logger($object = null)
    public static function DebugLog($message, array $context = array())
```
#### SQL 相关,
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

```php
    public static function Cache($object = null)
    public static function Show($data = [], $view = '')
```

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
```php
public static function Route($replacement_object = null)
public static function Url($url = null)
public static function Parameter($key, $default = null)
public static function replaceControllerSingelton($old_class, $new_class)
public static function getPathInfo()
public static function getParameters()
public static function addRouteHook($hook, $position, $once = true)
public static function add404RouteHook($callback)
public static function getRouteCallingMethod()
public static function setRouteCallingMethod(string $method)
public static function setUrlHandler($callback)
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
```
因为 Session 不仅仅读取，还有写入，所以用 SessionSet 。
```php
public static function CookieSet($key, $value, $expire=0)
```
因为 Cookie 不仅仅读取，还有写入，所以用 CookieSet 。
### 关于 injected_helper_map

## 全方法索引

//待脚本