# DuckPhp\Helper\AdvanceHelperTrait
[toc]

## 简介

AdvanceHelperTrait 的文档参见 [DuckPhp\Helper\AdvanceHelper](Helper-AdvanceHelper.md) 类

`DuckPhp\Helper\AdvanceHelper` 类

 使用 [DuckPhp\Core\ExtendableStaticCallTrait](Core-ExtendableStaticCallTrait.md) 

 使用 `DuckPhp\Helper\AdvanceHelperTrait`
## 公开方法

### 一般方法

    public static function CallException($ex)
    
    public static function isRunning()
    
    public static function isInException()
    
    public static function assignPathNamespace($path, $namespace = null)
    
    public static function addRouteHook($hook, $position, $once = true)
    
    public static function add404RouteHook($callback)
        
    public static function replaceControllerSingelton($old_class, $new_class)
    
    public static function getViewData()

### 全局和杂项

    public static function SESSION($key = null, $default = null)
    
    public static function FILES($key = null, $default = null)
    
    public static function SessionSet($key, $value)
    
    public static function CookieSet($key, $value, $expire = 0)
    
    public static function Event()

    public static function OnEvent($event, $callback)

### 系统替代函数

这些函数，和系统函数同名，目的是兼容 swoole/workerman 等平台。

    public static function header($output, bool $replace = true, int $http_response_code = 0)
    
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    
    public static function exit($code = 0)
    
    public static function set_exception_handler(callable $exception_handler)
    
    public static function register_shutdown_function(callable $callback, ...$args)
    
    public static function session_start(array $options = [])
    
    public static function session_id($session_id = null)
    
    public static function session_destroy()
    
    public static function session_set_save_handler(\SessionHandlerInterface $handler)


### App类的动态方法

    public static function extendComponents($method_map, $components = [])
    
    public static function cloneHelpers($new_namespace, $componentClassMap = [])
    
    public static function addBeforeShowHandler($handler)
    
    public static function getDynamicComponentClasses()
    
    public static function addDynamicComponentClass($class)



### 结束


​    

​    



