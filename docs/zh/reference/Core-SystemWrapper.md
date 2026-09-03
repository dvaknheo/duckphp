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
2. `_exit`：定义了以 `__SYSTEM_WRAPPER..` 且 exit_class 为 \Throwable → throw `new __EXIT_EXCEPTION`（而不是 process-exit）；否则真 exit。
3. `_setcookie` 要求字符串参数等，有 domain/secure/flags。
4. 每方法都先走 check/call (look at REPLACER/handler/delay) —— 看到可替换性设计，并非总是断言命名规则，具体看源码 `system_wrapper_call*`。
5. 受保护 `getMimeData()` 内含内置常用 mime types 表（扩展名→mime），供无 `mime_content_type` 的环境。

## 方法列表

> 每组函数封装都有 public static（shell）与 public 实例方法 `_xxx`，语义一致；二者均支持 handlers/REPLACER override。

### 静态与实例外壳（成对）

    header($output, bool $replace = true, int $http_response_code = 0)  // & _header
发送原始 HTTP 头(work in web & obey replace/status)；默认环境可写 header，若有 sub-overrider优先。

    setcookie(string $key,string $value='',int $expire=0,string $path='/',string $domain='',bool $secure=false,bool $httponly=false)  // _setcookie
原生 setcookie 封装（含一定签名一致）。

    exit($code=0)  // _exit
见注意事项 2（__EXIT_EXCEPTION 抛异常代替 exit）。

    set_exception_handler(callable $handler)  // _set_exception_handler
设全局异常handler（仍可替换捕获）。

    register_shutdown_function(callable $cb, ...$args)  // _register_shutdown_function
注册 shutdown。

    session_start(array $options = [])  // _session_start
带默认 handlers，@抑制。

    session_id($session_id = null)  // _session_id
取或设 id。

    session_destroy()  // _session_destroy

    session_set_save_handler(\SessionHandlerInterface $h)  // _session_set_save_handler

    mime_content_type($file)  // `_mime_content_type
内置表，无 mime 函数时给出扩展名→type；带 sub-replacer 优先。

### 替换 / 供给

    public static function system_wrapper_replace(array $funcs)
按名覆盖 this handlers → 返回 true。

    public function _system_wrapper_replace(array $funcs)（同）

    public static function system_wrapper_get_providers():array
取值：缺省按 [$class,$name] 提供 callable 的对像；返回 全数组替换方案。

    public function _system_wrapper_get_providers()（同）

### 受保护方法

    protected function system_wrapper_call_check(string $func): bool
是否要为某系统函数走 override（__SYSTEM_WRAPPER_REPLACER 可调或 handler 设过）。

    protected function system_wrapper_call(string $func, array $input_args)
若 REPLACER 调用之；否则 handler；否则直接(原 PHP)call，缺函数抛 ErrorException。

    protected function getMimeData(): string
内置 mime 类型表（heredoc），供 _mime_content_type fallback。

## 相关链接

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) —— header/cake 由 cookie 发
- [DuckPhp\Core\App](Core-App.md) —— error handler / exit exception使用
- [DuckPhp\Core\ExitException](Core-ExitException.md) —— `__EXIT_EXCEPTION`语义（exit 转异常）
- guide：testing —— 测试隔离 header/session
