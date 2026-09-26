# DuckPhp\Foundation\System\SystemHelper

## Introduction

`SystemHelper` is the collection of static helpers for the **application/wiring layer (System)** (the methods live in this class itself; there is no Trait any more). It gathers the common forwards that are "framework-level but not exclusive to one layer": exception handling, global events, running state, route hooks and maps, session/request state (`SuperGlobal`), the replaceable system functions (`SystemWrapper`: `header`/`setcookie`/`exit`/`session_*` and so on), DB/Redis, CLI parameters, saving extension options, and more.

Business code usually does not use it directly; it is one of the four layer Helpers, and a project may also have its own `System` layer/application base class `extends` it. Note that the `__callStatic` dispatch of `Foundation\Helper` and `DuckPhpAllInOne` looks for a method **here first**, so who wins among duplicate names is influenced by it (see [DuckPhp\Foundation\Helper](Foundation-Helper.md)).

## Class info

- Namespace: `DuckPhp\Foundation\System`
- Declaration: `class SystemHelper`
- Traits used: `DuckPhp\Core\SingletonExTrait`

## Usage

```php
namespace MyProject\System;

use DuckPhp\Foundation\System\SystemHelper as Helper;   // the aliased form, as with every layer Helper in a project

// 1) use it directly as the facade (at the application's wiring points, such as App::onInited())
$map = Helper::getRouteMaps();
Helper::addRouteHook($cb, 'prepend-inner');
Helper::header('Content-Type: application/json');

// 2) wrap it in your own System Helper (recommended: makes it easy to add this project's methods)
class SysHelper extends Helper
{
}
```
## Caveats

- Most methods are **pure forwards**: the "lower component" behind the same capability is described in the matching component's documentation (`ExceptionManager`/`Runtime`/`Route`/`RouteHookRouteMap`/`RouteHookRewrite`/`SuperGlobal`/`SystemWrapper`/`DbManager`/`RedisManager`/`Console`/`GlobalEvent`/`ExtOptionsLoader`/`View`).
- `header`/`setcookie`/`exit`/`session_*`/`set_exception_handler`/`register_shutdown_function`/`mime_content_type` go through `SystemWrapper`, that is, they are **replaceable system functions** (a test or long-running scenario can inject an implementation).
- This class does not contain the `Controller` layer's `GET/POST/Show/Url` and similar methods; those belong to [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md).

## Methods

### Public methods

    public static function CallException(\Throwable $ex)
Hands the exception to the exception manager (`ExceptionManager::CallException`).

    public static function RemoveEvent($event, $callback = null)
Removes a global event listener (forwarding to `GlobalEvent::remove`).

    public static function isRunning(): bool
Whether the framework is running (`Runtime` state).

    public static function isInException(): bool
Whether it is currently inside the exception-handling flow (`Runtime` state).

    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
Registers a route hook (`Route::addRouteHook`; for the positions see Core-Route).

    public static function replaceController(string $old_class, string $new_class)
Replaces one controller class in routing (`Route::replaceController`).

    public static function getViewData(): array
Gets the view data (`View::getViewData`).

    public static function DbCloseAll()
Closes every database connection (`DbManager`).

    public static function SESSION($key = null, $default = null)
Reads session variables (forwarding to `SuperGlobal::_SESSION`).

    public static function FILES($key = null, $default = null)
Reads uploaded-file variables (forwarding to `SuperGlobal::_FILES`).

    public static function SessionSet($key, $value)
Writes a session variable.

    public static function SessionUnset($key)
Deletes a session variable.

    public static function SessionGet($key, $default = null)
Reads one session variable.

    public static function CookieSet($key, $value, $expire = 0)
Writes a Cookie (forwarding to `SuperGlobal::_CookieSet`).

    public static function CookieGet($key, $default = null)
Reads a Cookie (forwarding to `SuperGlobal::_CookieGet`).

    public static function system_wrapper_replace(array $funcs)
Injects replaceable system-function implementations (forwarding to `SystemWrapper::_system_wrapper_replace`).

    public static function system_wrapper_get_providers(): array
Gets the current system-function provider table.

    public static function header($output, bool $replace = true, int $http_response_code = 0)
Sends an HTTP header (through SystemWrapper, replaceable).

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
Writes a Cookie (through SystemWrapper, replaceable).

    public static function exit($code = 0)
Exits (through SystemWrapper; the framework can be configured to throw `ExitException` so it can be caught).

    public static function set_exception_handler(callable $exception_handler)
Registers exception handling (through SystemWrapper).

    public static function register_shutdown_function(callable $callback, ...$args)
Registers a shutdown callback (through SystemWrapper).

    public static function session_start(array $options = [])
Starts the session (through SystemWrapper; `$options` is passed to the native `session_start`).

    public static function session_id($session_id = null)
Gets/sets the session ID (through SystemWrapper).

    public static function session_destroy()
Destroys the session (through SystemWrapper).

    public static function session_set_save_handler(\SessionHandlerInterface $handler)
Sets the session save handler (through SystemWrapper).

    public static function mime_content_type($file)
Gets a file's MIME type (through SystemWrapper).

    public static function setBeforeGetDbHandler($db_before_get_object_handler)
Sets the "before fetching a database" callback (forwarding to `DbManager::setBeforeGetDbHandler`).

    public static function Redis($tag = 0)
Gets a Redis client (`RedisManager::Redis($tag)`).

    public static function getRouteMaps()
Gets the route map table (forwarding to `RouteHookRouteMap::getRouteMaps`).

    public static function assignRoute($key, $value = null)
Registers a route map entry (forwarding to `RouteHookRouteMap::assignRoute`).

    public static function assignImportantRoute($key, $value = null)
Registers an important route map entry (it cannot be overridden).

    public static function assignRewrite($key, $value = null)
Registers a URL rewrite rule (forwarding to `RouteHookRewrite::assignRewrite`).

    public static function getRewrites()
Gets the current rewrite rule table.

    public static function getCliParameters()
Gets the parameters parsed from the CLI (forwarding to `Console::getCliParameters`).

    public static function FireGlobalEvent($event, ...$args)
Fires a global event (forwarding to `GlobalEvent::fire`).

    public static function OnGlobalEvent($event, $callback)
Registers a global event listener (forwarding to `GlobalEvent::on`).

    public static function saveExtOptions(array $options): void
Writes extension options (forwarding to `ExtOptionsLoader::saveExtOptions`).

    public static function ProjectThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
Throws a "project exception" (forwarding to `CoreHelper::_ProjectThrowOn`; the exception class comes from `exception_for_project` / `exception_map`).

    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
A shorthand alias of `ProjectThrowOn` (it goes through project exceptions too).

## Related links

- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — the controller layer helper
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — the four-layer union facade (`__callStatic`)
- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — the business layer helper
- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — the entry class that composes the four Helper layers with `__callStatic`
