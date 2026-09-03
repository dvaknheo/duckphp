# DuckPhp\GlobalUser\GlobalUser

## 简介

`GlobalUser` 是 DuckPHP 的「全局用户组件」：它实现 `UserActionInterface`，把“当前用户是谁 / 站内 URL / 用户视图 / 权限与日志”等能力集中到一个组件，并允许通过**选项回调（callback）**把具体实现外包给工程类（如 `UserAction`、`UserService`）。

典型接入方式：在应用选项中配置 `user_callback_for_id/name/data/local_service`（以及若干 `user_url_*`/`user_callback_for_url_for_*`）指向工程实现；控制器侧经 `Helper`（`User()/UserId()/…`）或直接 `GlobalUser::_()` 使用。`Foundation\Controller\UserControllerBase` 与之配套使用。

与 `GlobalAdmin` 的差异：面向“前台登录用户”场景，URL 含注册（`user_url_regist`/`urlForRegist`），服务含批量取用户名（`batchGetUsernames`），没有“超级管理员”。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`class GlobalUser extends DuckPhp\Core\ComponentBase implements UserActionInterface`
- 实现的接口：`UserActionInterface`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `user_url_home` / `user_url_regist` / `user_url_login` / `user_url_logout` | `null` | 站内首页/注册/登录/退出 URL（未配 callback 时用 `__url()` 生成）。 |
| `user_view_file_header` / `user_view_file_footer` | `null` | 用户页头/页脚视图文件。 |
| `user_enable_callback_singleton` | `true` | 回调为 `[类名, 方法]` 时是否先把类名转成 `类名::_()` 单例实例。 |
| `user_callback_for_id` / `for_name` / `for_data` | `null` | 取当前用户 id/name/data 的回调。 |
| `user_callback_for_local_service` | `null` | 返回本地 `UserServiceInterface` 实现的回调。 |
| `user_callback_for_add_ext_view_data` | `null` | 追加视图数据的回调（不设时默认注入 `__logined_id/name/url_logout`）。 |
| `user_callback_for_url_for_home` / `for_regist` / `for_login` / `for_logout` | `null` | 生成对应 URL 的回调（优先于 `user_url_*`）。 |

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
- `id()` 未配置 provider 时抛 `"No GlobalUser Provider."`。
- `service()` 与 `localService()`：前者经 `PhaseProxy` 包装成可跨 Phase 调用的代理；后者返回当前 Phase 的服务。
- URL 生成优先 callback；无 callback 时 `__url($options['user_url_*'])`。
- `_Show()` 会临时切到 `App::getLastPhase()` 并设置 `user_view_file_header/footer` 为头尾模板后渲染，结束后恢复原 Phase。
- `canAccess()` 缺省参数时取当前路由的 class/method/PATH_INFO，然后交给 `localService()->canAccess($id, …)`（注意与 `GlobalAdmin` 不同，本实现不额外包 `__url()`，以源码为准）。
- 组件经 `ComponentBase` 的 `_()` 取实例。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
当前用户 ID（`$check_login=true` 时未登录即报错）。

    public function name(bool $check_login = true): string
当前用户名。

    public function data(bool $check_login = true): array
当前用户数据数组。

    public function localService()
返回本地（当前 Phase）的 `UserServiceInterface` 实现。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
站内首页 URL。

    public function urlForRegist(?string $url_back = null, ?array $ext = null): string
注册 URL。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录 URL。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出 URL。

    public function service()
返回可跨 Phase 调用的用户服务（`PhaseProxy` 包装 `localService()`）。

    public function mergeViewData(array $input): array
合并用户信息与页面头尾 HTML 到视图数据（`__logined_id/name/url_logout/__view_data`）。

    public function _Show(array $data = [], string $view = '')
以“登录用户页面”方式渲染（切 Phase、设头尾、走 `View::_Show`）。

    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
判断当前用户能否访问；缺省参数取当前路由上下文。

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

## 相关链接

- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 本组件实现的接口
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — 服务侧契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — service() 的跨 Phase 代理
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员侧同构组件
