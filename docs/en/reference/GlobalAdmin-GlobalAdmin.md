# DuckPhp\GlobalAdmin\GlobalAdmin

## Introduction

`GlobalAdmin` is the **complete implementation** of the administrator system: it extends `Admin` (the constants and the "unavailable by default" stub implementations live in the parent), implements `AdminLoginActionInterface`, and concentrates "who the current administrator is / the back-office URLs / the back-office header and footer / permissions and logging / login and logout" into one component.

It itself **neither touches the database nor touches `$_SESSION`**; instead it outsources three things (all through option callbacks):

1. **The session** (`globaladmin_login_session` → `AdminSessionInterface`): the sole data source of `id()/name()/data()`;
2. **The service** (`globaladmin_local_service` → `AdminServiceInterface`): where `canAccess()` and `log()` go;
3. **The login service** (`globaladmin_login_service` → `AdminLoginServiceInterface`): the business implementation of `login()` / `logout()`.

At `init()` it wraps itself in a `PhaseProxy` and registers it under the container key `Admin::class`, the parent's name (this can be switched off, see `admin_provider_enable`), so the whole framework (including `Foundation\Controller\ControllerHelper::Admin()` and `Foundation\Controller\AdminControllerBase`) reaches it through `Admin::_()`.

## Class info

- Namespace: `DuckPhp\GlobalAdmin`
- Declaration: `class GlobalAdmin extends DuckPhp\GlobalAdmin\Admin implements AdminActionInterface, AdminLoginActionInterface`
- Parent: `DuckPhp\GlobalAdmin\Admin` (which provides the login fields of `mergeViewData()`, the delegation of `service()`/`log()`/`isSuper()`, and every constant)
- Interfaces implemented: `AdminActionInterface`, `AdminLoginActionInterface`
- Traits used: none
- Constants: **this class declares none**; they all come from `Admin` (4 `EVENT_ACTION_ADMIN_*`, 4 `EVENT_SERVICE_ADMIN_*`, 4 `EXCEPTION_*`; see [Admin](GlobalAdmin-Admin.md))

## Options

| Option | Default | Meaning |
|---|---|---|
| `globaladmin_is_authed_redirect` | `true` | Whether to 302 automatically once `login()`/`logout()` finishes (login goes to `urlForHome()`, logout to `urlForLogin()`). |
| `globaladmin_url_home` | `null` | The back-office home URL; when unset it takes the App's context option of the same meaning, `url_admin_home`, and then falls back to `'/'`. |
| `globaladmin_url_login` | `null` | The back-office login URL; when unset it falls back to `'/'` (this is the 302 target of `throwLoginOn()`). |
| `globaladmin_url_logout` | `null` | The back-office logout URL; when unset it takes the App's context option `url_admin_logout`, then falls back to `'/'`. |
| `globaladmin_view_file_header` | `null` | The back-office header view file (rendered only when non-empty; the result is merged into `__view_data.header`). |
| `globaladmin_view_file_footer` | `null` | The back-office footer view file (rendered only when non-empty; the result is merged into `__view_data.footer`). |
| `globaladmin_enable_callback_singleton` | `true` | When a callback is `[class name, method]`, whether to replace the class name with the `ClassName::_()` singleton instance first. |
| `globaladmin_local_service` | `null` | The callback returning the `AdminServiceInterface` implementation (with the key missing, `localService()` throws). |
| `globaladmin_login_service` | `null` | The callback returning the `AdminLoginServiceInterface` implementation. |
| `globaladmin_login_session` | `null` | The callback returning the `AdminSessionInterface` implementation. |
| `globaladmin_ext_view_data_callback` | `null` | The callback appending view data (with the key missing it **does not throw**; it is simply skipped, unlike the three "required" callbacks above). |
| `globaladmin_need_login_callback` | `null` | Custom handling when not logged in; once configured it **does not** go through the default 302/JSON, only the callback and then `exit()`. |

## Usage

```php
// hang the three things on the application options (mounting only some is fine too; unused keys do not throw)
$options = [
    'ext' => [
        \DuckPhp\GlobalAdmin\GlobalAdmin::class => true,
    ],
    'globaladmin_login_session' => [\MyProject\Admin\AdminSession::class, '_'],
    'globaladmin_local_service' => [\MyProject\Admin\AdminService::class, '_'],
    'globaladmin_login_service' => [\MyProject\Admin\AdminLoginService::class, '_'],
    'url_admin_home'            => '/admin/',
];

// read "the current administrator" anywhere —— note the parent name Admin, not GlobalAdmin
use DuckPhp\GlobalAdmin\Admin;

$id   = Admin::_()->id(true);          // when not logged in, handled by throwLoginOn() (302/JSON/custom callback + exit)
$name = Admin::_()->name(false);       // check_login=false: an empty string when not logged in, without interrupting
if (!Admin::_()->canAccess()) {        // default arguments = the class/method/PATH_INFO of the current route
    // no permission
}
Admin::_()->login($post);              // login: callback login service + write the session + fire events + (optionally) 302
```

## Configuration example

```php
// make "not logged in" stop 302ing and go through your own logic instead (e.g. an API returning 401, or an audit log)
$options['globaladmin_need_login_callback'] = function () {
    \DuckPhp\Core\SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'ADMIN_NEED_LOGIN']);
};
// once the callback returns, the component calls SystemWrapper::exit(); the request ends there.

// or do not redirect automatically after login/logout (e.g. you control the target yourself)
$options['globaladmin_is_authed_redirect'] = false;
```

## Caveats

- **Required versus optional callbacks**: `globaladmin_local_service` / `globaladmin_login_service` / `globaladmin_login_session` are "look up by key, throw when missing" — `run_callback_by_key()` throws `DuckPhpSystemException(" need ext options 'globaladmin_login_session'", -1)` (note the single space before `need` in the message); whereas `globaladmin_ext_view_data_callback` / `globaladmin_need_login_callback` are guarded with `isset()`, so a missing one is just skipped.
- **`admin_provider_enable` (a hidden option)**: in `init()`, `$context->options['admin_provider_enable'] ?? true`; when false it does **not** register itself into `Admin::_()`, so `Admin::_()` is the stub instance of the parent `Admin` (calling it throws). It is on by default.
- **Context options win**: `urlForHome()` / `urlForLogout()` read **the App's** `url_admin_home` / `url_admin_logout` first, then fall back to the component's own `globaladmin_url_*`, and finally to `'/'`; `urlForLogin()` does not look at context options and only honours `globaladmin_url_login`.
- `urlForLogin($url_back)` assembles `$url_back` into `'?b=' . urlencode($url_back)` (only when it is passed). That is how the 302 branch of `throwLoginOn()` passes the current `REQUEST_URI` path in.
- **The header/footer switch of `mergeViewData()`**: the header/footer files are rendered only when `$data['__logined_render_header_footer']` (treated as `true` when absent) is truthy; the rendered result goes both into `__view_data.header/footer` (for the view) and into `__logined_header_file/footer_file` (for `View::setViewHeaderFooter()`, consumed by `DuckPhp::_Show()`).
- **Rendering is taken over automatically**: when `__use_logined_view_data` is truthy and the current route's calling class implements `AdminControllerInterface`, `DuckPhp::_Show()` calls this component's `mergeViewData()` by itself; you normally do not need to call it manually.
- When all three arguments of `canAccess()` are `null` it **temporarily switches to `App::getLastPhase()`** to read the current route's class/method/PATH_INFO, then switches back; with explicit arguments it does not do this.
- The not-logged-in handling of `id()/name()/data()` all goes through `throwLoginOn()`: ① with `globaladmin_need_login_callback` configured → callback + `exit()`; ② not Ajax → `Show302(urlForLogin(current path))` + `exit()`; ③ Ajax (`X-Requested-With: XMLHttpRequest`) → `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])` + `exit()`. All three `exit()`, so **the caller never gets a return value**; to avoid being interrupted, use `check_login = false`.
- `service()` / `log()` / `isSuper()` are inherited from `Admin` and all go through `localService()` internally; the `__logined_*` fields of `mergeViewData()` are filled by the parent too, and this class only appends the header/footer.

## All options

```php
public $options = [
    'globaladmin_is_authed_redirect' => true,

    'globaladmin_url_home' => null,
    'globaladmin_url_login' => null,
    'globaladmin_url_logout' => null,

    // 'inc-head',
    'globaladmin_view_file_header' => null,
    // 'inc-foot',
    'globaladmin_view_file_footer' => null,

    'globaladmin_enable_callback_singleton' => true,
    //[AdminAction::class,'service'],
    'globaladmin_local_service' => null,
    //[AdminAction::class,'loginservice'],
    'globaladmin_login_service' => null,
    //[AdminAction::class,'loginsession'],
    'globaladmin_login_session' => null,
    //[AdminAction::class,'addExtViewData'],
    'globaladmin_ext_view_data_callback' => null,
    //[AdminAction::class,'needLogin'],
    'globaladmin_need_login_callback' => null,
];
```

## Methods

### Public methods

    public function init(array $options, ?object $context = null)
Initialises the component: reads the `globaladmin_*` options; when `admin_provider_enable` (true by default) is true it wraps itself in a `PhaseProxy` and registers it under the key `Admin::class`.

    public function id(bool $check_login = true)
The current administrator ID: reads the session's `getCurrentAdminId()`; when it cannot be obtained and `$check_login` is set it hands over to `throwLoginOn()` (which 302s/JSONs/calls back and then `exit()`s).

    public function name(bool $check_login = true): string
The current administrator name: reads the session's `getCurrentAdminName()`; when it cannot be obtained and `$check_login` is set it hands over to `throwLoginOn()`.

    public function data(bool $check_login = true): array
The current administrator's data array: reads the session's `getCurrentAdmin()`; when it cannot be obtained and `$check_login` is set it hands over to `throwLoginOn()`.

    public function localService()
Returns the local (current Phase) `AdminServiceInterface` implementation: it runs the `globaladmin_local_service` callback (throwing when the key is missing).

    public function urlForHome(): string
The back-office home URL: the App's `url_admin_home` → `globaladmin_url_home` → `'/'`, generated through `__url()`.

    public function urlForLogin(?string $url_back = null): string
The back-office login URL: `__url(globaladmin_url_login ?? '/')`; when `$url_back` is passed it appends `'?b=' . urlencode($url_back)`.

    public function urlForLogout(): string
The back-office logout URL: the App's `url_admin_logout` → `globaladmin_url_logout` → `'/'`.

    public function mergeViewData(array $data): array
Completes the back-office view data: it runs `globaladmin_ext_view_data_callback` first (when configured), then renders the header/footer files according to the switch and writes `__view_data.header/footer` and `__logined_header_file/footer_file`, and finally hands over to the parent `Admin::mergeViewData()` to fill `__logined_id/name/data/url_home/url_logout`.

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
Tells whether the current administrator may access it: when not logged in (`id(false)` falsy) it is straight `false`; when all three arguments are empty it takes "the current route's class / method / PATH_INFO" (temporarily switching to `getLastPhase()` and back); finally it delegates to `localService()->canAccess($id, $url, $class, $method)`.

    public function login(array $post)
Login: fires `EVENT_ACTION_ADMIN_LOGINING` → `getLoginService()->login($post)` → the session's `setCurrentAdmin()` → fires `EVENT_ACTION_ADMIN_LOGINED`; with `globaladmin_is_authed_redirect` truthy it does `Show302(urlForHome())`.

    public function logout()
Logout: takes the current id (`id(false)`) → fires `EVENT_ACTION_ADMIN_LOGOUTING` → `getLoginService()->logout($admin_id)` → the session's `unsetCurrentAdmin()` → fires `EVENT_ACTION_ADMIN_LOGOUTED`; with `globaladmin_is_authed_redirect` truthy it does `Show302(urlForLogin())`.

### Protected methods

    protected function run_callback_by_key(string $key, ...$args)
Runs a callback by its option key: when the key is not configured it throws `DuckPhpSystemException(" need ext options 'key name'", -1)`; when the callback is `[class name, method]` and `globaladmin_enable_callback_singleton` is true, it first replaces the class name with `ClassName::_()`.

    protected function throwLoginOn($flag)
The unified not-logged-in handling (it returns straight away when `$flag` is falsy): ① `globaladmin_need_login_callback` → callback + `exit()`; ② not Ajax → `Show302(urlForLogin(the path of REQUEST_URI))`; ③ Ajax → `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])`; ② and ③ both `exit()` at the end.

    protected function getLoginService()
Gets the login service implementation (`AdminLoginServiceInterface`): it runs the `globaladmin_login_service` callback.

    protected function getSession()
Gets the session implementation (`AdminSessionInterface`): it runs the `globaladmin_login_session` callback.

## Related links

- [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-Admin.md) — the parent: the constants and the "unavailable by default" stub
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — the action contract this component implements
- [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) — the login/logout action contract
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — the contract of `globaladmin_local_service`
- [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) — the contract of `globaladmin_login_service`
- [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) — the contract of `globaladmin_login_session`
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — the cross-Phase proxy used when `init()` registers itself
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — the isomorphic component on the user side
