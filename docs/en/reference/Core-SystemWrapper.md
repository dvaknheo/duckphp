# DuckPhp\Core\SystemWrapper

Wraps a batch of PHP system functions (header/setcookie/exit/exception/session/mime) into a "unified static/instance entry" that can be seamlessly replaced or intercepted under CLI / tests / multi-request environments.

## Introduction

`SystemWrapper` puts the common "side-effect" system functions in one place, so that: headers don't throw under CLI, tests can inject behavior, or the whole batch can be handed to a replacement implementation (the `__SYSTEM_WRAPPER_REPLACER` constant).

It records a set of handlers in `system_handlers` (function name → callback, null means default); the static shells and instance methods share names (e.g. `header` and `_header`). The general forwarder `system_wrapper_replace(handlers)` replaces some implementations wholesale; GET_PROVIDERS fills in the defaults.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class SystemWrapper extends ComponentBase` (`init_once=true`)
- Related constant: `__SYSTEM_WRAPPER_REPLACER` — if defined as a class name, every wrapper call is sent to `[constant, function name](...)`; otherwise handlers → the raw system functions.

## Options

This class has no user options; internally `protected $system_handlers` holds the default list for these functions:
`header/setcookie/exit/set_exception_handler/register_shutdown_function/session_start/session_id/session_destroy/session_set_save_handler/mime_content_type` (value null = default).

## Usage

Normally no direct calls are needed; App/Core makes those calls through it. You can also:

```php
use DuckPhp\Core\SystemWrapper;

SystemWrapper::header('Location: /x', true, 302);
SystemWrapper::setcookie('tk','v',0,'/');
SystemWrapper::exit(0);                 // with __EXIT_EXCEPTION => throws (otherwise real exit)
SystemWrapper::session_start();
SystemWrapper::session_set_save_handler($myHandler);
$mime = SystemWrapper::mime_content_type($file);
```

### Replacing a batch of system functions wholesale (in tests):

```php
// provide default implementations directly
$ret = SystemWrapper::system_wrapper_replace([
   'header'  => function ($out, $r=true, $c=0) { /* spy */ },
]);
```

### Upgrading the simplified wrapper to full-group providers:

```php
$providers = SystemWrapper::system_wrapper_get_providers();   // returns function => impl (default is [this,'x'])
```

## Configuration example

Using `__SYSTEM_WRAPPER_REPLACER`:

```php
// define at bootstrap:
define('__SYSTEM_WRAPPER_REPLACER', MyReplacer::class);
// afterwards, SystemWrapper::* calls go to myreplacer's same-named method if it exists
```
(In most environments the automatic default handlers are enough.)

## Caveats

1. CLI / headers-already-sent SAPIs: `_header` returns silently under cli or headers_sent, no blow-up.
2. `_exit`: when the exit class is defined (`__EXIT_EXCEPTION`) and is a \Throwable → throws `new __EXIT_EXCEPTION` (instead of process exit); otherwise a real exit.
3. `_setcookie` requires string arguments etc., with domain/secure/flags.
4. Every method goes through check/call first (REPLACER/handler/delay) — a replaceability design, not a strict naming assertion; see the source `system_wrapper_call*` for details.
5. The protected `getMimeData()` holds a built-in table of common mime types (extension → mime) for environments without `mime_content_type`.

## Methods

> Each wrapped system function has a `public static` (shell) and a `public` instance method `_xxx` with identical semantics; all support handler / REPLACER overrides.

### Public methods (static shells and instance implementations)

    public static function system_wrapper_replace(array $funcs)
Overrides system-function implementations by name (handlers); returns success.

    public function _system_wrapper_replace(array $funcs)
Instance implementation of `system_wrapper_replace()`.

    public static function system_wrapper_get_providers(): array
Returns all current system-function providers (callable table).

    public function _system_wrapper_get_providers()
Instance implementation of `system_wrapper_get_providers()`.

    public static function header($output, bool $replace = true, int $http_response_code = 0)
Sends a raw HTTP header (static shell → `_header`).

    public function _header($output, bool $replace = true, int $http_response_code = 0)
HTTP-header implementation (web environment; honors replace/status; replaceable).

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
`setcookie` wrapper (static shell → `_setcookie`).

    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
`setcookie` implementation.

    public static function exit($code = 0)
`exit` wrapper (static shell → `_exit`); see the caveats (throws instead of exiting with `__EXIT_EXCEPTION`).

    public function _exit($code = 0)
`exit` implementation.

    public static function set_exception_handler(callable $exception_handler)
`set_exception_handler` wrapper (static shell).

    public function _set_exception_handler(callable $exception_handler)
`set_exception_handler` implementation.

    public static function register_shutdown_function(callable $callback, ...$args)
`register_shutdown_function` wrapper (static shell).

    public function _register_shutdown_function(callable $callback, ...$args)
`register_shutdown_function` implementation.

    public static function session_start(array $options = [])
`session_start` wrapper (static shell).

    public function _session_start(array $options = [])
`session_start` implementation (with default handlers).

    public static function session_id($session_id = null)
`session_id` wrapper (read/set).

    public function _session_id($session_id = null)
`session_id` implementation.

    public static function session_destroy()
`session_destroy` wrapper (static shell).

    public function _session_destroy()
`session_destroy` implementation.

    public static function session_set_save_handler(\SessionHandlerInterface $handler)
`session_set_save_handler` wrapper (static shell).

    public function _session_set_save_handler(\SessionHandlerInterface $handler)
`session_set_save_handler` implementation.

    public static function mime_content_type($file)
Gets a file's MIME (static shell → `_mime_content_type`).

    public function _mime_content_type($file)
MIME implementation: falls back to the built-in table by extension when the `mime_content_type` function is missing; replaceable.

### Protected methods

    protected function system_wrapper_call_check(string $func): bool
Whether a system function goes through override (`__SYSTEM_WRAPPER_REPLACER` or a set handler).

    protected function system_wrapper_call(string $func, array $input_args)
Unified call: REPLACER first → handler → native function (throws `ErrorException` when missing).

    protected function getMimeData(): string
Built-in MIME type table (heredoc), the fallback for `_mime_content_type`.

## Related links

- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) — cookies are sent through this wrapper
- [DuckPhp\Core\App](Core-App.md) — uses the error handler / exit exception
- [DuckPhp\Core\ExitException](Core-ExitException.md) — the `__EXIT_EXCEPTION` semantics (exit turned into an exception)
- guide: testing — isolating headers/sessions in tests
