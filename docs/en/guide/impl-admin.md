# 4-12 Implementing the admin system

> What this solves: making the `Helper::Admin*()` calls from [Chapter 2-20 Using the admin system](admin.md) actually have something behind them — you supply three implementations (session / login service / local service) and mount the component into the app.
> Prerequisites: [Chapter 2-20](admin.md), [Chapter 4-11 Implementing the user system](impl-user.md) (the two implementations are parallel; this chapter only covers what differs), [Chapter 2-11 Sessions](session.md). About 20 minutes.
> Runnable assets: `tests/GlobalAdmin/GlobalAdminTest.php` (`FakeAdminApp` / `FakeAdminSession` / `FakeAdminLoginService` / `FakeAdminService`), `tests/Foundation/Controller/AdminControllerBaseTest.php`.

## A minimal working wiring

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\AdminSystem\MyAdmin;        // extends \DuckPhp\GlobalAdmin\GlobalAdmin
use MyProj\AdminSystem\AdminService;   // implements AdminServiceInterface + AdminLoginServiceInterface
use MyProj\AdminSystem\AdminSession;   // implements AdminSessionInterface

class App extends DuckPhp
{
    public $options = [
        'globaladmin_url_home'   => 'admin/dashboard',
        'globaladmin_url_login'  => 'admin/login',
        'globaladmin_url_logout' => 'admin/logout',

        'globaladmin_login_session' => [AdminSession::class, '_'],   // who is logged in
        'globaladmin_login_service' => [AdminService::class, '_'],   // login/logout
        'globaladmin_local_service' => [AdminService::class, '_'],   // canAccess/log/isSuper

        'ext' => [
            MyAdmin::class => true,
        ],
    ];
}
```

```php
namespace MyProj\AdminSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\AdminSessionTrait;

class AdminSession implements AdminSessionInterface
{
    use SessionTrait;          // prefixed session access (Chapter 2-11)
    use AdminSessionTrait;     // getCurrentAdminId/Name, getCurrentAdmin, setCurrentAdmin, unsetCurrentAdmin
}
```

```php
namespace MyProj\AdminSystem;

use DuckPhp\GlobalAdmin\AdminLoginServiceInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminService implements AdminServiceInterface, AdminLoginServiceInterface
{
    public function canAccess($admin_id, ?string $url, string $class, string $method): bool
    {
        return MyRoleModel::_()->can($admin_id, $url, $class, $method);   // your permission rules
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        MyAdminLogModel::_()->add($admin_id, $string, $type, $ext);
    }
    public function isSuper($admin_id): bool
    {
        return $admin_id === 1;                     // your super-admin test; the user side has no such method
    }
    public function login(array $post)
    {
        $admin = MyAdminModel::_()->findByLogin($post['username'], $post['password']);
        return $admin ?: [];                        // the component writes the return value into the session verbatim (see §2)
    }
    public function logout($id)
    {
    }
}
```

> The admin side has **no registration**: back-office accounts are created by you (in the install flow, in a CLI command, or straight into the data), so there is neither a `register()` nor a `globaladmin_url_register`.

## 1. What differs from the user system

| Item | User system ([Chapter 4-11](impl-user.md)) | Admin system |
|---|---|---|
| Container key / implementation | `User` / `GlobalUser` | `Admin` / `GlobalAdmin` |
| Option prefix | `globaluser_` | `globaladmin_` |
| Session contract | [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) + `UserSessionTrait` (session key `user`) | [`AdminSessionInterface`](../reference/GlobalAdmin-AdminSessionInterface.md) + `AdminSessionTrait` (session key **`admin`**) |
| Login service | [`UserLoginServiceInterface`](../reference/GlobalUser-UserLoginServiceInterface.md): `register()` / `login()` / `logout()` | [`AdminLoginServiceInterface`](../reference/GlobalAdmin-AdminLoginServiceInterface.md): `login()` / `logout()` |
| Local service | [`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md): `canAccess()` / `log()` / `batchGetUsernames()` | [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md): `canAccess()` / `log()` / **`isSuper()`** |
| URL options | `url_home` / `url_login` / `url_logout` / `url_register` | `url_home` / `url_login` / `url_logout` (no registration) |
| App-level override options | `url_user_home` / `url_user_logout` | `url_admin_home` / `url_admin_logout` |
| Event constants | `User::EVENT_ACTION_USER_*` / `EVENT_SERVICE_USER_*` | `Admin::EVENT_ACTION_ADMIN_*` / `EVENT_SERVICE_ADMIN_*` ([Chapter 2-13 §3](events.md)) |
| Back-office only | — | A controller implementing [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) is scanned into the menu by [`PermissionMenu`](../reference/Ext-PermissionMenu.md) ([Chapter 2-20 §5](admin.md)) |

The error-code constants live on the top-level class too: [`Admin`](../reference/GlobalAdmin-Admin.md)'s `EXCEPTION_CODE_ADMIN_NEED_LOGIN`, `EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION` and so on.

## 2. The `globaladmin_*` options

| Option | Default | Effect |
|---|---|---|
| `globaladmin_login_session` | `null` | The session implementation; **required** |
| `globaladmin_login_service` | `null` | The login service (login/logout); **required** |
| `globaladmin_local_service` | `null` | The local Service (`canAccess()`/`log()`/`isSuper()`); **required** |
| `globaladmin_url_home` / `globaladmin_url_login` / `globaladmin_url_logout` | `null` | Back-office home / login / logout URLs (the App's `url_admin_home`, `url_admin_logout` take precedence over the first two) |
| `globaladmin_view_file_header` / `globaladmin_view_file_footer` | `null` | The header/footer view files of back-office pages (resolution follows the user side: relative to `<app path>/view/`, phase-overridable) |
| `globaladmin_enable_callback_singleton` | `true` | When a callback is written `[ClassName, method]`, it is first swapped for the `ClassName::_()` singleton |
| `globaladmin_ext_view_data_callback` | `null` | A callback that appends view data (optional) |
| `globaladmin_need_login_callback` | `null` | Custom handling for "what if not logged in" (optional) |
| `globaladmin_is_authed_redirect` | `true` | Redirect with 302 automatically after a successful login/logout |

The three ways of writing `ext`, and `admin_provider_enable` (default `true`; turning it off falls back to the stub), are word-for-word the same as the user side — see [Chapter 4-11 §1](impl-user.md).

## 3. The login service: `AdminLoginServiceInterface`

| Method | Who calls it | What it returns |
|---|---|---|
| `login(array $post)` | `Helper::Admin()->login($post)` | The admin array (written into the session by `setCurrentAdmin()`) |
| `logout($id)` | `Helper::Admin()->logout()` (`$id` is the result of `id(false)`) | Nothing |

As on the user side above: **the component does not judge whether the login succeeded** — it writes `login()`'s return value into the session verbatim, then fires the completion event, then redirects with 302 according to `globaladmin_is_authed_redirect`; what "wrong account or password" looks like is your service's decision (returning an empty array is the simplest).

## 4. Back-office specifics: `isSuper()` and `onNeedPermission()`

- `isSuper()`: "is this admin a super admin", which only the admin side has; every call asks your [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md)'s `isSuper($admin_id)` directly — the framework neither caches it nor decides for you. Use it for "super admins only" menus/buttons ([Chapter 2-20 §3](admin.md)).
- `onNeedPermission()`: the **controller's fallback hook**. A controller extending [`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) calls it when `canAccess()` is false; the default implementation is "non-Ajax: 302 to the login page; Ajax: `{"error_code":-1,"error_message":"NEED_PERMISSION"}`". To get behaviour such as "return a 403 JSON" or "log a permission violation", **override this method** in your own controller base class (no arguments; do not change the signature).

```php
namespace MyProj\AdminSystem;

use DuckPhp\Foundation\Controller\AdminControllerBase;

class AdminBaseController extends AdminControllerBase
{
    protected function onNeedPermission()
    {
        MyAuditLog::_()->warn('unauthorised access', ['admin' => Helper::AdminId(false)]);
        parent::onNeedPermission();          // keep the default 302 / JSON behaviour
    }
}
```

## 5. Working with `PermissionMenu`

Menus are a "usage" side capability ([Chapter 2-20 §5](admin.md)); the implementation side only has to remember the contract: **a controller is scanned into the menu only when it implements [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md)** (an empty marker interface). So there are two ways to write a back-office controller:

- extend `AdminControllerBase` (its parent already `implements AdminControllerInterface`);
- or `implements AdminControllerInterface` yourself.

## 6. How to verify your wiring

```bash
wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/GlobalAdmin/GlobalAdminTest.php"
```

The four self-checks are parallel to the user side: `Helper::AdminId(false)` returns `0` when nobody is logged in; `Helper::Admin()->isSuper()` goes through your Service; after `Helper::Admin()->login($post)` `Helper::AdminId()` has a value and it 302s to `globaladmin_url_home`; and commenting out that line in `ext` makes `Helper::AdminId()` throw `DuckPhpSystemException: No GlobalAdmin Provider.`.

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| ` need ext options 'globaladmin_login_session'` | The three required callbacks are not all configured | Fill them in using the table in §2 |
| `DuckPhpSystemException: No GlobalAdmin Provider.` | `Admin::_()` is the stub: `ext` was not mounted, or `admin_provider_enable` was turned off | Mount `'ext' => [MyAdmin::class => true]` |
| The session holds an admin but `Helper::AdminId()` is still 0 | The session key/prefix does not match `AdminSessionTrait` | Use `AdminSessionTrait` (key `admin`), or make your implementation return the right `id` |
| The back-office menu does not pick up your controller | The controller does not implement `AdminControllerInterface` | Extend `AdminControllerBase`, or `implements` it explicitly |
| An unauthorised request still gets the default 302/JSON | When overriding `onNeedPermission()` you forgot `parent::`, or it did not take effect | Override it in **your own controller base class** (not inside a controller action) |

## Next steps

- [Chapter 4-11 Implementing the user system](impl-user.md): the parallel implementation for the front end.
- [Chapter 2-20 Using the admin system](admin.md): the caller-facing half of this chapter.
- Reference manual: [GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md), [Admin](../reference/GlobalAdmin-Admin.md), [AdminLoginActionInterface](../reference/GlobalAdmin-AdminLoginActionInterface.md), [AdminSessionInterface](../reference/GlobalAdmin-AdminSessionInterface.md), [AdminServiceInterface](../reference/GlobalAdmin-AdminServiceInterface.md), [AdminLoginServiceInterface](../reference/GlobalAdmin-AdminLoginServiceInterface.md), [AdminSessionTrait](../reference/GlobalAdmin-AdminSessionTrait.md), [Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)
