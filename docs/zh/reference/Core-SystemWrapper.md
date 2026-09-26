# DuckPhp\Core\SystemWrapper

把一批 PHP 系统函数（header/setcookie/exit/异常/会话/mime）包成“统一 static/instance 入口”，可在 CLI / 测试 / 多请求环境被无缝替代或拦截。

## 简介

`SystemWrapper` 把常见“副作用型”系统函数放到一处，便于：CLI 下不抛 header、测试时能注入行为、或整包交给替换实现（`__SYSTEM_WRAPPER_REPLACER` 常量）。

它以 `system_handlers` 记录一组 handler（函数名→回调，null 表示默认），静态壳/实例方法都叫同名（如 `header` 与 `_header`）。通用转发 `system_wrapper_replace(handlers)` 可整体替换某几个的实装，GET_PROVIDERS 把这些默认补齐。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class SystemWrapper extends ComponentBase`（`init_once=true`）
- 关联常量：`__SYSTEM_WRAPPER_REPLACER` —— 若定义成某类名，任意 wrapper 调用会送去 `[常量, 函数名](...)`；否则用 handlers → 原样系统函数。

## 选项

本类无 user opts；内部 `protected $system_handlers` 为下列函数的默认列表：
`header/setcookie/exit/set_exception_handler/register_shutdown_function/session_start/session_id/session_destroy/session_set_save_handler/mime_content_type`（值 null=默认）。

## 使用方式

常规不需直接调用；它们被 App/Core 代替那些调用。也可:

```php
use DuckPhp\Core\SystemWrapper;

SystemWrapper::header('Location: /x', true, 302);
SystemWrapper::setcookie('tk','v',0,'/');
SystemWrapper::exit(0);                 // 有 __EXIT_EXCEPTION => 抛（否则真 exit）
SystemWrapper::session_start();
SystemWrapper::session_set_save_handler($myHandler);
$mime = SystemWrapper::mime_content_type($file);
```

### 整体替换一批系统函数（测试内）：

```php
// 直接为提供默认实现
$ret = SystemWrapper::system_wrapper_replace([
   'header'  => function ($out, $r=true, $c=0) { /* spy */ },
]);
```

### 简化 wrapper 升级到全整组供给者：

```php
$providers = SystemWrapper::system_wrapper_get_providers();   //返回 函数=> impl(默认即 [this,'x'])
```

## 配置示例

利用 `__SYSTEM_WRAPPER_REPLACER`：

```php
// 启动时定义：
define('__SYSTEM_WRAPPER_REPLACER', MyReplacer::class);
// 之后 SystemWrapper::* 若 myreplacer 有同名 method 就会被调
```
（多数环境用自动默认 handler 就够了。）

## 注意事项

1. CLI/已 headers 的 SAPI：`_header` 在 cli 或 headers_sent 时静默 return，不炸。
2. `_exit`：定义了 `__EXIT_EXCEPTION` 且它是 `\Throwable` 的子类时 → `throw new $exit_class($code)`（而不是 process-exit）；否则真 exit。
3. `_setcookie` 要求字符串参数等，有 domain/secure/flags。
4. 每方法都先走 check/call (look at REPLACER/handler/delay) —— 看到可替换性设计，并非总是断言命名规则，具体看源码 `system_wrapper_call*`。
5. 受保护 `getMimeData()` 内含内置常用 mime types 表（扩展名→mime），供无 `mime_content_type` 的环境。

## 方法列表

> 每组系统函数封装都有 `public static`（壳）与 `public` 实例方法 `_xxx`，语义一致，均支持 handlers / REPLACER 覆盖。

### 公共方法（静态壳与实例实现）

    public static function system_wrapper_replace(array $funcs)
按名覆盖系统函数实现（handlers），返回是否成功。

    public function _system_wrapper_replace(array $funcs)
`system_wrapper_replace()` 的实例实现。

    public static function system_wrapper_get_providers(): array
返回当前全部系统函数提供者（callable 表）。

    public function _system_wrapper_get_providers()
`system_wrapper_get_providers()` 的实例实现。

    public static function header($output, bool $replace = true, int $http_response_code = 0)
发送原始 HTTP 头（静态壳 → `_header`）。

    public function _header($output, bool $replace = true, int $http_response_code = 0)
发送 HTTP 头实现（web 环境；遵循 replace/status；可被替换）。

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
`setcookie` 封装（静态壳 → `_setcookie`）。

    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
`setcookie` 实现。

    public static function exit($code = 0)
`exit` 封装（静态壳 → `_exit`）；见注意事项（`__EXIT_EXCEPTION` 时抛异常代替 exit）。

    public function _exit($code = 0)
`exit` 实现。

    public static function set_exception_handler(callable $exception_handler)
`set_exception_handler` 封装（静态壳）。

    public function _set_exception_handler(callable $exception_handler)
`set_exception_handler` 实现。

    public static function register_shutdown_function(callable $callback, ...$args)
`register_shutdown_function` 封装（静态壳）。

    public function _register_shutdown_function(callable $callback, ...$args)
`register_shutdown_function` 实现。

    public static function session_start(array $options = [])
`session_start` 封装（静态壳）。

    public function _session_start(array $options = [])
`session_start` 实现（带默认 handlers）。

    public static function session_id($session_id = null)
`session_id` 封装（读/设）。

    public function _session_id($session_id = null)
`session_id` 实现。

    public static function session_destroy()
`session_destroy` 封装（静态壳）。

    public function _session_destroy()
`session_destroy` 实现。

    public static function session_set_save_handler(\SessionHandlerInterface $handler)
`session_set_save_handler` 封装（静态壳）。

    public function _session_set_save_handler(\SessionHandlerInterface $handler)
`session_set_save_handler` 实现。

    public static function mime_content_type($file)
取文件 MIME（静态壳 → `_mime_content_type`）。

    public function _mime_content_type($file)
MIME 实现：无 `mime_content_type` 函数时按扩展名查内置表；可被替换。

### 受保护方法

    protected function system_wrapper_call_check(string $func): bool
判断某系统函数是否走 override（`__SYSTEM_WRAPPER_REPLACER` 或已设 handler）。

    protected function system_wrapper_call(string $func, array $input_args)
统一调用：REPLACER 优先 → handler → 原生函数（缺失抛 `ErrorException`）。

    protected function getMimeData(): string
内置 MIME 类型表（heredoc），供 `_mime_content_type` 兜底。

## 相关链接

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) —— 请求数据的读取侧（`_GET`/`_POST`/`_COOKIE`/`_SERVER` 的隔离访问）；本类管 header/cookie 的写出与 `exit`
- [DuckPhp\Core\App](Core-App.md) —— error handler / exit exception使用
- [DuckPhp\Core\ExitException](Core-ExitException.md) —— `__EXIT_EXCEPTION`语义（exit 转异常）
- guide：testing —— 测试隔离 header/session
