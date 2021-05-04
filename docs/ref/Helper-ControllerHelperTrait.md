# DuckPhp\Helper\ControllerHelperTrait
[toc]

## 简介

ControllerHelper 绑定了 ControllerHelperTrait

ControllerHelper 绑定了 [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) ，ControllerHelperTrait


### 超全局变量
    public static function GET($key = null, $default = null)
    
    public static function POST($key = null, $default = null)
    
    public static function REQUEST($key = null, $default = null)
    
    public static function COOKIE($key = null, $default = null)
    
    public static function SERVER($key, $default = null)


### 显示相关

    public static function H($str)
    
    public static function Json($data)

    
    public static function L($str, $args = [])
    
    public static function Hl($str, $args = [])
    
    public static function Display($view, $data = null)

    public static function Show($data = [], $view = '')
    
    public static function setViewHeadFoot($head_file = null, $foot_file = null)
    
    public static function assignViewData($key, $value = null)

### 配置相关
    public static function Setting($key)
    
    public static function Config($key, $file_basename = 'config')
    
    public static function LoadConfig($file_basename)

### 路由相关

    public static function Url($url)
    
    public static function Domain($use_scheme = false)
    
    public static function ExitRedirect($url, $exit = true)
    
    public static function ExitRedirectOutside($url, $exit = true)
    
    public static function ExitRouteTo($url, $exit = true)
    
    public static function Exit404($exit = true)
    
    public static function ExitJson($ret, $exit = true)
    
    public static function Parameter($key, $default = null)
    
    public static function getRouteCallingMethod()
    
    public static function setRouteCallingMethod($method)
    
    public static function getPathInfo()
    
    public static function dumpAllRouteHooksAsString()

### 系统兼容替换

    public static function header($output, bool $replace = true, int $http_response_code = 0)
    
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    
    public static function exit($code = 0)

### 异常处理

    public static function assignExceptionHandler($classes, $callback = null)
    
    public static function setMultiExceptionHandler(array $classes, $callback)
    
    public static function setDefaultExceptionHandler($callback)


### 分页相关

    public static function PageNo($new_value = null)
    
    public static function PageSize($new_value = null)
    
    public static function PageHtml($total, $options = [])

### 其他


    public static function DbCloseAll()

    public static function XpCall($callback, ...$args)
    
    public static function FireEvent($event, ...$args)
    
    public static function Logger($object = null)

### 结束
