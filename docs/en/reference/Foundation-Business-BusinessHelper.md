# DuckPhp\Foundation\Business\BusinessHelper

## Introduction

`BusinessHelper` is a collection of static helpers aimed at the **Business (business) layer** (the methods live in this class itself; there is no Trait any more). Through it the business layer reaches: application settings and paths (`Setting`/`AppOptions`/`PathOfProject`/`PathOfRuntime`), configuration (`Config`), caching (`Cache`), validation (`Validator*`), events (`FireGlobalEvent`/`OnGlobalEvent`), the user/administrator services (`AdminService`/`UserService`) and quick throwing of business exceptions (`BusinessThrowOn`).

This class carries 10 event-name constants and 8 exception-code/message constants of its own, all aliases of the matching `User`/`Admin` constants.

## Class info

- Namespace: `DuckPhp\Foundation\Business`
- Declaration: `class BusinessHelper`
- Traits used: `DuckPhp\Core\SingletonExTrait`
- Event-name constants (`User` aliases): `EVENT_SERVICE_USER_REGISTERING` / `EVENT_SERVICE_USER_REGISTERED` / `EVENT_SERVICE_USER_LOGINING` / `EVENT_SERVICE_USER_LOGINED` / `EVENT_SERVICE_USER_LOGOUTING` / `EVENT_SERVICE_USER_LOGOUTED`
- Event-name constants (`Admin` aliases): `EVENT_SERVICE_ADMIN_LOGINING` / `EVENT_SERVICE_ADMIN_LOGINED` / `EVENT_SERVICE_ADMIN_LOGOUTING` / `EVENT_SERVICE_ADMIN_LOGOUTED`
- Exception-code/message constants (`User` aliases): `EXCEPTION_CODE_USER_NEED_LOGIN` / `EXCEPTION_MESSAGE_USER_NEED_LOGIN` / `EXCEPTION_CODE_USER_NEED_PERMISSION` / `EXCEPTION_MESSAGE_USER_NEED_PERMISSION`
- Exception-code/message constants (`Admin` aliases): `EXCEPTION_CODE_ADMIN_NEED_LOGIN` / `EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN` / `EXCEPTION_CODE_ADMIN_NEED_PERMISSION` / `EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION`

## Usage

```php
namespace MyProject\Business;

use DuckPhp\Foundation\Business\BusinessHelper;

class Helper extends BusinessHelper
{
}

// inside Business:
$conf = Helper::Config('database', 'host');
$rows = Helper::Cache()->get('k');          // fetch from cache
$ok   = Helper::XpCall([$obj, 'method']);   // exception-wrapping call
$errs = Helper::ValidatorValid($_POST, ['age' => 'int|min:1']);
Helper::BusinessThrowOn(!$flag, 'business rule forbids this', 10001);
```
## Caveats

- `Setting`/`Options` read the App's settings and options; `PathOfProject`/`PathOfRuntime` come from the App's project/runtime paths.
- The three `Validator*` methods follow the `filter`/`check`/`valid` styles respectively: `ValidatorFilter` returns the filtered data, `ValidatorCheck` throws on failure, and `ValidatorValid` returns an array of errors.
- `AdminService`/`UserService` fetch the service of `GlobalAdmin`/`GlobalUser` respectively, for the business layer to call authentication services.
- Every event-name constant and exception-code/message constant of this class is an alias of the matching constant on `GlobalUser\User` / `GlobalAdmin\Admin`: they reuse the framework's existing values with no extra behaviour.

## Methods

### Public methods

    public static function Setting($key = null, $default = null)
Reads an application setting (equivalent to `App::_()->_Setting()`).

    public static function AppOptions(string $key, $default = null)
Reads one key of the application options (returns `$default` when it is not set).

    public static function Config($file_basename, $key = null, $default = null)
Reads the content of a configuration file under `config/` (through `Configer`).

    public static function XpCall($callback, ...$args)
Calls the callback in the "exception-wrapping" style and passes the result through (through `CoreHelper`, so business exceptions can be caught uniformly higher up).

    public static function BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
Throws a business exception when `$flag` is truthy (the default business exception class, or `$exception_class` when given).

    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
A shorthand alias of `BusinessThrowOn` (it goes through business exceptions too).

    public static function Cache($object = null)
Gets (or replaces) the cache component instance.

    public static function PathOfProject(): string
The project root path (`App::getProjectPath()`).

    public static function PathOfRuntime(): string
The runtime directory path (`App::getRuntimePath()`).

    public static function FireGlobalEvent($event, ...$args)
Fires a global event (forwarding to `GlobalEvent::fire`).

    public static function OnGlobalEvent($event, $callback)
Registers a global event listener (forwarding to `GlobalEvent::on`).

    public static function AdminService()
Returns the administrator service (`GlobalAdmin::_()->service()`).

    public static function UserService()
Returns the user service (`GlobalUser::_()->service()`).

    public static function Validator($new = null)
Gets (or replaces) the `Validator` component instance.

    public static function ValidatorFilter($data, $rules, $messages = [])
Validates by the rules and returns the filtered data (throwing on failure; equivalent to the filter style).

    public static function ValidatorCheck($data, $rules, $messages = [])
Validates by the rules and throws on failure (the check style).

    public static function ValidatorValid($data, $rules, $messages = [])
Validates by the rules and returns the array of errors (the valid style; an empty array when there are none).

## Related links

- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — the controller layer helper (with the controller-side `AdminService`/`UserService`)
- [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) — the data layer helper
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — the four-layer union facade (`__callStatic`; this layer is its third lookup target)
