# Helper\ControllerHelper

## 简介

控制器助手类

## 方法

### 助手类公开静态方法

[助手类公开静态方法](Helper-HelperTrait.md)
IsDebug()
IsRealDebug()
Platform()
Logger()
trace_dump()
var_dump(...$args)

### 超全局变量
GET($key, $default = null)
POST($key, $default = null)
REQUEST($key, $default = null)
COOKIE($key, $default = null)
显示相关

H($str)
L($str, $args = [])
HL($str, $args = [])
Display($view, $data = null)
Show($data = [], $view = null)
setViewWrapper($head_file = null, $foot_file = null)
assignViewData($key, $value = null)

### 配置相关
Setting($key)
Config($key, $file_basename = 'config')
LoadConfig($file_basename)

### 路由相关

URL($url)
Domain()
ExitRedirect($url, $exit = true)
ExitRedirectOutside($url, $exit = true)
ExitRouteTo($url, $exit = true)
Exit404($exit = true)
ExitJson($ret, $exit = true)
getParameters()
getRouteCallingMethod()
setRouteCallingMethod($method)
getPathInfo()

### 系统兼容替换
header($output, bool $replace = true, int $http_response_code = 0)
setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
exit($code = 0)

SG($object=null)



### 异常处理

assignExceptionHandler($classes, $callback = null)
setMultiExceptionHandler(array $classes, $callback)
setDefaultExceptionHandler($callback)


### 分页相关
- Pager($object = null)
- PageNo()
- PageSize($new_value = null)
- PageHtml($total)

## 助手类公用方法列表
- IsDebug()

    判断是否在调试状态，App 的  `is_debug` 选项 ,`duckphp_is_debug` 设置项。
    
- IsRealDebug()
    这个用于调试标识开，但是实际还是调试状态。用于特定用处。
    
- Platform()
    获得平台标志，App 的  `platform` 选项 ,`duckphp_platform` 设置项。
    
- Logger($object=null)
    返回Logger类。
    $object 是替换入的新的 Logger 类。
    
- trace_dump()
    显示调用堆栈
    
- var_dump(...$args)
    替代 var_dump ，在非调试状态下不显示。
    
- ThrowOn($flag, $message, $code = 0, $exception_class = null) 详见 [Core/ThrowOn](Core-ThrowOn.md)

    如果 $flag成立则抛出异常，如果未指定 $exception_class，抛则判断当前类是否是 Exception 类的子类，如果不是，则默认为 Exception 类。    
- AssignExtendStaticMethod($key, $value = null)   详见 [Core/ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    分配固定方法。

- GetExtendStaticMethodList() 详见 [Core/ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    获得
- \_\_callStatic($name, $arguments) 详见 [Core/ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md)
    静态方法已经被接管。
## 详解


## 额外单独扩展的方法

RecordsetUrl [DuckPhp\Ext\Misc::RecordsetUrl](Ext-Misc.md#RecordsetUrl)
	
    DuckPhp\Ext\Misc::RecordsetUrl
RecordsetH
	
    DuckPhp\Ext\Misc::RecordsetH
CallAPI
	
    DuckPhp\Ext\Misc::CallAPI
assignRewrite
	
    DuckPhp\Ext\RouteHookRewrite::G::assignRewrite
getRewrites
	
    DuckPhp\Ext\RouteHookRewrite::G::getRewrites
getRoutes
	
    DuckPhp\Ext\RouteHookRouteMap::G::getRoutes

## 详解

Controller Helper 全是静态方法，调用 App 类的内容。


## 方法索引

    public static function Setting($key)
    public static function Config($key, $file_basename = 'config')
    public static function LoadConfig($file_basename)
    public static function H($str)
    public static function L($str, $args = [])
    public static function HL($str, $args = [])
    public static function Display($view, $data = null)
    public static function URL($url)
    public static function Domain()
    public static function getParameters()
    public static function getRouteCallingMethod()
    public static function setRouteCallingMethod($method)
    public static function getPathInfo()
    public static function Show($data = [], $view = null)
    public static function setViewWrapper($head_file = null, $foot_file = null)
    public static function assignViewData($key, $value = null)
    public static function ExitRedirect($url, $exit = true)
    public static function ExitRedirectOutside($url, $exit = true)
    public static function ExitRouteTo($url, $exit = true)
    public static function Exit404($exit = true)
    public static function ExitJson($ret, $exit = true)
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    public static function exit($code = 0)
    public static function assignExceptionHandler($classes, $callback = null)
    public static function setMultiExceptionHandler(array $classes, $callback)
    public static function setDefaultExceptionHandler($callback)
    public static function SG()
    public static function GET($key, $default = null)
    public static function POST($key, $default = null)
    public static function REQUEST($key, $default = null)
    public static function COOKIE($key, $default = null)
    public static function Pager($object = null)
    public static function PageNo()
    public static function PageSize($new_value = null)
    public static function PageHtml($total)
