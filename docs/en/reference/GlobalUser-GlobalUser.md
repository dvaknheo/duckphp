# DuckPhp\GlobalUser\GlobalUser

## Introduction

`GlobalUser` is the **complete implementation** of the user system: it extends `User` (the constants and the "unavailable by default" stub implementations live in the parent), implements `UserLoginActionInterface`, and concentrates "who the current user is / the in-site URLs / the user header and footer / permissions and logging / registration, login and logout" into one component.

It itself **neither touches the database nor touches `$_SESSION`**; instead it outsources three things (all through option callbacks):

1. **The session** (`globaluser_login_session` → `UserSessionInterface`): the sole data source of `id()/name()/data()`;
2. **The service** (`globaluser_local_service` → `UserServiceInterface`): where `canAccess()` / `log()` / `batchGetUsernames()` go;
3. **The login service** (`globaluser_login_service` → `UserLoginServiceInterface`): the business implementation of `register()` / `login()` / `logout()`.

At `init()` it wraps itself in a `PhaseProxy` and registers it under the container key `User::class`, the parent's name (this can be switched off, see `user_provider_enable`), so the whole framework (including `Foundation\Controller\ControllerHelper::User()` and `Foundation\Controller\UserControllerBase`) reaches it through `User::_()`.

## Class info

- Namespace: `DuckPhp\GlobalUser`
- Declaration: `class GlobalUser extends DuckPhp\GlobalUser\User implements UserActionInterface, UserLoginActionInterface`
- Parent: `DuckPhp\GlobalUser\User` (which provides the login fields of `mergeViewData()`, the delegation of `service()`/`log()`/`batchGetUsernames()`, and every constant)
- Interfaces implemented: `UserActionInterface`, `UserLoginActionInterface`
- Traits used: none
- Constants: **this class declares none**; they all come from `User` (6 `EVENT_ACTION_USER_*`, 6 `EVENT_SERVICE_USER_*`, 4 `EXCEPTION_*`; see [User](GlobalUser-User.md))

## Options

| Option | Default | Meaning |
|---|---|---|
| `globaluser_is_authed_redirect` | `true` | Whether to 302 automatically once `register()`/`login()`/`logout()` finishes (registration/login go to `urlForHome()`, logout to `urlForLogin()`). |
| `globaluser_url_home` | `null` | The in-site home URL; when unset it takes the App's context option `url_user_home`, then falls back to `'/'`. |
| `globaluser_url_register` | `null` | The registration page URL; when unset it falls back to `'/'`. |
| `globaluser_url_login` | `null` | The login page URL; when unset it falls back to `'/'` (this is the 302 target of `throwLoginOn()`). |
| `globaluser_url_logout` | `null` | The logout URL; when unset it takes the App's context option `url_user_logout`, then falls back to `'/'`. |
| `globaluser_view_file_header` | `null` | The user header view file (rendered only when non-empty; the result is merged into `__view_data.header`). |
| `globaluser_view_file_footer` | `null` | The user footer view file (rendered only when non-empty; the result is merged into `__view_data.footer`). |
| `globaluser_enable_callback_singleton` | `true` | When a callback is `[class name, method]`, whether to replace the class name with the `ClassName::_()` singleton instance first. |
| `globaluser_local_service` | `null` | The callback returning the `UserServiceInterface` implementation (with the key missing, `localService()` throws). |
| `globaluser_login_service` | `null` | The callback returning the `UserLoginServiceInterface` implementation. |
| `globaluser_login_session` | `null` | The callback returning the `UserSessionInterface` implementation. |
| `globaluser_ext_view_data_callback` | `null` | The callback appending view data (with the key missing it **does not throw**; it is simply skipped, unlike the three "required" callbacks above). |
| `globaluser_need_login_callback` | `null` | Custom handling when not logged in; once configured it **does not** go through the default 302/JSON, only the callback and then `exit()`. |

## Usage

```php
// hang the three things on the application options (mounting only some is fine too; unused keys do not throw)
$options = [
    'ext' => [
        \DuckPhp\GlobalUser\GlobalUser::class => true,
    ],
    'globaluser_login_session' => [\MyProject\User\UserSession::class, '_'],
    'globaluser_local_service' => [\MyProject\User\UserService::class, '_'],
    'globaluser_login_service' => [\MyProject\User\UserLoginService::class, '_'],
    'url_user_home'            => '/',
];

// read "the current user" anywhere —— note the parent name User, not GlobalUser
use DuckPhp\GlobalUser\User;

$id    = User::_()->id(true);          // when not logged in, handled by throwLoginOn() (302/JSON/custom callback + exit)
$name  = User::_()->name(false);       // check_login=false: an empty string when not logged in, without interrupting
$url   = User::_()->urlForLogin('/order/1');   // go back to /order/1 after login → generates '?b=%2Forder%2F1'
if (!User::_()->canAccess()) {         // default arguments = the class/method/PATH_INFO of the current route
    // no permission
}
User::_()->register($post);            // registration: callback login service + write the session + fire events + (optionally) 302
User::_()->login($post);               // login
User::_()->logout();                   // logout
```

## Configuration example

```php
// make "not logged in" stop 302ing and go through your own logic instead (e.g. an API returning 401)
$options['globaluser_need_login_callback'] = function () {
    \DuckPhp\Core\SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'USER_NEED_LOGIN']);
};
// once the callback returns, the component calls SystemWrapper::exit(); the request ends there.

// or give user pages a shared header and footer (template paths are resolved relative to path_view)
$options['globaluser_view_file_header'] = 'inc-head';
$options['globaluser_view_file_footer'] = 'inc-foot';
```

## Caveats

- **Required versus optional callbacks**: `globaluser_local_service` / `globaluser_login_service` / `globaluser_login_session` are "look up by key, throw when missing" — `run_callback_by_key()` throws `DuckPhpSystemException(" need ext options 'globaluser_login_session'", -1)` (note the single space before `need` in the message); whereas `globaluser_ext_view_data_callback` / `globaluser_need_login_callback` are guarded with `isset()`, so a missing one is just skipped.
- **`user_provider_enable` (a hidden option)**: in `init()`, `$context->options['user_provider_enable'] ?? true`; when false it does **not** register itself into `User::_()`, so `User::_()` is the stub instance of the parent `User` (calling it throws). It is on by default.
- **Context options win**: `urlForHome()` / `urlForLogout()` read **the App's** `url_user_home` / `url_user_logout` first (the two are `DuckPhp`'s hidden options and are not in this page's option table), then fall back to the component's own `globaluser_url_*`, and finally to `'/'`; `urlForRegister()` / `urlForLogin()` do not look at context options.
- **`$url_back` and `$ext`**: all four `urlFor*()` accept `(?string $url_back = null, ?array $ext = null)`, and both are assembled into a query string by the internal method `buildUrlBackQuery()` — the key/value pairs in `$ext` go straight into the query string, and `$url_back` goes last as `b` (`?b=...`); when both are empty no `?` is added. The 302 branch of `throwLoginOn()` passes the current `REQUEST_URI` path in as `$url_back`.
- **The header/footer switch of `mergeViewData()`**: the header/footer files are rendered only when `$data['__logined_render_header_footer']` (treated as `true` when absent) is truthy; the rendered result goes both into `__view_data.header/footer` (for the view) and into `__logined_header_file/footer_file` (for `View::setViewHeaderFooter()`, consumed by `DuckPhp::_Show()`).
- **Rendering is taken over automatically**: when `__use_logined_view_data` is truthy and the current route's calling class implements `UserControllerInterface`, `DuckPhp::_Show()` calls this component's `mergeViewData()` by itself; you normally do not need to call it manually.
- When all three arguments of `canAccess()` are `null` it **temporarily switches to `App::getLastPhase()`** to read the current route's class/method/PATH_INFO, then switches back; with explicit arguments it does not do this.
- The not-logged-in handling of `id()/name()/data()` all goes through `throwLoginOn()`: ① with `globaluser_need_login_callback` configured → callback + `exit()`; ② not Ajax → `Show302(urlForLogin(current path))` + `exit()`; ③ Ajax (`X-Requested-With: XMLHttpRequest`) → `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])` + `exit()`. All three `exit()`, so **the caller never gets a return value**; to avoid being interrupted, use `check_login = false`.
- Each of `register()/login()/logout()` fires an event before doing the work (`EVENT_ACTION_USER_*`); the matching service-side events (`EVENT_SERVICE_USER_*`) are fired by **your login service** when it sees fit, not by this component.
- `logout()` uses `id(false)`: when not logged in the id is `0`, and it still calls the login service's `logout(0)` and clears the session as usual — to avoid that, check `id(false)` yourself first.
- `service()` / `log()` / `batchGetUsernames()` are inherited from `User` and all go through `localService()` internally; the `__logined_*` fields of `mergeViewData()` are filled by the parent too, and this class only appends the header/footer.

## All options

```php
public $options = [
    'globaluser_is_authed_redirect' => true,

    'globaluser_url_home' => null,
    'globaluser_url_register' => null,
    'globaluser_url_login' => null,
    'globaluser_url_logout' => null,

    // 'inc-head',
    'globaluser_view_file_header' => null,
    // 'inc-foot',
    'globaluser_view_file_footer' => null,

    'globaluser_enable_callback_singleton' => true,
    //[UserAction::class,'service'],
    'globaluser_local_service' => null,
    //[UserAction::class,'loginservice'],
    'globaluser_login_service' => null,
    //[UserAction::class,'loginsession'],
    'globaluser_login_session' => null,
    //[UserAction::class,'addExtViewData'],
    'globaluser_ext_view_data_callback' => null,
    //[UserAction::class,'needLogin'],
    'globaluser_need_login_callback' => null,
];
```

## Methods

### Public methods

    public function init(array $options, ?object $context = null)
Initialises the component: reads the `globaluser_*` options; when `user_provider_enable` (true by default) is true it wraps itself in a `PhaseProxy` and registers it under the key `User::class`.

    public function id(bool $check_login = true)
The current user ID: reads the session's `getCurrentUserId()`; when it cannot be obtained and `$check_login` is set it hands over to `throwLoginOn()` (which 302s/JSONs/calls back and then `exit()`s).

    public function name(bool $check_login = true): string
The current user name: reads the session's `getCurrentUserName()`; when it cannot be obtained and `$check_login` is set it hands over to `throwLoginOn()`.

    public function data(bool $check_login = true): array
The current user's data array: reads the session's `getCurrentUser()`; when it cannot be obtained and `$check_login` is set it hands over to `throwLoginOn()`.

    public function localService()
Returns the local (current Phase) `UserServiceInterface` implementation: it runs the `globaluser_local_service` callback (throwing when the key is missing).

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
The in-site home URL: the App's `url_user_home` → `globaluser_url_home` → `'/'`, generated through `__url()`, then the query string is assembled from `$url_back`/`$ext`.

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
The registration page URL: `__url(globaluser_url_register ?? '/')`, then the query string is assembled from `$url_back`/`$ext`.

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
The login page URL: `__url(globaluser_url_login ?? '/')`, then the query string is assembled from `$url_back`/`$ext`.

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
The logout URL: the App's `url_user_logout` → `globaluser_url_logout` → `'/'`, then the query string is assembled from `$url_back`/`$ext`.

    public function mergeViewData(array $data): array
Completes the user view data: it runs `globaluser_ext_view_data_callback` first (when configured), then renders the header/footer files according to the switch and writes `__view_data.header/footer` and `__logined_header_file/footer_file`, and finally hands over to the parent `User::mergeViewData()` to fill `__logined_id/name/data/url_home/url_logout`.

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
Tells whether the current user may access it: when not logged in (`id(false)` falsy) it is straight `false`; when all three arguments are empty it takes "the current route's class / method / PATH_INFO" (temporarily switching to `getLastPhase()` and back); finally it delegates to `localService()->canAccess($id, $url, $class, $method)`.

    public function batchGetUsernames(array $ids): array
Fetches user names in batch by ID: delegates to `localService()->batchGetUsernames($ids)`.

    public function register(array $post)
Registration: fires `EVENT_ACTION_USER_REGISTERING` → `getLoginService()->register($post)` → the session's `setCurrentUser()` → fires `EVENT_ACTION_USER_REGISTERED`; with `globaluser_is_authed_redirect` truthy it does `Show302(urlForHome())`.

    public function login(array $post)
Login: fires `EVENT_ACTION_USER_LOGINING` → `getLoginService()->login($post)` → the session's `setCurrentUser()` → fires `EVENT_ACTION_USER_LOGINED`; with `globaluser_is_authed_redirect` truthy it does `Show302(urlForHome())`.

    public function logout()
Logout: takes the current id (`id(false)`) → fires `EVENT_ACTION_USER_LOGOUTING` → `getLoginService()->logout($user_id)` → the session's `unsetCurrentUser()` → fires `EVENT_ACTION_USER_LOGOUTED`; with `globaluser_is_authed_redirect` truthy it does `Show302(urlForLogin())`.

### Protected methods

    protected function run_callback_by_key(string $key, ...$args)
Runs a callback by its option key: when the key is not configured it throws `DuckPhpSystemException(" need ext options 'key name'", -1)`; when the callback is `[class name, method]` and `globaluser_enable_callback_singleton` is true, it first replaces the class name with `ClassName::_()`.

    protected function throwLoginOn($flag)
The unified not-logged-in handling (it returns straight away when `$flag` is falsy): ① `globaluser_need_login_callback` → callback + `exit()`; ② not Ajax → `Show302(urlForLogin(the path of REQUEST_URI))`; ③ Ajax → `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])`; ② and ③ both `exit()` at the end.

    protected function buildUrlBackQuery(?string $url_back, ?array $ext): string
Assembles `$ext` (extra query parameters) and `$url_back` (key name `b`, placed last) into a query string of the form `?a=1&b=...`; it returns an empty string when both are empty.

    protected function getLoginService()
Gets the login service implementation (`UserLoginServiceInterface`): it runs the `globaluser_login_service` callback.

    protected function getSession()
Gets the session implementation (`UserSessionInterface`): it runs the `globaluser_login_session` callback.

## Related links

- [DuckPhp\GlobalUser\User](GlobalUser-User.md) — the parent: the constants and the "unavailable by default" stub
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — the action contract this component implements
- [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) — the registration/login/logout action contract
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — the contract of `globaluser_local_service`
- [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) — the contract of `globaluser_login_service`
- [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) — the contract of `globaluser_login_session`
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — the cross-Phase proxy used when `init()` registers itself
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — the isomorphic component on the administrator side
