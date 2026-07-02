# DuckPhp\Core\SystemWrapper

系统函数包装组件。

## 简介

`SystemWrapper` 对 PHP 的系统函数（如 `header`、`setcookie`、`exit`、`session_start`、`mime_content_type` 等）进行了包装。通过替换处理器，可以在单元测试中 mock 这些系统调用，避免真实执行。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

`SystemWrapper` 本身没有额外的配置选项，通过 `system_wrapper_replace` 方法替换系统函数处理器。

## 使用方式

### 静态调用系统函数

```php
use DuckPhp\Core\SystemWrapper;

SystemWrapper::header('Content-Type: application/json');
SystemWrapper::setcookie('name', 'value', 3600);
SystemWrapper::session_start();
$mime = SystemWrapper::mime_content_type('logo.png');
```

### 替换系统函数处理器

```php
use DuckPhp\Core\SystemWrapper;

$records = [];
SystemWrapper::_()->_system_wrapper_replace([
    'header' => function ($output) use (&$records) {
        $records[] = $output;
    },
    'setcookie' => function ($key, $value, $expire) use (&$records) {
        $records[] = [$key, $value, $expire];
    },
]);
```

### 获取当前处理器提供者

```php
use DuckPhp\Core\SystemWrapper;

$providers = SystemWrapper::_()->_system_wrapper_get_providers();
// 返回所有系统函数对应的处理器，未替换的默认指向当前类的方法
```

### 使用 `__SYSTEM_WRAPPER_REPLACER`

```php
// 定义替换类，所有系统函数调用都会转发到该类的同名方法
if (!defined('__SYSTEM_WRAPPER_REPLACER')) {
    define('__SYSTEM_WRAPPER_REPLACER', \MyApp\Test\SystemMock::class);
}
```

## 支持的系统函数

| 函数 | 说明 |
|---|---|
| `header` | 发送 HTTP 头。 |
| `setcookie` | 设置 Cookie。 |
| `exit` | 终止程序。若定义了 `__EXIT_EXCEPTION`，会抛出对应异常。 |
| `set_exception_handler` | 设置异常处理函数。 |
| `register_shutdown_function` | 注册关闭时回调。 |
| `session_start` | 启动 Session。 |
| `session_id` | 获取或设置 Session ID。 |
| `session_destroy` | 销毁 Session。 |
| `session_set_save_handler` | 设置自定义 Session 存储处理器。 |
| `mime_content_type` | 根据文件扩展名获取 MIME 类型。 |

## 配置示例

```php
class App extends \DuckPhp\DuckPhp
{
    public function onInit()
    {
        SystemWrapper::_()->_system_wrapper_replace([
            'header' => function ($output) {
                // 自定义 header 处理
            },
        ]);
    }
}
```

## 注意事项

1. 所有方法调用都会先检查是否被替换，如果没有被替换则调用原生的 PHP 系统函数。
2. 当定义了 `__SYSTEM_WRAPPER_REPLACER` 常量时，所有调用会转发到该类的同名方法，优先级高于 `system_handlers`。
3. `exit` 在定义了 `__EXIT_EXCEPTION` 时会抛出该异常，否则调用原生 `exit()`。
4. `mime_content_type()` 使用内置的 MIME 类型映射表，不依赖 `fileinfo` 扩展。

## 方法列表

### 公共方法

    public static function system_wrapper_replace(array $funcs)
静态入口：替换一个或多个系统函数处理器

    public static function system_wrapper_get_providers(): array
静态入口：返回所有系统函数当前的处理器提供者

    public function _system_wrapper_replace(array $funcs)
替换系统函数处理器

    public function _system_wrapper_get_providers()
获取所有系统函数处理器，未替换的默认指向当前类方法

    public static function header($output, bool $replace = true, int $http_response_code = 0)
调用 `header` 函数

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
调用 `setcookie` 函数

    public static function exit($code = 0)
调用 `exit` 函数

    public static function set_exception_handler(callable $exception_handler)
调用 `set_exception_handler` 函数

    public static function register_shutdown_function(callable $callback, ...$args)
调用 `register_shutdown_function` 函数

    public static function session_start(array $options = [])
调用 `session_start` 函数

    public static function session_id($session_id = null)
调用 `session_id` 函数

    public static function session_destroy()
调用 `session_destroy` 函数

    public static function session_set_save_handler(\SessionHandlerInterface $handler)
调用 `session_set_save_handler` 函数

    public static function mime_content_type($file)
调用 `mime_content_type` 函数

    public function _header($output, bool $replace = true, int $http_response_code = 0)
`header` 包装实现，CLI 环境下不执行

    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
`setcookie` 包装实现

    public function _exit($code = 0)
`exit` 包装实现

    public function _set_exception_handler(callable $exception_handler)
`set_exception_handler` 包装实现

    public function _register_shutdown_function(callable $callback, ...$args)
`register_shutdown_function` 包装实现

    public function _session_start(array $options = [])
`session_start` 包装实现

    public function _session_id($session_id = null)
`session_id` 包装实现

    public function _session_destroy()
`session_destroy` 包装实现

    public function _session_set_save_handler(\SessionHandlerInterface $handler)
`session_set_save_handler` 包装实现

    public function _mime_content_type($file)
根据扩展名返回 MIME 类型

### 受保护方法

    protected function system_wrapper_call_check($func)
检查指定函数是否被替换或存在

    protected function system_wrapper_call($func, $input_args)
执行替换后的处理器或原生系统函数

    protected function getMimeData()
返回内置的 MIME 类型映射数据

## 相关链接

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
- [DuckPhp\Core\ExitException](Core-ExitException.md)
