# DuckPhp\Foundation\Controller\UserControllerBase

## Introduction

`UserControllerBase` is the recommended base class for a project's "front-end logged-in user controller": it `implements UserControllerInterface` as a marker, and it runs the login/permission check automatically in the constructor (`initController()`):

1. `Helper::checkInstall(null)`: if not installed, jump to the install page;
2. `Helper::User()->id(true)`: make sure someone is logged in;
3. `Helper::User()->canAccess()`: decide whether the current user may access the current route;
4. when not allowed: a non-Ajax request `Show302`s to the login page and calls `exit()`; an Ajax request gets the JSON `{error_code: -2, error_message: 'NEED_PERMISSION'}`.

Its structure is exactly parallel to `AdminControllerBase`, only aimed at `GlobalUser`.

## Class info

- Namespace: `DuckPhp\Foundation\Controller`
- Declaration: `class UserControllerBase implements UserControllerInterface`
- Implements: `DuckPhp\GlobalUser\UserControllerInterface`

## Usage

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\UserControllerBase;

class CenterController extends UserControllerBase
{
    public function action_index()
    {
        // reaching this point means the front-end login and permission checks passed
    }
}
```

## Caveats

- It depends on `GlobalUser` having a correctly configured provider.
- The Ajax branch of `onNeedPermission()` hard-codes `error_code => -2` and `error_message => 'NEED_PERMISSION'`; the non-Ajax branch takes `PHP_URL_PATH` of `ControllerHelper::SERVER('REQUEST_URI','')` as `url_back` and passes it to `urlForLogin()`.

## Methods

### Public methods

    public function __construct()
Runs `initController()` (login/permission check) automatically on construction.

### Protected methods

    protected function onNeedPermission()
The unified hook for "no permission": non-Ajax jumps to the login page, Ajax returns the `error_code:-2` JSON.

    protected function initController()
Install check → forced login → permission check: when not allowed it calls `onNeedPermission()` and then `exit()`.

## Related links

- [DuckPhp\GlobalUser\User](GlobalUser-GlobalUser.md) — the permission and login provider
- [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) — the marker interface
- [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) — the static helpers used internally
