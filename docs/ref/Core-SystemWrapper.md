# DuckPhp\Core\SystemWrapper
[toc]

## 简介

替换同名系统函数保持兼容性

## 方法

    public static function system_wrapper_replace(array $funcs)
    public function _system_wrapper_replace(array $funcs)
第三方替换本类提供的系统函数
    
    public static function system_wrapper_get_providers():array
    public function _system_wrapper_get_providers()
获得本类能替换的系统函数，返回数组   

    protected function system_wrapper_call_check($func)
检查类内是否有相应的系统函数实现。

    protected function system_wrapper_call($func, $input_args)
相关例子在 `DuckPhp\Core\App` 里

## 说明

配合属性 protected $system_handlers=[]; 和 G() 方法用。


v1.2.13 添加了默认实现

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

    public function _header($output, bool $replace = true, int $http_response_code = 0)

    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)

    public function _exit($code = 0)

    public function _set_exception_handler(callable $exception_handler)

    public function _register_shutdown_function(callable $callback, ...$args)

    public function _session_start(array $options = [])

    public function _session_id($session_id = null)

    public function _session_destroy()

    public function _session_set_save_handler(\SessionHandlerInterface $handler)

    public function _mime_content_type($file)

    protected function getMimeData()

