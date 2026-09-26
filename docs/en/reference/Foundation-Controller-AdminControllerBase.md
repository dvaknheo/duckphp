# DuckPhp\Foundation\Controller\AdminControllerBase

## Introduction

`AdminControllerBase` is the recommended base class for a project's "back-office admin controller": it `implements AdminControllerInterface` as a marker, and it runs the login/permission check automatically in the constructor (`initController()`):

1. `Helper::checkInstall(null)`: if not installed, jump to the install page;
2. `Helper::Admin()->id(true)`: make sure someone is logged in;
3. `Helper::Admin()->canAccess()`: decide whether the current admin may access the current route;
4. when not allowed: a non-Ajax request `Show302`s to the login page and calls `exit()`; an Ajax request gets the JSON `{error_code: -1, error_message: 'NEED_PERMISSION'}`.

So a controller extending it has already gone through the "is this the back office / is someone logged in / is it permitted" fallback before any action runs.

## Class info

- Namespace: `DuckPhp\Foundation\Controller`
- Declaration: `class AdminControllerBase implements AdminControllerInterface`
- Implements: `DuckPhp\GlobalAdmin\AdminControllerInterface`

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\AdminControllerBase;

class DashboardController extends AdminControllerBase
{
    public function action_index()
    {
        // reaching this point means the back-office login and permission checks passed
    }
}
```

## Caveats

- The permission check depends on `GlobalAdmin` being configured correctly (`Admin()`/`canAccess()`/`urlForLogin()` all need a provider).
- The Ajax branch of `onNeedPermission()` hard-codes `error_code => -1` and `error_message => 'NEED_PERMISSION'`; the non-Ajax branch takes `PHP_URL_PATH` of `ControllerHelper::SERVER('REQUEST_URI','')` as `url_back` and passes it to `urlForLogin()`.
- If your controller needs finer-grained permissions, call `Helper::Admin()->canAccess($class, $method)` again inside the action.

## Methods

### Public methods

    public function __construct()
Runs `initController()` (login/permission check) automatically on construction.

### Protected methods

    protected function onNeedPermission()
The unified hook for "no permission": non-Ajax jumps to the login page, Ajax returns the `error_code:-1` JSON.

    protected function initController()
Install check → forced login → permission check: when not allowed it calls `onNeedPermission()` and then `exit()`.

## Related links

- [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-GlobalAdmin.md) — the permission and login provider
- [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) — the marker interface
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — the static helpers used internally
