# DuckPhp\GlobalUser\GlobalUser

## 简介

`GlobalUser` 是 DuckPHP 的「全局用户组件」：它实现 `UserActionInterface`，把“当前用户是谁 / 站内 URL / 用户视图 / 权限与日志”等能力集中到一个组件，并允许通过**选项回调（callback）**把具体实现外包给工程类（如 `UserAction`、`UserService`）。

典型接入方式：在应用选项中配置 `user_callback_for_id/name/data/local_service`（以及若干 `user_url_*`/`user_callback_for_url_for_*`）指向工程实现；控制器侧经 `Helper`（`User()/UserId()/…`）或直接 `GlobalUser::_()` 使用。`Foundation\Controller\UserControllerBase` 与之配套使用。

与 `GlobalAdmin` 的差异：面向“前台登录用户”场景，URL 含注册（`user_url_regist`/`urlForRegist`），服务含批量取用户名（`batchGetUsernames`），没有“超级管理员”。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`class GlobalUser extends DuckPhp\Core\ComponentBase implements UserActionInterface, UserLoginActionInterface`
- 实现的接口：`UserActionInterface`、`UserLoginActionInterface`
- 事件常量（`const`，共 12 个）：

```php
const EVENT_ACTION_USER_REGISTERING  = 'ACTION_USER_REGISTERING';
const EVENT_ACTION_USER_REGISTERED   = 'ACTION_USER_REGISTERED';
const EVENT_ACTION_USER_LOGINING     = 'ACTION_USER_LOGINING';
const EVENT_ACTION_USER_LOGINED      = 'ACTION_USER_LOGINED';
const EVENT_ACTION_USER_LOGOUTING    = 'ACTION_USER_LOGOUTING';
const EVENT_ACTION_USER_LOGOUTED     = 'ACTION_USER_LOGOUTED';
const EVENT_SERVICE_USER_REGISTERING = 'SERVICE_USER_REGISTERING';
const EVENT_SERVICE_USER_REGISTERED  = 'SERVICE_USER_REGISTERED';
const EVENT_SERVICE_USER_LOGINING    = 'SERVICE_USER_LOGINING';
const EVENT_SERVICE_USER_LOGINED     = 'SERVICE_USER_LOGINED';
const EVENT_SERVICE_USER_LOGOUTING   = 'SERVICE_USER_LOGOUTING';
const EVENT_SERVICE_USER_LOGOUTED    = 'SERVICE_USER_LOGOUTED';
```

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `user_url_home` | `null` | 站内首页 URL（未配回调时用 `__url()` 生成）。 |
| `user_url_register` | `null` | 注册 URL（键名由旧 `user_url_regist` 更名）。 |
| `user_url_login` | `null` | 登录 URL。 |
| `user_url_logout` | `null` | 退出 URL。 |
| `user_view_file_header` | `null` | 用户页头视图文件。 |
| `user_view_file_footer` | `null` | 用户页脚视图文件。 |
| `user_enable_callback_singleton` | `true` | 回调为 `[类名, 方法]` 时是否先把类名转成 `类名::_()` 单例实例。 |
| `user_callback_for_id` | `null` | 取当前用户 id 的回调。 |
| `user_callback_for_name` | `null` | 取当前用户名的回调。 |
| `user_callback_for_data` | `null` | 取当前用户数据（数组）的回调。 |
| `user_callback_for_local_service` | `null` | 返回本地 `UserServiceInterface` 实现的回调。 |
| `user_callback_for_add_ext_view_data` | `null` | 追加视图数据的回调（不设时默认注入 `__logined_id/name/url_logout`）。 |
| `user_callback_for_login_service` | `null` | 登录服务回调（`register()/login()/logout()` 经 `getLoginBusiness()` 调用它）。 |
| `user_callback_for_session` | `null` | 用户会话实现回调（返回 `UserSessionInterface`）；配置后 `id()/name()` 优先读会话。 |
| `user_loginout_auto_redirect` | `true` | `register()/login()/logout()` 完成后是否自动 302（注册/登录跳 home、退出跳 login）。 |
| `user_callback_for_url_for_home` | `null` | 生成首页 URL 的回调（优先于 `user_url_home`）。 |
| `user_callback_for_url_for_regist` | `null` | 生成注册 URL 的回调（键名仍为 `..._regist`，源码原样）。 |
| `user_callback_for_url_for_login` | `null` | 生成登录 URL 的回调。 |
| `user_callback_for_url_for_logout` | `null` | 生成退出 URL 的回调。 |
| `user_default_exception_class` | `null` | 未登录时抛出的异常类（缺省用 `UserException::class`）；只在会话模式（配了 `user_callback_for_session`）下的 `id()/name()` 里生效。 |

## 使用方式

```php
// App 选项里配置 provider（工程示例）：
$options = [
    'user_callback_for_id'   => [UserAction::class, 'id'],
    'user_callback_for_name' => [UserAction::class, 'name'],
    'user_callback_for_data' => [UserAction::class, 'data'],
    'user_callback_for_local_service' => [UserAction::class, 'service'],
];

// 控制器内：
$user = GlobalUser::_();
$uid  = $user->id();                      // 当前用户 id（未登录抛错）
if (!$user->canAccess()) { /* 无权 */ }
$names = $user->batchGetUsernames([1, 2, 3]);
$user->_Show($data, 'user/center');       // 带用户页头尾的渲染
```

## 注意事项

- **callback 机制**：`run_callback_by_key()` 要求对应选项键已配置（否则 `ThrowOn "need app options 'key'"`）；回调是 `[类名, 方法]` 且 `user_enable_callback_singleton` 开启时，把类名替换为 `类名::_()`。
- `id()/name()` 未配置任何 provider（session / id / name 回调）时抛 `DuckPhpSystemException("No GlobalUser Provider.")`；配置了 session 而未登录时抛 `UserException`。
- `service()` 与 `localService()`：前者经 `PhaseProxy` 包装成可跨 Phase 调用的代理；后者返回当前 Phase 的服务。
- URL 生成优先 callback；无 callback 时 `__url($options['user_url_*'])`。
- `_Show()` 会临时切到 `App::getLastPhase()`；头尾模板仅在 `user_view_file_header/footer` 非空时解析；`$view` 为空时使用当前路由路径。
- `canAccess()` 缺省参数时取当前路由的 class/method/PATH_INFO，然后交给 `localService()->canAccess($id, …)`。
- 组件经 `ComponentBase` 的 `_()` 取实例。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
当前用户 ID：配置了 `user_callback_for_session` 时读会话（未登录且 `$check_login` 时抛 `UserException("id(): NoLogin")`）；否则走 `user_callback_for_id`；都未配置则抛 `DuckPhpSystemException("id(): No GlobalUser Provider.")`。

    public function name(bool $check_login = true): string
当前用户名：会话优先（未登录抛 `UserException("name() NoLogin 2")`）；否则走 `user_callback_for_name`；都未配置则抛 `DuckPhpSystemException`。

    public function data(bool $check_login = true): array
当前用户数据数组。

    public function localService()
返回本地（当前 Phase）的 `UserServiceInterface` 实现。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
站内首页 URL。

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
注册 URL（由旧名 `urlForRegist` 更名；内部取 `user_url_register`）。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录 URL。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出 URL。

    public function service()
返回可跨 Phase 调用的用户服务（`PhaseProxy` 包装 `localService()`）。

    public function mergeViewData(array $input): array
合并用户信息与页面头尾 HTML 到视图数据（`__logined_id/name/url_logout/__view_data`）。

    public function _Show(array $data = [], string $view = '')
以“登录用户页面”方式渲染：切到 `App::getLastPhase()`、`onBeforeOutput()`、设置头尾（仅当 `user_view_file_header/footer` 非空才解析文件），`$view` 为空时改用当前路由路径；结束后恢复原 Phase。

    public function register(array $post)
注册：触发 `EVENT_ACTION_USER_REGISTERING` → `getLoginBusiness()->register($post)` → 会话 `setCurrentUser()` → 触发 `EVENT_ACTION_USER_REGISTERED`；`user_loginout_auto_redirect` 为真时 302 到 `urlForHome()`。

    public function login(array $post)
登录：触发 `EVENT_ACTION_USER_LOGINING` → `getLoginBusiness()->login($post)` → 会话 `setCurrentUser()` → 触发 `EVENT_ACTION_USER_LOGINED`；`user_loginout_auto_redirect` 为真时 302 到 `urlForHome()`。

    public function logout()
退出：取当前 id → 触发 `EVENT_ACTION_USER_LOGOUTING` → `getLoginBusiness()->logout($user_id)` → 清除会话 → 触发 `EVENT_ACTION_USER_LOGOUTED`；`user_loginout_auto_redirect` 为真时 302 到 `urlForLogin()`。

    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
判断当前用户能否访问；缺省参数取当前路由的 class/method/PATH_INFO，随后交给 `localService()->canAccess($id, …)`。

    public function log(string $string, ?string $type = null, array $ext = [])
记录用户操作日志（委托 localService）。

    public function batchGetUsernames(array $ids): array
按 ID 批量取用户名（委托 localService）。

### 受保护方法

    protected function run_callback_by_key(string $key, ...$args)
按选项键执行回调：键缺失抛错；`[类, 方法]` 且开启 singleton 时类名转实例。

    protected function go_url(string $key_callback, string $key_url, ?string $url_back, ?array $ext)
生成 URL 的公共逻辑：有 callback 用 callback，否则 `__url(options[key_url])`。

    protected function addExtViewData(array $input): array
追加视图数据：默认补 `__logined_id/name/url_logout`，可被 `user_callback_for_add_ext_view_data` 覆盖。

    protected function getLoginBusiness()
取登录业务实现：执行 `user_callback_for_login_service` 回调。

    protected function getSession()
取会话实现（`UserSessionInterface`）：执行 `user_callback_for_session` 回调。

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 本组件实现的接口
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — 服务侧契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — service() 的跨 Phase 代理
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员侧同构组件
