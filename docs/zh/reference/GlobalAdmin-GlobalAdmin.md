# DuckPhp\GlobalAdmin\GlobalAdmin

## 简介

`GlobalAdmin` 是管理员体系的**完整实现**：它继承 `Admin`（常量与「默认不可用」的桩实现都在父类），实现 `AdminLoginActionInterface`，把「当前管理员是谁 / 后台 URL / 后台页眉页脚 / 权限与日志 / 登录登出」集中到一个组件里。

它自己**不碰数据库也不碰 `$_SESSION`**，而是把三件事外包出去（都通过选项回调）：

1. **会话**（`globaladmin_login_session` → `AdminSessionInterface`）：`id()/name()/data()` 的唯一数据源；
2. **服务**（`globaladmin_local_service` → `AdminServiceInterface`）：`canAccess()` 与 `log()` 的去处；
3. **登录服务**（`globaladmin_login_service` → `AdminLoginServiceInterface`）：`login()` / `logout()` 的业务实现。

`init()` 时会把自己包成 `PhaseProxy` 注册到父类名 `Admin::class` 这个容器键上（可关，见 `admin_provider_enable`），因此全框架（含 `Foundation\Controller\ControllerHelper::Admin()`、`Foundation\Controller\AdminControllerBase`）都通过 `Admin::_()` 用到它。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`class GlobalAdmin extends DuckPhp\GlobalAdmin\Admin implements AdminActionInterface, AdminLoginActionInterface`
- 父类：`DuckPhp\GlobalAdmin\Admin`（提供 `mergeViewData()` 的登录字段、`service()`/`log()`/`isSuper()` 的委托，以及全部常量）
- 实现的接口：`AdminActionInterface`、`AdminLoginActionInterface`
- 使用的 trait：无
- 常量：**本类不声明常量**，全部继承自 `Admin`（`EVENT_ACTION_ADMIN_*` 4 个、`EVENT_SERVICE_ADMIN_*` 4 个、`EXCEPTION_*` 4 个，见 [Admin](GlobalAdmin-Admin.md)）

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `globaladmin_is_authed_redirect` | `true` | `login()`/`logout()` 完成后是否自动 302（登录跳 `urlForHome()`、退出跳 `urlForLogin()`）。 |
| `globaladmin_url_home` | `null` | 后台首页 URL；未配时取 App 的同名上下文选项 `url_admin_home`，再退回 `'/'`。 |
| `globaladmin_url_login` | `null` | 后台登录 URL；未配时退回 `'/'`（这是 `throwLoginOn()` 的 302 目标）。 |
| `globaladmin_url_logout` | `null` | 后台退出 URL；未配时取 App 的上下文选项 `url_admin_logout`，再退回 `'/'`。 |
| `globaladmin_view_file_header` | `null` | 后台页眉视图文件（非空才渲染，结果并入 `__view_data.header`）。 |
| `globaladmin_view_file_footer` | `null` | 后台页脚视图文件（非空才渲染，结果并入 `__view_data.footer`）。 |
| `globaladmin_enable_callback_singleton` | `true` | 回调是 `[类名, 方法]` 时，是否先把类名换成 `类名::_()` 单例实例。 |
| `globaladmin_local_service` | `null` | 返回 `AdminServiceInterface` 实现的回调（键缺失时 `localService()` 抛异常）。 |
| `globaladmin_login_service` | `null` | 返回 `AdminLoginServiceInterface` 实现的回调。 |
| `globaladmin_login_session` | `null` | 返回 `AdminSessionInterface` 实现的回调。 |
| `globaladmin_ext_view_data_callback` | `null` | 追加视图数据的回调（键缺失时**不报错**，跳过即可；与上面三个「必需」回调不同）。 |
| `globaladmin_need_login_callback` | `null` | 未登录时的自定义处理；配了就**不会**走默认的 302/JSON，只回调再 `exit()`。 |

## 使用方式

```php
// 应用选项里把三件事挂上（也可以只挂一部分，用不到的键不会报错）
$options = [
    'ext' => [
        \DuckPhp\GlobalAdmin\GlobalAdmin::class => true,
    ],
    'globaladmin_login_session' => [\MyProject\Admin\AdminSession::class, '_'],
    'globaladmin_local_service' => [\MyProject\Admin\AdminService::class, '_'],
    'globaladmin_login_service' => [\MyProject\Admin\AdminLoginService::class, '_'],
    'url_admin_home'            => '/admin/',
];

// 任何地方读「当前管理员」——注意用父类名 Admin，而不是 GlobalAdmin
use DuckPhp\GlobalAdmin\Admin;

$id   = Admin::_()->id(true);          // 未登录时按 throwLoginOn() 处理（302/JSON/自定义回调 + exit）
$name = Admin::_()->name(false);       // check_login=false：未登录返回空串，不打断
if (!Admin::_()->canAccess()) {        // 缺省参数＝当前路由的 class/method/PATH_INFO
    // 无权限
}
Admin::_()->login($post);              // 登录：回调登录服务 + 写会话 + 触发事件 +（可选）302
```

## 配置示例

```php
// 让「未登录」不再 302，而是走你自己的逻辑（例如 API 返回 401、或记录审计日志）
$options['globaladmin_need_login_callback'] = function () {
    \DuckPhp\Core\SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'ADMIN_NEED_LOGIN']);
};
// 回调返回后组件会调用 SystemWrapper::exit()，请求到此为止。

// 不想让登录/退出后自动跳转（例如自己控制跳转目标）
$options['globaladmin_is_authed_redirect'] = false;
```

## 注意事项

- **必需回调与可选回调**：`globaladmin_local_service` / `globaladmin_login_service` / `globaladmin_login_session` 是「按键取值，缺了就抛」——`run_callback_by_key()` 抛 `DuckPhpSystemException(" need ext options 'globaladmin_login_session'", -1)`（注意消息里 `need` 前有一个空格）；而 `globaladmin_ext_view_data_callback` / `globaladmin_need_login_callback` 用 `isset()` 判过，缺了只是跳过。
- **`admin_provider_enable`（隐藏选项）**：`init()` 里 `$context->options['admin_provider_enable'] ?? true`，为假时**不**把自己注册到 `Admin::_()`，于是 `Admin::_()` 会是父类 `Admin` 的桩实例（调用即抛）。默认开着。
- **上下文选项优先**：`urlForHome()` / `urlForLogout()` 先读 **App 的** `url_admin_home` / `url_admin_logout`，没有再退回组件自己的 `globaladmin_url_*`，最后退回 `'/'`；`urlForLogin()` 不看上下文选项，只认 `globaladmin_url_login`。
- `urlForLogin($url_back)` 会把 `$url_back` 拼成 `'?b=' . urlencode($url_back)`（仅在传入时）。`throwLoginOn()` 的 302 分支就是这么把当前 `REQUEST_URI` 的 path 传进去的。
- **`mergeViewData()` 的页眉页脚开关**：只有 `$data['__logined_render_header_footer']`（缺省视为 `true`）为真才渲染头尾文件；渲染结果同时放进 `__view_data.header/footer`（给视图用）与 `__logined_header_file/footer_file`（给 `View::setViewHeaderFooter()` 用，由 `DuckPhp::_Show()` 消费）。
- **自动接管渲染**：`DuckPhp::_Show()` 在 `__use_logined_view_data` 为真、且当前路由调用类实现 `AdminControllerInterface` 时，会自动调用本组件的 `mergeViewData()`；你通常不需要手动调它。
- `canAccess()` 三个参数全为 `null` 时会**临时切到 `App::getLastPhase()`** 去读当前路由的 class/method/PATH_INFO，读完切回原相位；显式传参时不做这件事。
- `id()/name()/data()` 的未登录处理统一走 `throwLoginOn()`：① 配了 `globaladmin_need_login_callback` → 回调 + `exit()`；② 非 Ajax → `Show302(urlForLogin(当前 path))` + `exit()`；③ Ajax（`X-Requested-With: XMLHttpRequest`）→ `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])` + `exit()`。三种都会 `exit()`，所以**调用方拿不到返回值**；想避免打断就用 `check_login = false`。
- `service()` / `log()` / `isSuper()` 继承自 `Admin`，内部都走 `localService()`；`mergeViewData()` 的 `__logined_*` 字段也由父类填，本类只追加页眉页脚。

## 全部选项

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

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化组件：读入 `globaladmin_*` 选项；`admin_provider_enable`（缺省真）为真时把自己包成 `PhaseProxy` 注册到 `Admin::class` 这个键上。

    public function id(bool $check_login = true)
当前管理员 ID：读会话的 `getCurrentAdminId()`；取不到且 `$check_login` 时交给 `throwLoginOn()`（会 302/JSON/回调后 `exit()`）。

    public function name(bool $check_login = true): string
当前管理员名：读会话的 `getCurrentAdminName()`；取不到且 `$check_login` 时交给 `throwLoginOn()`。

    public function data(bool $check_login = true): array
当前管理员数据数组：读会话的 `getCurrentAdmin()`；取不到且 `$check_login` 时交给 `throwLoginOn()`。

    public function localService()
返回本地（当前 Phase）的 `AdminServiceInterface` 实现：执行 `globaladmin_local_service` 回调（键缺失抛异常）。

    public function urlForHome(): string
后台首页 URL：App 的 `url_admin_home` → `globaladmin_url_home` → `'/'`，经 `__url()` 生成。

    public function urlForLogin(?string $url_back = null): string
后台登录 URL：`__url(globaladmin_url_login ?? '/')`；传了 `$url_back` 时追加 `'?b=' . urlencode($url_back)`。

    public function urlForLogout(): string
后台退出 URL：App 的 `url_admin_logout` → `globaladmin_url_logout` → `'/'`。

    public function mergeViewData(array $data): array
把后台视图数据补齐：先跑 `globaladmin_ext_view_data_callback`（若配），再按开关渲染页眉页脚文件并写入 `__view_data.header/footer` 与 `__logined_header_file/footer_file`，最后交给父类 `Admin::mergeViewData()` 填 `__logined_id/name/data/url_home/url_logout`。

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
判断当前管理员能否访问：未登录（`id(false)` 为假）直接 `false`；三个参数全空时取「当前路由的 class / method / PATH_INFO」（会临时切到 `getLastPhase()` 再切回）；最后委托 `localService()->canAccess($id, $url, $class, $method)`。

    public function login(array $post)
登录：触发 `EVENT_ACTION_ADMIN_LOGINING` → `getLoginService()->login($post)` → 会话 `setCurrentAdmin()` → 触发 `EVENT_ACTION_ADMIN_LOGINED`；`globaladmin_is_authed_redirect` 为真时 `Show302(urlForHome())`。

    public function logout()
退出：取当前 id（`id(false)`）→ 触发 `EVENT_ACTION_ADMIN_LOGOUTING` → `getLoginService()->logout($admin_id)` → 会话 `unsetCurrentAdmin()` → 触发 `EVENT_ACTION_ADMIN_LOGOUTED`；`globaladmin_is_authed_redirect` 为真时 `Show302(urlForLogin())`。

### 受保护方法

    protected function run_callback_by_key(string $key, ...$args)
按选项键执行回调：键没配就抛 `DuckPhpSystemException(" need ext options '键名'", -1)`；回调是 `[类名, 方法]` 且 `globaladmin_enable_callback_singleton` 为真时，先把类名换成 `类名::_()`。

    protected function throwLoginOn($flag)
未登录的统一处理（`$flag` 为假时直接返回）：① `globaladmin_need_login_callback` → 回调 + `exit()`；② 非 Ajax → `Show302(urlForLogin(REQUEST_URI 的 path))`；③ Ajax → `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])`；②③ 结尾都 `exit()`。

    protected function getLoginService()
取登录服务实现（`AdminLoginServiceInterface`）：执行 `globaladmin_login_service` 回调。

    protected function getSession()
取会话实现（`AdminSessionInterface`）：执行 `globaladmin_login_session` 回调。

## 相关链接

- [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-Admin.md) — 父类：常量与「默认不可用」的桩实现
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 本组件实现的动作契约
- [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) — 登录/退出的动作契约
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — `globaladmin_local_service` 的契约
- [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) — `globaladmin_login_service` 的契约
- [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) — `globaladmin_login_session` 的契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — `init()` 注册自己时用的跨 Phase 代理
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 用户侧同构组件
