# 2-12 Exceptions and Error Handling

> What this solves: how to layer exceptions, how to write conditional throws, how to configure error pages, and how to hook up an exception reporter.
> Prerequisites: [Chapter 2-5 Controllers](controllers.md), [Chapter 2-10 Forms and Validation](validator.md). About 20 minutes.
> Examples: `demo/src/Controller/ExceptionAction.php` (the reporter skeleton), `demo/view/_sys/error_404.php` / `error_500.php` / `error_maintain.php` (error views).

## Minimal example

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use Exception;

class ProjectException extends Exception {}
class BusinessException extends ProjectException {}
class ControllerException extends ProjectException {}
```

```php
// Business layer
Helper::BusinessThrowOn($balance < $amount, '余额不足', 2001);
// equivalent to: BusinessException::ThrowOn(...) (for adding a static guard on a custom exception class see Chapter 4-13 §16)
```

The exception takes a tour through the framework's exception manager ([DuckPhp\Core\ExceptionManager](../reference/Core-ExceptionManager.md)): a registered handler matches → it runs; otherwise it goes to the default exit `_OnDefaultException()` for a 500 page or debug details.

## How it works

### Exception layering: one iron rule

> ⚠️ **[`DuckPhpSystemException`](../reference/Core-DuckPhpSystemException.md) means only "the framework itself has a problem"** (duplicate phase names, directly init'ing a base class, missing provider, etc.); a project's business/permission/login exceptions should **directly `extends \Exception`**. You don't need to inherit it for guard-style throwing either — use `Helper::ThrowOn($flag, 'msg', $code, MyException::class)` (`use Ext\ThrowOnTrait` on the exception class is the old style; see [Chapter 4-13](ext-classes.md) §16). Details in Core-DuckPhpSystemException.

```
\Exception                              ← PHP 内置
  ├─ DuckPhp\Core\DuckPhpSystemException   ← 框架内部专用（工程不要继承）
  │     └─ DuckPhp\Core\ExitException      ← exit 语义（__EXIT_EXCEPTION）
  └─ MyProj\System\ProjectException        ← 工程异常基类（直接继承 \Exception）
        ├─ MyProj\System\BusinessException
        └─ MyProj\System\ControllerException
```

The framework's own **login and permission** scenarios don't use exception classes: error codes/messages are constants on [`User`](../reference/GlobalUser-User.md) / [`Admin`](../reference/GlobalAdmin-Admin.md) (`User::EXCEPTION_CODE_USER_NEED_LOGIN`, `Admin::EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION`, etc.), and the handling is "not logged in → `throwLoginOn()`" / "no permission → the controller's `onNeedPermission()`", both of which pick one of **custom callback / 302 to the login page / JSON for Ajax** and then `exit()` (details in [Chapter 2-19](../guide/user.md) section 5 and [Chapter 2-20](../guide/admin.md) sections 3–4). So the project side doesn't need to, and shouldn't, create exception classes for login/permission.

The [`ExitException`](../reference/Core-ExitException.md) on that tree is **internal to the framework**: with the `use_exit_exception` option on, [`SystemWrapper::exit()`](../reference/Core-SystemWrapper.md) throws it (`src/Core/SystemWrapper.php` lines 167-168), and [`ExceptionManager`](../reference/Core-ExceptionManager.md) lets it through unchanged (`src/Core/ExceptionManager.php` line 90) — business code must not throw it.

### Conditional throw: the ThrowOn family

**Prefer the Helper-side conditional throw** ([CoreHelper](../reference/Core-CoreHelper.md)) — it throws when `$flag` is true, and the exception class is decided centrally by application options:

| Form | Who decides the thrown exception class |
| --- | --- |
| `Helper::ThrowOn($flag, 'msg', $code)` | Depends on which layer's Helper: System layer = Project version; Business / Controller layers each use their own layer's option |
| `Helper::ProjectThrowOn($flag, 'msg', $code)` | The option `exception_for_project` (defaults to `\Exception`) |
| `Helper::BusinessThrowOn($flag, 'msg', $code)` | The option `exception_for_business` (defaults to `\Exception`) |
| `Helper::ControllerThrowOn($flag, 'msg', $code)` | The option `exception_for_controller` (defaults to `\Exception`) |
| `Helper::ThrowOn($flag, 'msg', $code, MyException::class)` | The 4th parameter names the exception class directly |

> ⚠️ The pattern of "`use DuckPhp\Ext\ThrowOnTrait` on an exception class, then `MyException::ThrowOn(...)`" is **no longer recommended** ([Chapter 4-13](ext-classes.md) §16 explains why): conditional throws always go through Helper — the exception class is decided centrally by options, and tests can swap the whole family.

`demo/src/Controller/ExceptionAction.php` is the skeleton:

```php
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\ExceptionReporterTrait;
use DuckPhp\Foundation\SingletonTrait;

class ExceptionAction
{
    use SingletonTrait;            // provides _(): OnException() relies on it to get the instance
    use ExceptionReporterTrait;

    public function onBusinessException($ex)
    {
        // log / output JSON / 302 …
    }
    public static function onControllerException($ex)
    {
        // static methods work too
    }
}
```

Both traits are required: `ExceptionReporterTrait::OnException()` is written as `static::_()->_OnException($ex)`, so the reporter class must bring its own `_()` — use [`DuckPhp\Foundation\SingletonTrait`](../reference/Foundation-SingletonTrait.md) (the skeleton's `Controller\Base` is provided the same way), or make the reporter extend your `Base`. **With only `use ExceptionReporterTrait`, you get `Call to undefined method …::_()` at the moment an exception actually fires** — invisible at startup, because then only `is_callable()` is checked.

The mapping: `BusinessException` → `onBusinessException()`, `ControllerException` → `onControllerException()`, `ProjectException` → `onProjectException()`; no matching method (or the exception happens to be one of the two reporter method names) → `App::_()->_OnDefaultException()`.

### Exception-safe wrapper: `Ext\ExceptionWrapper` (**not recommended**)

⚠️ The framework has no internal users of it left; don't use it in new code:

- "This one call shouldn't blow up the whole flow" → use `Helper::XpCall($cb, ...$args)` (the exception comes back as a return value);
- "You really need to handle exceptions" → plain `try/catch`; don't mix exceptions into normal return values.

Old code already using it may keep using it — its mechanism (catches only `\Exception`, not `\Error`) and examples are in [Chapter 4-13](ext-classes.md) §12.

## Common patterns

### 1. Configure the exception system

```php
// MyProj\System\App
public $options = [
    'exception_for_project'    => ProjectException::class,
    'exception_for_business'   => BusinessException::class,
    'exception_for_controller' => ControllerException::class,
    'exception_reporter'       => [ExceptionAction::class, 'OnException'],

    'error_404'      => '_sys/error_404',
    'error_500'      => '_sys/error_500',
    'error_debug'    => '_sys/error-debug',
    'error_maintain' => '_sys/error_maintain',
];
```

### 2. Conditional throw per layer

```php
// Controller
Helper::ControllerThrowOn(!$user, '请先登录', 403);
// Business
Helper::BusinessThrowOn(!password_verify($password, $user['password']), '密码错误', 1002);
```

### 3. Override the default exit in App

```php
public function _OnDefaultException($ex): void
{
    Logger::_()->error($ex->getMessage());
    // send notifications, record metrics …
    parent::_OnDefaultException($ex);
}
```

### 4. Register handlers with ExceptionManager

```php
use DuckPhp\Core\ExceptionManager;

ExceptionManager::_()->assignExceptionHandler(ValidationException::class, function ($ex) {
    Helper::ShowJson(['errors' => $ex->errors]);
});
ExceptionManager::_()->setMultiExceptionHandler(
    [BusinessException::class, ControllerException::class],
    function ($ex) { /* 统一处理 */ }
);
ExceptionManager::_()->setDefaultExceptionHandler(function ($ex) { /* 兜底 */ });
```

### 5. Take over PHP errors (development)

`ExceptionManager` options (all on by default): `handle_all_dev_error` (Notice/Deprecated go through `_OnDevErrorHandler()`, other levels become `\ErrorException`), `handle_all_exception` (uncaught exceptions enter `_CallException()`). Production usually keeps the defaults; if you only want to turn off error takeover, set `handle_all_dev_error` to `false`.

## Common errors

| Symptom | Cause | Fix |
| --- | --- | --- |
| A business exception extends `DuckPhpSystemException` | Mixing "the framework is broken" with "the business went wrong" | Change to `extends \Exception`; switch conditional throws to `Helper::ThrowOn()` (`use ThrowOnTrait` is no longer recommended) |
| Configured `exception_reporter` but startup throws `config error` | The value is not **callable**: a bare class name (even with a static `OnException()`) is not a callable in PHP (the entry class checks `is_callable()`, source `src/DuckPhp.php` lines 140–146) | Write `[ReporterClass::class, 'OnException']` (the static entry the trait gives), or use a closure / callable object |
| The reporter method isn't hit | The method name is not `on{exception class short name}` | Check the spelling rule of [`ExceptionReporterTrait::OnException()`](../reference/Foundation-Controller-ExceptionReporterTrait.md) |
| The 404 page doesn't appear, only placeholder text | `error_404` isn't configured, or the app 404s before init finishes | Set `'error_404' => '_sys/error_404'`; errors before init completes only produce placeholders |
| Exception details leaked in production | `is_debug` is true, or the view doesn't guard with `__is_debug()` | Set it to `false` when going live; wrap debug blocks with `__is_debug()` in error views |
| `IsDebug()` is inexplicably true | `duckphp_is_debug` is true in the root app or in Setting | Use `IsHiddenDebug()` or check root/settings |
| The maintenance page doesn't take effect | Only `error_maintain` was set, not `is_maintain` | Also set `'is_maintain' => true` or the Setting `duckphp_is_maintain` |
| [`ExceptionWrapper`](../reference/Ext-ExceptionWrapper.md) (not recommended) didn't catch an `\Error` | It only catches `\Exception` | An `\Error` is a programming error and should be thrown out and fixed; see above for why it isn't recommended |

## Next steps

- [Chapter 2-13 The Event System](events.md): events can be attached before/after login/logout and exceptions.
- [Chapter 2-2 The Request Lifecycle](lifecycle.md): where in the request timeline an exception occurs.
- [Chapter 1-6 Debugging, Logging and a First Taste of the CLI](debugging.md): an introduction to `is_debug` and log levels.
- The reference manual: [Core-ExceptionManager](../reference/Core-ExceptionManager.md), [Core-App](../reference/Core-App.md), [Core-ExitException](../reference/Core-ExitException.md), [Foundation-ExceptionReporterTrait](../reference/Foundation-Controller-ExceptionReporterTrait.md); `Ext\ExceptionWrapper` / `Ext\ThrowOnTrait` see [Chapter 4-13](ext-classes.md)
