# 4-11 Implementing the user system

> What this solves: making the `Helper::User*()` calls from [Chapter 2-19 Using the user system](user.md) actually have something behind them — you supply three implementations (session / login service / local service) and mount the component into the app.
> Prerequisites: [Chapter 2-19](user.md) (know how the caller uses it first), [Chapter 2-11 Sessions](session.md), [Chapter 4-2 Developing components and extensions](custom-component.md). About 25 minutes.
> Runnable assets: `tests/GlobalUser/GlobalUserTest.php` (`UserTestApp` / `UserTestSession` / `UserTestService`, 58 assertions), `tests/Foundation/Controller/UserControllerBaseTest.php` (12 assertions on the controller side).

## A minimal working wiring

One subclass + three implementations + one block of options, and that is the whole thing:

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\UserSystem\MyUser;        // extends \DuckPhp\GlobalUser\GlobalUser
use MyProj\UserSystem\UserService;   // implements UserServiceInterface + UserLoginServiceInterface
use MyProj\UserSystem\UserSession;   // implements UserSessionInterface

class App extends DuckPhp
{
    public $options = [
        'globaluser_url_home'     => 'user/center',
        'globaluser_url_login'    => 'user/login',
        'globaluser_url_logout'   => 'user/logout',
        'globaluser_url_register' => 'user/register',

        'globaluser_login_session' => [UserSession::class, '_'],   // who is logged in
        'globaluser_login_service' => [UserService::class, '_'],   // register/login/logout
        'globaluser_local_service' => [UserService::class, '_'],   // canAccess/log/batchGetUsernames

        'ext' => [
            MyUser::class => true,
        ],
    ];
}
```

```php
namespace MyProj\UserSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;          // prefixed session access (Chapter 2-11)
    use UserSessionTrait;      // getCurrentUserId/Name, getCurrentUser, setCurrentUser, unsetCurrentUser
}
```

```php
namespace MyProj\UserSystem;

use DuckPhp\GlobalUser\UserLoginServiceInterface;
use DuckPhp\GlobalUser\UserServiceInterface;

class UserService implements UserServiceInterface, UserLoginServiceInterface
{
    public function canAccess($user_id, ?string $url, string $class, string $method): bool
    {
        return $user_id > 0;                       // your permission rules
    }
    public function log($user_id, string $string, ?string $type = null, array $ext = [])
    {
        MyLogModel::_()->add($user_id, $string, $type, $ext);
    }
    public function batchGetUsernames(array $ids): array
    {
        return MyUserModel::_()->namesByIds($ids);   // [id => name]
    }
    public function register(array $post)
    {
        // validate -> persist -> return the user array (it will be written into the session)
        return MyUserModel::_()->create($post);
    }
    public function login(array $post)
    {
        $user = MyUserModel::_()->findByLogin($post['username'], $post['password']);
        return $user ?: [];                          // return the user array; an empty array means the login failed
    }
    public function logout($id)
    {
        // bookkeeping goes here if you need it (clearing the session is the component's job)
    }
}
```

> The set above (app-level `globaluser_*` options + the `ext` mount + three implementations) is measured: `Helper::UserId()` reads the session, `Helper::User()->login()` writes it and 302s to `globaluser_url_home`, `Helper::UserService()` can fetch user names in bulk, and when nobody is logged in `Helper::UserId()` 302s to `globaluser_url_login`.

## 1. The component and "the key": `User` is the key, `GlobalUser` is the implementation

The whole framework (including `Helper::User*()` and [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)) reads the parent class name [`User`](../reference/GlobalUser-User.md) as its container key. `User` itself is a **stub**: with no implementation mounted, every capability call throws `DuckPhpSystemException` outright (`id()`/`name()` throw `No GlobalUser Provider.`, the rest throw `Need Provider`) — it never silently returns an empty value.

[`GlobalUser`](../reference/GlobalUser-GlobalUser.md) is the complete implementation, and its `init()` registers itself under that key through [`PhaseProxy`](../reference/Component-PhaseProxy.md) — which is the whole meaning of "mount it".

The three ways of writing `ext` (see [Chapter 4-2](custom-component.md)):

| Form | Effect |
|---|---|
| `MyUser::class => true` | follow the app options (what the minimal example above uses) |
| `MyUser::class => ['globaluser_url_login' => 'u/login']` | give this component just these options |
| `GlobalUser::class => true` | no subclass; use the framework implementation directly (options still live in the app) |

**You do not need a subclass**: `MyUser` is only "the project's own type", handy for adding methods or writing default values in `$options`; `'ext' => [\DuckPhp\GlobalUser\GlobalUser::class => true]` works just as well.

The app option `user_provider_enable` (default `true`) controls that registration: set it to `false` and nothing is registered, so `Helper::User*()` falls back to the stub (equivalent to "this project has no user system").

## 2. All the options

| Option | Default | Effect |
|---|---|---|
| `globaluser_login_session` | `null` | The session implementation; **required** |
| `globaluser_login_service` | `null` | The login service (register/login/logout); **required** |
| `globaluser_local_service` | `null` | The local Service (`canAccess()`/`log()`/`batchGetUsernames()`); **required** |
| `globaluser_url_home` / `globaluser_url_login` / `globaluser_url_logout` / `globaluser_url_register` | `null` | The site home / login / logout / register URLs (built with `__url()`, defaulting to `'/'`) |
| `globaluser_view_file_header` / `globaluser_view_file_footer` | `null` | The header/footer view files of user pages (see §7) |
| `globaluser_enable_callback_singleton` | `true` | When a callback is written `[ClassName, method]`, swap it for the `ClassName::_()` singleton first; set it to `false` to call it statically |
| `globaluser_ext_view_data_callback` | `null` | A callback that appends view data (optional) |
| `globaluser_need_login_callback` | `null` | Custom handling for "what if not logged in" (optional, see §6) |
| `globaluser_is_authed_redirect` | `true` | Redirect with 302 automatically after a successful register/login/logout |

When one of the three **required** keys is missing it throws `DuckPhpSystemException: need ext options 'globaluser_login_session'`; the two optional keys are tested with `isset()`, so leaving them out merely skips them.

Two more app-level options that are **not in the component's `$options`** but override the component's values (`DuckPhp`'s hidden options):

- `url_user_home`: takes precedence over `globaluser_url_home`;
- `url_user_logout`: takes precedence over `globaluser_url_logout`.

## 3. The session implementation: `UserSessionTrait`

The user system **does not touch `$_SESSION`**; `globaluser_login_session` wants an implementation of [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) — five methods: `getCurrentUserId()` / `getCurrentUserName()` / `getCurrentUser()` / `setCurrentUser($user)` / `unsetCurrentUser()`.

The framework ships [`UserSessionTrait`](../reference/GlobalUser-UserSessionTrait.md), which stores the user under the session key **`user`** (an array with `id`/`name`); paired with [Chapter 2-11](session.md)'s [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) that is exactly the form in the minimal example above. To switch to JWT / Redis / single sign-on, replace this layer — `id()`/`name()` only trust its return values.

> The key names in `$_SESSION` must agree with the caller conventions in [Chapter 2-19 §1](user.md): what `Helper::UserId()` gets is `getCurrentUserId()`'s return value, which is `0` when nobody is logged in (`$user['id'] ?? 0` in `UserSessionTrait`).

## 4. The local service: `UserServiceInterface`

[`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md) has three methods, all taking `$user_id` explicitly as the first argument and **never reading the session**:

| Method | When it is called |
|---|---|
| `canAccess($user_id, ?string $url, string $class, string $method): bool` | `Helper::User()->canAccess()` (the parameter order matches the Action side: `$url` first) |
| `log($user_id, string $string, ?string $type = null, array $ext = [])` | `Helper::User()->log()` |
| `batchGetUsernames(array $ids): array` | `Helper::UserService()->batchGetUsernames()` |

It is also the answer to "how does the business layer get user information" in [Chapter 2-19 §4](user.md): the business layer only has `UserService`, so to use "whose permission / whose name", pass `$userId` in from the controller.

## 5. The login service: `UserLoginServiceInterface`

[`UserLoginServiceInterface`](../reference/GlobalUser-UserLoginServiceInterface.md) does the real validation and persistence; the user side has one more method than the admin side — registration:

| Method | Who calls it | What it returns |
|---|---|---|
| `register(array $post)` | `Helper::User()->register($post)` | The user array (written into the session by `setCurrentUser()`) |
| `login(array $post)` | `Helper::User()->login($post)` | The user array (likewise written into the session) |
| `logout($id)` | `Helper::User()->logout()` (`$id` is the result of `id(false)`, possibly `0` when nobody is logged in) | Nothing |

⚠️ **The component does not judge whether the login succeeded**: it writes `login()`'s return value **verbatim** into the session via `setCurrentUser($user)`, then fires the completion event as usual and 302s according to `globaluser_is_authed_redirect`. So what "wrong user name or password" looks like is decided by **your login service** — most commonly by returning an empty array (the session then has "no user", so the Helpers in [Chapter 2-19 §1](user.md) take the not-logged-in branch), though you can also throw or `Show302` back to the login page yourself.

The component strings together "the order plus the events" (`EVENT_ACTION_USER_REGISTERING` → service → `setCurrentUser()` → `EVENT_ACTION_USER_REGISTERED` → optional 302), so **your login service must not fire those events again**; what it should fire is the `EVENT_SERVICE_USER_*` group ([Chapter 2-13 §3](events.md)).

## 6. Custom handling for "not logged in"

Calls such as `Helper::UserId()` go through `GlobalUser::throwLoginOn()` when nobody is logged in, with three choices (details in [Chapter 2-19 §2](user.md)). The default is "302 to the login page" or "Ajax JSON"; to change it, configure the callback:

```php
'globaluser_need_login_callback' => function () {
    SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'USER_NEED_LOGIN']);
},
// the component calls exit() after the callback returns, so the request ends here
```

## 7. The view-data callback and the header/footer files

- `globaluser_ext_view_data_callback`: called once inside `mergeViewData()` with the current view-data array, and it returns the modified array — use it to add the extra variables you want in the "post-login view";
- `globaluser_view_file_header` / `globaluser_view_file_footer`: the header/footer view files, whose values are resolved by `App::getOverrideableFile('view', …)` — **relative paths land under `<app path>/view/`** (not `path_view`), and they are **phase-overridable**: a third app can replace the user page's header/footer by putting same-named files in its own `view/` ([Chapter 3-5](overriding.md)).

The two switches are view data, not app options: `__use_logined_view_data` (whether to use the post-login view) and `__use_logined_header_footer_file` (whether to wrap the header/footer files); extending `UserControllerBase` sets them automatically.

## 8. How to verify your wiring

The fastest way is to copy the runnable implementation in the repository and run the tests:

```bash
wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/GlobalUser/GlobalUserTest.php"
```

After wiring your own, check these four things (the first three are the caller's view, the fourth is the "not wired" fallback):

1. `Helper::UserId()` returns `0` when nobody is logged in (try `Helper::UserId(false)`), and the `id` you put in the session after logging in;
2. `Helper::UserService()->batchGetUsernames([1, 2])` returns `[id => name]`;
3. after `Helper::User()->login($post)`, `Helper::UserId()` has a value and the response 302s to `globaluser_url_home`;
4. comment out that line in `ext` and `Helper::UserId()` should throw `DuckPhpSystemException: No GlobalUser Provider.` — it must **make a noise**, which is what proves "not wired means not wired" rather than passing silently.

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| ` need ext options 'globaluser_login_session'` | The three required callbacks are not all configured (or a key is misspelled) | Fill them in using the table in §2 |
| `DuckPhpSystemException: No GlobalUser Provider.` | `User::_()` is the stub: `ext` was not mounted, or `user_provider_enable` was turned off | Mount `'ext' => [MyUser::class => true]` |
| The session holds a user but `Helper::UserId()` is still 0 | The session key/prefix does not match `UserSessionTrait` | Use `UserSessionTrait` (key `user`), or make your implementation return the right `id` |
| The header/footer files cannot be found | Their values resolve against `<app path>/view/`, not `path_view` | Use a name relative to `<path>/view/`, or give an absolute path |
| A callback written `[Class::class, 'method']` reports "non-static method" | `globaluser_enable_callback_singleton` was set to `false` | Set it back to `true` (the default), or make the method static |
| The login succeeds but nothing redirects | `globaluser_is_authed_redirect` is off, or the URL options are unset | Turn the option on and set `globaluser_url_home` / `globaluser_url_login` |

## Next steps

- [Chapter 4-12 Implementing the admin system](impl-admin.md): the parallel implementation for the back office (no registration, plus `isSuper()`).
- [Chapter 2-19 Using the user system](user.md): the caller-facing half of this chapter.
- Reference manual: [GlobalUser](../reference/GlobalUser-GlobalUser.md), [User](../reference/GlobalUser-User.md), [UserSessionInterface](../reference/GlobalUser-UserSessionInterface.md), [UserServiceInterface](../reference/GlobalUser-UserServiceInterface.md), [UserLoginServiceInterface](../reference/GlobalUser-UserLoginServiceInterface.md), [UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md)
