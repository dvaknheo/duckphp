# DuckPhp\Foundation\Controller\ControllerHelper

## Introduction

`ControllerHelper` is the collection of static helpers aimed at the **Controller (controller) layer** (the methods live in this class itself; there is no Trait any more), and it is the richest of the four Helper layers. It provides every convenience entry a controller needs day to day:

- request input: `GET/POST/REQUEST/COOKIE/SERVER`;
- request-type tests: `IsPost` (is this a POST), `IsAjax` (is it Ajax);
- routing and URLs: `PathInfo/Url/Res/Domain/Parameter/getRouteCallingClass/getRouteCallingMethod`;
- output: `Show/Render/Show302/Show404/ShowJson`;
- replaceable system functions: `header/setcookie/exit`;
- exception-handler registration: `assignExceptionHandler/setMultiExceptionHandler/setDefaultExceptionHandler/ControllerThrowOn`;
- pagination: `Pager/PageNo/PageWindow/PageHtml`;
- configuration/settings: `Setting/AppOptions/Config`;
- events: `FireGlobalEvent/OnGlobalEvent`;
- user/administrator: the `Admin*`/`User*` families.

This class carries 10 action-level event-name constants and 8 exception-code/message constants of its own, all aliases of the matching `User`/`Admin` constants.

## Class info

- Namespace: `DuckPhp\Foundation\Controller`
- Declaration: `class ControllerHelper`
- Traits used: `DuckPhp\Core\SingletonExTrait`
- Event-name constants (`User` aliases): `EVENT_ACTION_USER_REGISTERING` / `EVENT_ACTION_USER_REGISTERED` / `EVENT_ACTION_USER_LOGINING` / `EVENT_ACTION_USER_LOGINED` / `EVENT_ACTION_USER_LOGOUTING` / `EVENT_ACTION_USER_LOGOUTED`
- Event-name constants (`Admin` aliases): `EVENT_ACTION_ADMIN_LOGINING` / `EVENT_ACTION_ADMIN_LOGINED` / `EVENT_ACTION_ADMIN_LOGOUTING` / `EVENT_ACTION_ADMIN_LOGOUTED`
- Exception-code/message constants (`User` aliases): `EXCEPTION_CODE_USER_NEED_LOGIN` / `EXCEPTION_MESSAGE_USER_NEED_LOGIN` / `EXCEPTION_CODE_USER_NEED_PERMISSION` / `EXCEPTION_MESSAGE_USER_NEED_PERMISSION`
- Exception-code/message constants (`Admin` aliases): `EXCEPTION_CODE_ADMIN_NEED_LOGIN` / `EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN` / `EXCEPTION_CODE_ADMIN_NEED_PERMISSION` / `EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION`

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\ControllerHelper;

// project convention: this layer's Helper is a thin shell, making it easy to add this project's own conveniences
class Helper extends ControllerHelper
{
}

// inside a Controller (the Helper shares the controller's namespace):
public function action_login()
{
    $name = Helper::POST('name');
    if (Helper::IsAjax()) {
        Helper::ShowJson(['ok' => true]);
        return;
    }
    Helper::assignViewData('name', $name);
    Helper::Show(get_defined_vars(), 'login');
}
```
## Caveats

- `Show($data, $view)` ends up in `App::_()->_Show()` (including header/footer wrapping and the view-file lookup); `Render` goes through `View::_Render()` (without header/footer).
- `GET/POST/REQUEST/COOKIE/SERVER` all come from `SuperGlobal` and fall back to `$default`.
- `Admin/AdminId/AdminName/User/UserId/UserName` correspond to the action interfaces and login queries of `GlobalAdmin`/`GlobalUser`; `AdminService/UserService` fetch the service.
- `PageHtml($total, $options)` generates the HTML pager bar from `Pager`.
- In a controller, prefer `Show302/Show404` over a direct `exit` (it is more testable); when you must output directly you can `exit()` (through SystemWrapper).
- Every event-name constant and exception-code/message constant of this class is an alias of the matching constant on `GlobalUser\User` / `GlobalAdmin\Admin`: they reuse the framework's existing values with no extra behaviour.

## Methods

### Public methods

    public static function Setting($key = null, $default = null)
Reads an application setting (equivalent to `App::Setting`).

    public static function AppOptions(string $key, $default = null)
Reads one key of the application options.

    public static function XpCall($callback, ...$args)
An exception-wrapping call (forwarding to `CoreHelper::_XpCall`).

    public static function Config($file_basename, $key = null, $default = null)
Reads the content of a configuration file under `config/` (forwarding to `Configer`).

    public static function getRouteCallingClass(): ?string
The controller class the current route hit (`Route` context).

    public static function getRouteCallingMethod(): ?string
The method name the current route hit.

    public static function PathInfo(): ?string
The current PATH_INFO (`Route::PathInfo`).

    public static function Url($url = null)
Generates an in-application URL (`Route::_Url`).

    public static function Domain(bool $use_scheme = false): string
The current domain (`Route::_Domain`).

    public static function Res($url = null)
Generates an asset URL (`Route::_Res`).

    public static function Parameter($key = null, $default = null)
Gets a route parameter (`Route::Parameter`).

    public static function Render($view, $data = null)
Renders a view and returns it (`View::_Render`).

    public static function Show($data = [], $view = '')
Renders the page and outputs it (through `App::_Show`, including header/footer).

    public static function checkInstall(?string $url_install = null)
Redirects to the installation page when not installed (`App::checkInstallToPage`).

    public static function setViewHeaderFooter($header_file = null, $footer_file = null)
Sets the view's header/footer templates (forwarding to `View::setViewHeaderFooter`; the arguments are **view names**, relative to `path_view`).

    public static function assignViewData($key, $value = null)
Assigns a value into the view data (forwarding to `View::assignViewData`).

    public static function IsAjax()
Whether this is an Ajax request (`CoreHelper::IsAjax`).

    public static function Show302($url)
A 302 redirect (`CoreHelper::Show302`).

    public static function Show404()
Outputs the 404 page (`CoreHelper::Show404`).

    public static function ShowJson($ret, $flags = 0)
Outputs JSON (`CoreHelper::ShowJson`).

    public static function header($output, bool $replace = true, int $http_response_code = 0)
Sends an HTTP header (through SystemWrapper).

    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
Writes a Cookie (through SystemWrapper).

    public static function exit($code = 0)
Exits (through SystemWrapper; it can be configured to throw instead).

    public static function assignExceptionHandler($classes, $callback = null)
Registers a handler for specific exception classes (forwarding to `ExceptionManager`).

    public static function setMultiExceptionHandler(array $classes, $callback)
Registers one handler for several exception classes.

    public static function setDefaultExceptionHandler($callback)
Sets the default exception handler.

    public static function ControllerThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
Throws a controller exception when `$flag` is truthy.

    public static function ThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)
A shorthand alias of `ControllerThrowOn` (it goes through controller exceptions too).

    public static function IsPost()
Whether the current request is a POST (reads `REQUEST_METHOD` through `SuperGlobal`).

    public static function GET($key = null, $default = null)
Reads `$_GET` (through `SuperGlobal`).

    public static function POST($key = null, $default = null)
Reads `$_POST`.

    public static function REQUEST($key = null, $default = null)
Reads `$_REQUEST`.

    public static function COOKIE($key = null, $default = null)
Reads `$_COOKIE`.

    public static function SERVER($key = null, $default = null)
Reads `$_SERVER`.

    public static function Pager($new = null)
Gets (or replaces) the pagination component instance.

    public static function PageNo($new_value = null)
Gets/sets the current page number.

    public static function PageWindow($new_value = null)
Gets/sets the pagination window.

    public static function PageHtml($total, $options = [])
Generates the pagination HTML (`Pager::PageHtml`).

    public static function FireGlobalEvent($event, ...$args)
Fires a global event.

    public static function OnGlobalEvent($event, $callback)
Registers a global event listener.

    public static function Admin()
Returns the administrator action interface (`GlobalAdmin::_()`).

    public static function AdminId(bool $check_login = true)
The current administrator ID (handled according to `$check_login` when not logged in).

    public static function AdminName(bool $check_login = true)
The current administrator name.

    public static function AdminService()
The administrator service (`GlobalAdmin::_()->service()`).

    public static function User()
Returns the user action interface (`GlobalUser::_()`).

    public static function UserId(bool $check_login = true)
The current user ID.

    public static function UserName(bool $check_login = true)
The current user name.

    public static function UserService()
The user service (`GlobalUser::_()->service()`).

## Related links

- [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) — the business layer helper (this class also carries its `AdminService/UserService` and so on)
- [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) — the application-level helper
- [DuckPhp\Foundation\Helper](Foundation-Helper.md) — the four-layer union facade (`__callStatic`; this layer is its second lookup target)
