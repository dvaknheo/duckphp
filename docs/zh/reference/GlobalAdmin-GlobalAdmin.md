# DuckPhp\GlobalAdmin\GlobalAdmin

## 简介

`GlobalAdmin` 是 DuckPHP 的「全局管理员组件」：它实现 `AdminActionInterface`，把“当前管理员是谁 / 后台 URL / 后台视图 / 权限与日志”等能力集中到一个组件，并允许通过**选项回调（callback）**把具体实现外包给工程类（如 `AdminAction`、`AdminService`）。

典型接入方式：在应用选项中配置 `admin_callback_for_id/name/data/local_service`（以及若干 `url_*`/`admin_url_*`）指向工程实现；控制器侧经 `Helper`（`Admin()/AdminId()/…`）或直接 `GlobalAdmin::_()` 使用。`Foundation\Controller\AdminControllerBase` 与之配套使用。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`class GlobalAdmin extends DuckPhp\Core\ComponentBase implements AdminActionInterface, AdminLoginActionInterface`
- 实现的接口：`AdminActionInterface`、`AdminLoginActionInterface`
- 事件常量（`const`，共 12 个）：

```php
const EVENT_ACTION_ADMIN_REGISTERING  = 'ACTION_ADMIN_REGISTERING';
const EVENT_ACTION_ADMIN_REGISTERED   = 'ACTION_ADMIN_REGISTERED';
const EVENT_ACTION_ADMIN_LOGINING     = 'ACTION_ADMIN_LOGINING';
const EVENT_ACTION_ADMIN_LOGED        = 'ACTION_ADMIN_LOGINED';
const EVENT_ACTION_ADMIN_LOGOUTING    = 'ACTION_ADMIN_LOGOUTING';
const EVENT_ACTION_ADMIN_LOGOUTED     = 'ACTION_ADMIN_LOGOUTED';
const EVENT_SERVICE_ADMIN_REGISTERING = 'SERVICE_ADMIN_REGISTERING';
const EVENT_SERVICE_ADMIN_REGISTERED  = 'SERVICE_ADMIN_REGISTERED';
const EVENT_SERVICE_ADMIN_LOGINING    = 'SERVICE_ADMIN_LOGINING';
const EVENT_SERVICE_ADMIN_LOGINED     = 'SERVICE_ADMIN_LOGINED';
const EVENT_SERVICE_ADMIN_LOGOUTING   = 'SERVICE_ADMIN_LOGOUTING';
const EVENT_SERVICE_ADMIN_LOGOUTED    = 'SERVICE_ADMIN_LOGOUTED';
```

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `admin_url_home` | `null` | 后台首页 URL（未配回调时用 `__url()` 生成）。 |
| `admin_url_login` | `null` | 后台登录 URL。 |
| `admin_url_logout` | `null` | 后台退出 URL。 |
| `admin_view_file_header` | `null` | 后台页头视图文件（渲染时并入 `__view_data.header`）。 |
| `admin_view_file_footer` | `null` | 后台页脚视图文件（渲染时并入 `__view_data.footer`）。 |
| `admin_enable_callback_singleton` | `true` | 回调为 `[类名, 方法]` 数组时，是否先把类名转成 `类名::_()` 单例实例。 |
| `admin_callback_for_id` | `null` | 取当前管理员 id 的回调。 |
| `admin_callback_for_name` | `null` | 取当前管理员名的回调。 |
| `admin_callback_for_data` | `null` | 取当前管理员数据（数组）的回调。 |
| `admin_callback_for_local_service` | `null` | 返回本地 `AdminServiceInterface` 实现的回调。 |
| `admin_callback_for_add_ext_view_data` | `null` | 追加视图数据的回调（不设时默认注入 `__logined_id/name/url_logout`）。 |
| `admin_callback_for_login_service` | `null` | 登录服务回调（`login()/logout()` 经 `getLoginBusiness()` 调用它）。 |
| `admin_callback_for_session` | `null` | 管理员会话实现回调（返回 `AdminSessionInterface`）；配置后 `id()/name()` 优先读会话。 |
| `admin_loginout_auto_redirect` | `true` | `login()/logout()` 完成后是否自动 302（登录跳 home、退出跳 login）。 |
| `admin_callback_for_url_for_home` | `null` | 生成首页 URL 的回调（优先于 `admin_url_home`）。 |
| `admin_callback_for_url_for_login` | `null` | 生成登录 URL 的回调（优先于 `admin_url_login`）。 |
| `admin_callback_for_url_for_logout` | `null` | 生成退出 URL 的回调（优先于 `admin_url_logout`）。 |

## 使用方式

```php
// App 选项里配置 provider（工程示例）：
$options = [
    'admin_callback_for_id'   => [AdminAction::class, 'id'],
    'admin_callback_for_name' => [AdminAction::class, 'name'],
    'admin_callback_for_data' => [AdminAction::class, 'data'],
    'admin_callback_for_local_service' => [AdminAction::class, 'service'],
];

// 控制器内：
$admin = GlobalAdmin::_();
$id    = $admin->id();                    // 当前管理员 id（未登录抛错）
if (!$admin->canAccess()) { /* 无权 */ }
$admin->_Show($data, 'admin/index');      // 带后台头尾的渲染
```

## 注意事项

- **callback 机制**：`run_callback_by_key()` 先要求对应选项键已配置（否则 `ThrowOn "need app options 'key'"`），然后 `call_user_func($callback, ...$args)`；若回调是 `[类名, 方法]` 且 `admin_enable_callback_singleton` 开启，则把类名替换为 `类名::_()`。
- `id()/name()` 未配置任何 provider（session / id / name 回调）时抛 `DuckPhpSystemException("No GlobalAdmin Provider.")`；配置了 session 而未登录时抛 `AdminException`。
- `service()` 与 `localService()`：后者直接返回本 Phase 的服务；前者用 `PhaseProxy::CreatePhaseProxy` 包装，便于跨子应用 Phase 调用。
- URL 生成优先回调；无回调时 `__url($options['admin_url_*'])`。
- `_Show()` 会临时切到 `App::getLastPhase()`；头尾模板仅在 `admin_view_file_header/footer` 非空时解析；`$view` 为空时使用当前路由路径。
- **隐藏选项**（读得到、但不在 `$options` 声明里，故本页选项表没有）：`__logined_enable_header_footer`——它出现在 `_Show()` 的 `$data` 或 `View::_()->data` 里且为真时，才把 `admin_view_file_header/footer` 设为视图 head/foot（缺省 `false`）。注：早期的 `use_admin_view_header_footer` 选项源码里已无读取点，别再使用。
- `canAccess()` 缺省参数时取当前路由的 class/method/PATH_INFO，然后交给 `localService()->canAccess($id, …)`。
- 组件经 `ComponentBase` 的 `_()` 取实例；`Foundation\Controller\AdminControllerBase`/`GlobalAdmin` 配套见 F 批相关文档。

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化组件（覆盖父类）：读入 admin_* 选项并完成 provider 装配。

    public function id(bool $check_login = true)
当前管理员 ID：配置了 `admin_callback_for_session` 时读会话（未登录且 `$check_login` 时抛 `AdminException(" NoLogin 1")`）；否则走 `admin_callback_for_id`；两者都未配置则抛 `DuckPhpSystemException("No GlobalAdmin Provider.")`。

    public function name(bool $check_login = true): string
当前管理员名：会话优先（未登录抛 `AdminException("NoLogin 2")`）；否则走 `admin_callback_for_name`；都未配置则抛 `DuckPhpSystemException`。

    public function data(bool $check_login = true): array
当前管理员数据数组。

    public function localService()
返回本地（当前 Phase）的 `AdminServiceInterface` 实现（经 `admin_callback_for_local_service`）。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
后台首页 URL：优先 callback，否则 `__url(admin_url_home)`。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录 URL。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出 URL。

    public function service()
返回可跨 Phase 调用的管理员服务（`PhaseProxy` 包装 `localService()`）。

    public function mergeViewData(array $input): array
合并登录信息与后台头尾 HTML 到视图数据（`__logined_id/__logined_name/__logined_url_logout/__view_data`）。

    public function _Show(array $data = [], string $view = '')
以管理员页面方式渲染：切到 `App::getLastPhase()`、`onBeforeOutput()`、设置头尾（仅当 `admin_view_file_header/footer` 非空才解析文件），`$view` 为空时改用当前路由路径；结束后恢复原 Phase。

    public function login(array $post)
登录：触发 `EVENT_ACTION_ADMIN_LOGINING` → `getLoginBusiness()->login($post)` → 会话 `setCurrentAdmin()` → 触发 `EVENT_ACTION_ADMIN_LOGED`；`admin_loginout_auto_redirect` 为真时 302 到 `urlForHome()`。

    public function logout()
退出：取当前 id → 触发 `EVENT_ACTION_ADMIN_LOGOUTING` → `getLoginBusiness()->logout($admin_id)` → 清除会话 → 触发 `EVENT_ACTION_ADMIN_LOGOUTED`；`admin_loginout_auto_redirect` 为真时 302 到 `urlForLogin()`。

    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
判断当前管理员能否访问；缺省参数取当前路由的 class/method/PATH_INFO，随后交给 `localService()->canAccess($id, …)`。

    public function log(string $string, ?string $type = null, array $ext = [])
记录管理员操作日志（委托 localService）。

    public function isSuper(): bool
当前管理员是否超级管理员（委托 localService）。

### 受保护方法

    protected function run_callback_by_key(string $key, ...$args)
按选项键执行回调：键缺失抛错；`[类, 方法]` 且开启 singleton 时类名转实例。

    protected function go_url(string $key_callback, string $key_url, ?string $url_back, ?array $ext)
生成 URL 的公共逻辑：有 callback 用 callback，否则 `__url(options[key_url])`。

    protected function addExtViewData(array $input): array
追加视图数据：默认补 `__logined_id/name/url_logout`，可被 `admin_callback_for_add_ext_view_data` 覆盖。

    protected function getLoginBusiness()
取登录业务实现：执行 `admin_callback_for_login_service` 回调。

    protected function getSession()
取会话实现（`AdminSessionInterface`）：执行 `admin_callback_for_session` 回调。

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 本组件实现的接口
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — 服务侧契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — service() 的跨 Phase 代理
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 用户侧同构组件
