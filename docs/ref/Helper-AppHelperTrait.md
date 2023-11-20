# DuckPhp\Helper\AppHelperTraitTrait
[toc]

## 简介

`DuckPhp\Helper\AppHelperTrait` 类

## 公开方法

### 一般方法

    public static function CallException($ex)
    
    public static function isRunning()
    
    public static function isInException()
    
    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
    
    public static function replaceController($old_class, $new_class)
    
    public static function getViewData()

### 全局和杂项

    public static function SESSION($key = null, $default = null)
    
    public static function FILES($key = null, $default = null)
    
    public static function SessionSet($key, $value)
    
    public static function CookieSet($key, $value, $expire = 0)
    
    public static function SessionGet($key, $default = null)

    public static function CookieGet($key, $default = null)
    
    public static function SessionUnset($key)



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

    public static function mime_content_type($file)

    public static function system_wrapper_replace(array $funcs)

    public static function system_wrapper_get_providers():array
### 结束

    public static function DbCloseAll()

    public static function Event()
    
    public static function OnEvent($event, $callback)

    public static function setBeforeGetDbHandler($db_before_get_object_handler)

    public static function Redis($tag = 0)

    public static function getRoutes()

    public static function assignRoute($key, $value = null)

    public static function assignImportantRoute($key, $value = null)

    public static function assignRewrite($key, $value = null)

    public static function getRewrites()

    public static function RemoveEvent($event, $callback = null)

    public static function getRouteMaps()

    public static function getCliParameters()

