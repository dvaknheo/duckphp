# DuckPhp\GlobalAdmin\GlobalAdmin

## 简介

`GlobalAdmin` 是 DuckPHP 的「全局管理员组件」：它实现 `AdminActionInterface`，把“当前管理员是谁 / 后台 URL / 后台视图 / 权限与日志”等能力集中到一个组件，并允许通过**选项回调（callback）**把具体实现外包给工程类（如 `AdminAction`、`AdminService`）。

典型接入方式：在应用选项中配置 `admin_callback_for_id/name/data/local_service`（以及若干 `url_*`/`admin_url_*`）指向工程实现；控制器侧经 `Helper`（`Admin()/AdminId()/…`）或直接 `GlobalAdmin::_()` 使用。`Foundation\Controller\AdminControllerBase` 与之配套使用。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`class GlobalAdmin extends DuckPhp\Core\ComponentBase implements AdminActionInterface`
- 实现的接口：`AdminActionInterface`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `admin_url_home` / `admin_url_login` / `admin_url_logout` | `null` | 后台首页/登录/退出 URL（未配 callback 时用 `__url()` 生成）。 |
| `admin_view_file_header` / `admin_view_file_footer` | `null` | 后台页头/页脚视图文件（渲染时并入 `__view_data.header/footer`）。 |
| `admin_enable_callback_singleton` | `true` | 回调为 `[类名, 方法]` 数组时，是否先把类名转成 `类名::_()` 单例实例。 |
| `admin_callback_for_id` / `for_name` / `for_data` | `null` | 取当前管理员 id/name/data 的回调（可 `[AdminAction::class,'id']` 等）。 |
| `admin_callback_for_local_service` | `null` | 返回本地 `AdminServiceInterface` 实现的回调。 |
| `admin_callback_for_add_ext_view_data` | `null` | 追加视图数据的回调（不设时默认注入 `__logined_id/name/url_logout`）。 |
| `admin_callback_for_url_for_home` / `for_login` / `for_logout` | `null` | 生成对应 URL 的回调（优先于 `admin_url_*`）。 |

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
- `id()` 未配置 provider 时抛 `"No GlobalAdmin Provider."`。
- `service()` 与 `localService()`：后者直接返回本 Phase 的服务；前者用 `PhaseProxy::CreatePhaseProxy` 包装，便于跨子应用 Phase 调用。
- URL 生成优先回调；无回调时 `__url($options['admin_url_*'])`。
- `_Show()` 会临时切到 `App::getLastPhase()` 并设置 `admin_view_file_header/footer` 为头尾模板后渲染，结束后恢复原 Phase。
- `canAccess()` 缺省参数时取当前路由的 class/method/PATH_INFO，然后交给 `localService()->canAccess($id, …)`。
- 组件经 `ComponentBase` 的 `_()` 取实例；`Foundation\Controller\AdminControllerBase`/`GlobalAdmin` 配套见 F 批相关文档。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
当前管理员 ID（`$check_login=true` 时未登录即报错）。

    public function name(bool $check_login = true): string
当前管理员名。

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
以管理员页面方式渲染（切 Phase、设头尾、走 `View::_Show`）。

    public function canAccess(?string $class = null, ?string $method = null, ?string $url = null): bool
判断当前管理员能否访问；缺省参数取当前路由上下文。

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

## 相关链接

- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 本组件实现的接口
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — 服务侧契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — service() 的跨 Phase 代理
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 用户侧同构组件
