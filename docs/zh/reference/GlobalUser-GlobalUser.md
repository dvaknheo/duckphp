# DuckPhp\GlobalUser\GlobalUser

## 简介

`GlobalUser` 是用户体系的**完整实现**：它继承 `User`（常量与「默认不可用」的桩实现都在父类），实现 `UserLoginActionInterface`，把「当前用户是谁 / 站内 URL / 用户页眉页脚 / 权限与日志 / 注册登录退出」集中到一个组件里。

它自己**不碰数据库也不碰 `$_SESSION`**，而是把三件事外包出去（都通过选项回调）：

1. **会话**（`globaluser_login_session` → `UserSessionInterface`）：`id()/name()/data()` 的唯一数据源；
2. **服务**（`globaluser_local_service` → `UserServiceInterface`）：`canAccess()` / `log()` / `batchGetUsernames()` 的去处；
3. **登录服务**（`globaluser_login_service` → `UserLoginServiceInterface`）：`register()` / `login()` / `logout()` 的业务实现。

`init()` 时会把自己包成 `PhaseProxy` 注册到父类名 `User::class` 这个容器键上（可关，见 `user_provider_enable`），因此全框架（含 `Foundation\Controller\ControllerHelper::User()`、`Foundation\Controller\UserControllerBase`）都通过 `User::_()` 用到它。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`class GlobalUser extends DuckPhp\GlobalUser\User implements UserActionInterface, UserLoginActionInterface`
- 父类：`DuckPhp\GlobalUser\User`（提供 `mergeViewData()` 的登录字段、`service()`/`log()`/`batchGetUsernames()` 的委托，以及全部常量）
- 实现的接口：`UserActionInterface`、`UserLoginActionInterface`
- 使用的 trait：无
- 常量：**本类不声明常量**，全部继承自 `User`（`EVENT_ACTION_USER_*` 6 个、`EVENT_SERVICE_USER_*` 6 个、`EXCEPTION_*` 4 个，见 [User](GlobalUser-User.md)）

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `globaluser_is_authed_redirect` | `true` | `register()`/`login()`/`logout()` 完成后是否自动 302（注册/登录跳 `urlForHome()`、退出跳 `urlForLogin()`）。 |
| `globaluser_url_home` | `null` | 站内首页 URL；未配时取 App 的上下文选项 `url_user_home`，再退回 `'/'`。 |
| `globaluser_url_register` | `null` | 注册页 URL；未配时退回 `'/'`。 |
| `globaluser_url_login` | `null` | 登录页 URL；未配时退回 `'/'`（这是 `throwLoginOn()` 的 302 目标）。 |
| `globaluser_url_logout` | `null` | 退出 URL；未配时取 App 的上下文选项 `url_user_logout`，再退回 `'/'`。 |
| `globaluser_view_file_header` | `null` | 用户页眉视图文件（非空才渲染，结果并入 `__view_data.header`）。 |
| `globaluser_view_file_footer` | `null` | 用户页脚视图文件（非空才渲染，结果并入 `__view_data.footer`）。 |
| `globaluser_enable_callback_singleton` | `true` | 回调是 `[类名, 方法]` 时，是否先把类名换成 `类名::_()` 单例实例。 |
| `globaluser_local_service` | `null` | 返回 `UserServiceInterface` 实现的回调（键缺失时 `localService()` 抛异常）。 |
| `globaluser_login_service` | `null` | 返回 `UserLoginServiceInterface` 实现的回调。 |
| `globaluser_login_session` | `null` | 返回 `UserSessionInterface` 实现的回调。 |
| `globaluser_ext_view_data_callback` | `null` | 追加视图数据的回调（键缺失时**不报错**，跳过即可；与上面三个「必需」回调不同）。 |
| `globaluser_need_login_callback` | `null` | 未登录时的自定义处理；配了就**不会**走默认的 302/JSON，只回调再 `exit()`。 |

## 使用方式

```php
// 应用选项里把三件事挂上（也可以只挂一部分，用不到的键不会报错）
$options = [
    'ext' => [
        \DuckPhp\GlobalUser\GlobalUser::class => true,
    ],
    'globaluser_login_session' => [\MyProject\User\UserSession::class, '_'],
    'globaluser_local_service' => [\MyProject\User\UserService::class, '_'],
    'globaluser_login_service' => [\MyProject\User\UserLoginService::class, '_'],
    'url_user_home'            => '/',
];

// 任何地方读「当前用户」——注意用父类名 User，而不是 GlobalUser
use DuckPhp\GlobalUser\User;

$id    = User::_()->id(true);          // 未登录时按 throwLoginOn() 处理（302/JSON/自定义回调 + exit）
$name  = User::_()->name(false);       // check_login=false：未登录返回空串，不打断
$url   = User::_()->urlForLogin('/order/1');   // 登录后回跳 /order/1 → 生成 '?b=%2Forder%2F1'
if (!User::_()->canAccess()) {         // 缺省参数＝当前路由的 class/method/PATH_INFO
    // 无权限
}
User::_()->register($post);            // 注册：回调登录服务 + 写会话 + 触发事件 +（可选）302
User::_()->login($post);               // 登录
User::_()->logout();                   // 退出
```

## 配置示例

```php
// 让「未登录」不再 302，而是走你自己的逻辑（例如 API 返回 401）
$options['globaluser_need_login_callback'] = function () {
    \DuckPhp\Core\SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'USER_NEED_LOGIN']);
};
// 回调返回后组件会调用 SystemWrapper::exit()，请求到此为止。

// 想给用户页加统一页眉页脚（模板路径相对 path_view 解析）
$options['globaluser_view_file_header'] = 'inc-head';
$options['globaluser_view_file_footer'] = 'inc-foot';
```

## 注意事项

- **必需回调与可选回调**：`globaluser_local_service` / `globaluser_login_service` / `globaluser_login_session` 是「按键取值，缺了就抛」——`run_callback_by_key()` 抛 `DuckPhpSystemException(" need ext options 'globaluser_login_session'", -1)`（注意消息里 `need` 前有一个空格）；而 `globaluser_ext_view_data_callback` / `globaluser_need_login_callback` 用 `isset()` 判过，缺了只是跳过。
- **`user_provider_enable`（隐藏选项）**：`init()` 里 `$context->options['user_provider_enable'] ?? true`，为假时**不**把自己注册到 `User::_()`，于是 `User::_()` 会是父类 `User` 的桩实例（调用即抛）。默认开着。
- **上下文选项优先**：`urlForHome()` / `urlForLogout()` 先读 **App 的** `url_user_home` / `url_user_logout`（这两个是 `DuckPhp` 的隐藏选项，不在本页选项表里），没有再退回组件自己的 `globaluser_url_*`，最后退回 `'/'`；`urlForRegister()` / `urlForLogin()` 不看上下文选项。
- **`$url_back` 与 `$ext`**：四个 `urlFor*()` 都接受 `(?string $url_back = null, ?array $ext = null)`，两者都由内部方法 `buildUrlBackQuery()` 拼成查询串——`$ext` 里的键值直接进查询串，`$url_back` 以 `b` 排在最后（`?b=...`）；两者都为空则不加 `?`。`throwLoginOn()` 的 302 分支就是把当前 `REQUEST_URI` 的 path 当作 `$url_back` 传进去的。
- **`mergeViewData()` 的页眉页脚开关**：只有 `$data['__logined_render_header_footer']`（缺省视为 `true`）为真才渲染头尾文件；渲染结果同时放进 `__view_data.header/footer`（给视图用）与 `__logined_header_file/footer_file`（给 `View::setViewHeaderFooter()` 用，由 `DuckPhp::_Show()` 消费）。
- **自动接管渲染**：`DuckPhp::_Show()` 在 `__use_logined_view_data` 为真、且当前路由调用类实现 `UserControllerInterface` 时，会自动调用本组件的 `mergeViewData()`；你通常不需要手动调它。
- `canAccess()` 三个参数全为 `null` 时会**临时切到 `App::getLastPhase()`** 去读当前路由的 class/method/PATH_INFO，读完切回原相位；显式传参时不做这件事。
- `id()/name()/data()` 的未登录处理统一走 `throwLoginOn()`：① 配了 `globaluser_need_login_callback` → 回调 + `exit()`；② 非 Ajax → `Show302(urlForLogin(当前 path))` + `exit()`；③ Ajax（`X-Requested-With: XMLHttpRequest`）→ `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])` + `exit()`。三种都会 `exit()`，所以**调用方拿不到返回值**；想避免打断就用 `check_login = false`。
- `register()/login()/logout()` 的每一个都会先 `fire()` 事件再干活（`EVENT_ACTION_USER_*`）；服务侧的对应事件（`EVENT_SERVICE_USER_*`）由**你的登录服务**在需要时触发，组件本身不发。
- `logout()` 用的是 `id(false)`：没登录时 id 为 `0`，仍然会照常调用登录服务的 `logout(0)` 与清会话——想避免就自己先判断 `id(false)`。
- `service()` / `log()` / `batchGetUsernames()` 继承自 `User`，内部都走 `localService()`；`mergeViewData()` 的 `__logined_*` 字段也由父类填，本类只追加页眉页脚。

## 全部选项

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

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化组件：读入 `globaluser_*` 选项；`user_provider_enable`（缺省真）为真时把自己包成 `PhaseProxy` 注册到 `User::class` 这个键上。

    public function id(bool $check_login = true)
当前用户 ID：读会话的 `getCurrentUserId()`；取不到且 `$check_login` 时交给 `throwLoginOn()`（会 302/JSON/回调后 `exit()`）。

    public function name(bool $check_login = true): string
当前用户名：读会话的 `getCurrentUserName()`；取不到且 `$check_login` 时交给 `throwLoginOn()`。

    public function data(bool $check_login = true): array
当前用户数据数组：读会话的 `getCurrentUser()`；取不到且 `$check_login` 时交给 `throwLoginOn()`。

    public function localService()
返回本地（当前 Phase）的 `UserServiceInterface` 实现：执行 `globaluser_local_service` 回调（键缺失抛异常）。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
站内首页 URL：App 的 `url_user_home` → `globaluser_url_home` → `'/'`，经 `__url()` 生成，再按 `$url_back`/`$ext` 拼查询串。

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
注册页 URL：`__url(globaluser_url_register ?? '/')`，再按 `$url_back`/`$ext` 拼查询串。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录页 URL：`__url(globaluser_url_login ?? '/')`，再按 `$url_back`/`$ext` 拼查询串。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出 URL：App 的 `url_user_logout` → `globaluser_url_logout` → `'/'`，再按 `$url_back`/`$ext` 拼查询串。

    public function mergeViewData(array $data): array
把用户视图数据补齐：先跑 `globaluser_ext_view_data_callback`（若配），再按开关渲染页眉页脚文件并写入 `__view_data.header/footer` 与 `__logined_header_file/footer_file`，最后交给父类 `User::mergeViewData()` 填 `__logined_id/name/data/url_home/url_logout`。

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
判断当前用户能否访问：未登录（`id(false)` 为假）直接 `false`；三个参数全空时取「当前路由的 class / method / PATH_INFO」（会临时切到 `getLastPhase()` 再切回）；最后委托 `localService()->canAccess($id, $url, $class, $method)`。

    public function batchGetUsernames(array $ids): array
按 ID 批量取用户名：委托 `localService()->batchGetUsernames($ids)`。

    public function register(array $post)
注册：触发 `EVENT_ACTION_USER_REGISTERING` → `getLoginService()->register($post)` → 会话 `setCurrentUser()` → 触发 `EVENT_ACTION_USER_REGISTERED`；`globaluser_is_authed_redirect` 为真时 `Show302(urlForHome())`。

    public function login(array $post)
登录：触发 `EVENT_ACTION_USER_LOGINING` → `getLoginService()->login($post)` → 会话 `setCurrentUser()` → 触发 `EVENT_ACTION_USER_LOGINED`；`globaluser_is_authed_redirect` 为真时 `Show302(urlForHome())`。

    public function logout()
退出：取当前 id（`id(false)`）→ 触发 `EVENT_ACTION_USER_LOGOUTING` → `getLoginService()->logout($user_id)` → 会话 `unsetCurrentUser()` → 触发 `EVENT_ACTION_USER_LOGOUTED`；`globaluser_is_authed_redirect` 为真时 `Show302(urlForLogin())`。

### 受保护方法

    protected function run_callback_by_key(string $key, ...$args)
按选项键执行回调：键没配就抛 `DuckPhpSystemException(" need ext options '键名'", -1)`；回调是 `[类名, 方法]` 且 `globaluser_enable_callback_singleton` 为真时，先把类名换成 `类名::_()`。

    protected function throwLoginOn($flag)
未登录的统一处理（`$flag` 为假时直接返回）：① `globaluser_need_login_callback` → 回调 + `exit()`；② 非 Ajax → `Show302(urlForLogin(REQUEST_URI 的 path))`；③ Ajax → `ShowJson(['error_code' => -1, 'error_message' => 'NEED_LOGIN'])`；②③ 结尾都 `exit()`。

    protected function buildUrlBackQuery(?string $url_back, ?array $ext): string
把 `$ext`（附加查询参数）与 `$url_back`（键名 `b`，排在最后）拼成 `?a=1&b=...` 形式的查询串；两者皆空时返回空串。

    protected function getLoginService()
取登录服务实现（`UserLoginServiceInterface`）：执行 `globaluser_login_service` 回调。

    protected function getSession()
取会话实现（`UserSessionInterface`）：执行 `globaluser_login_session` 回调。

## 相关链接

- [DuckPhp\GlobalUser\User](GlobalUser-User.md) — 父类：常量与「默认不可用」的桩实现
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 本组件实现的动作契约
- [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) — 注册/登录/退出的动作契约
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — `globaluser_local_service` 的契约
- [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) — `globaluser_login_service` 的契约
- [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) — `globaluser_login_session` 的契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — `init()` 注册自己时用的跨 Phase 代理
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 管理员侧同构组件
