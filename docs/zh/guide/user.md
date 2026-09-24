# 2-18 用户体系

> 解决什么问题：前台用户怎么接进来（当前是谁）、登录/注册/登出怎么做、未登录怎么跳、用户页面的头尾怎么来。
> 前置：[第 2-9 章 会话](session.md)、[第 2-3 章 控制器](controllers.md)、[第 2-11 章 异常与错误处理](exception.md)。预计 20 分钟。
> 示例片段基于 `MyProj` 工程；仓库里能跑的最小实现见 `tests/GlobalUser/GlobalUserTest.php`（`UserTestApp` / `UserTestSession` / `UserTestService`），跑法：`wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/GlobalUser/GlobalUserTest.php"`。

## 最小示例

用户体系本身**不碰数据库、不碰 `$_SESSION`**，它把三件事外包给工程类，你只要一个子类 + 三个实现：

| 外包什么 | 选项键 | 契约 |
|---|---|---|
| 当前是谁 | `globaluser_login_session` | [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) |
| 登录/注册/登出 | `globaluser_login_service` | [`UserLoginServiceInterface`](../reference/GlobalUser-UserLoginServiceInterface.md) |
| 权限与日志 | `globaluser_local_service` | [`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md) |

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\UserSystem\MyUser;          // extends \DuckPhp\GlobalUser\GlobalUser，只写这一行也行
use MyProj\UserSystem\UserService;     // 实现 UserServiceInterface + UserLoginServiceInterface
use MyProj\UserSystem\UserSession;     // 实现 UserSessionInterface（见第 4 节）

class App extends DuckPhp
{
    public $options = [
        'globaluser_url_home'     => 'user/center',
        'globaluser_url_login'    => 'user/login',
        'globaluser_url_logout'   => 'user/logout',
        'globaluser_url_register' => 'user/register',

        'globaluser_login_session' => [UserSession::class, '_'],
        'globaluser_login_service' => [UserService::class, '_'],
        'globaluser_local_service' => [UserService::class, '_'],

        'ext' => [
            MyUser::class => true,     // 把工程侧子类挂成「用户组件」
        ],
    ];
}
```

```php
// 控制器里：取当前用户
public function center()
{
    $userId = Helper::UserId();          // 未登录时按「未登录怎么办」处理（见第 5 节）
    Helper::Show(get_defined_vars(), 'user/center');
}
```

> 上面这段（应用级 `globaluser_*` 选项 + `ext` 里的子类）已实测：`User::_()` 拿到的是工程子类的 [`PhaseProxy`](../reference/Component-PhaseProxy.md)，`id()/name()` 读会话、`canAccess()` 走 Service、`login()` 写会话后 302 到 `globaluser_url_home`。

## 机制说明

### 1. `User` 是「键」，`GlobalUser` 是实现

全框架（含 `Helper::User()` 与 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)）读的都是父类名 [DuckPhp\GlobalUser\User](../reference/GlobalUser-User.md) 这个容器键。`User` 自己是**桩**：没挂实现时任何能力调用都直接抛 `DuckPhpSystemException`（`id()`/`name()` 抛 `No GlobalUser Provider.`，其余抛 `Need Provider`），不会静默返回空值。

[DuckPhp\GlobalUser\GlobalUser](../reference/GlobalUser-GlobalUser.md) 是完整实现，`init()` 里把自己经 [`PhaseProxy`](../reference/Component-PhaseProxy.md) 注册到这个键上：

- 应用选项 `user_provider_enable`（默认 `true`）控制这次注册；设为 `false` 就退回桩（等于「明确地没有用户体系」）；
- 所以**调用方一律写 `User::_()` / `Helper::User()`**，不要写 `GlobalUser::_()`——写具体类名会在别的相位/子应用里拿到不是你挂的那个实例。

### 2. 接入方式

只有一条路：写 `class MyUser extends \DuckPhp\GlobalUser\GlobalUser {}`（选项可以写在应用里，也可以写在这个子类的 `$options` 里），然后 `'ext' => [MyUser::class => true]`。

`ext` 的值有三种写法（见[第 4-2 章 开发组件与扩展](custom-component.md)）：

| 写法 | 效果 |
|---|---|
| `MyUser::class => true` | 跟随应用选项（上面最小示例用的就是它） |
| `MyUser::class => ['globaluser_url_login' => 'u/login']` | 只给这个组件这批选项 |
| `GlobalUser::class => true` | 不写子类，直接用框架实现（选项仍写在应用里） |

### 3. 选项对照表（以 `src/GlobalUser/GlobalUser.php` 的 `$options` 为准）

| 选项 | 默认 | 作用 |
|---|---|---|
| `globaluser_login_session` | `null` | 会话实现；**必需**（`id()/name()/data()` 的唯一数据源） |
| `globaluser_login_service` | `null` | 登录服务（注册/登录/登出）；**必需** |
| `globaluser_local_service` | `null` | 本地 Service（`canAccess()` / `log()` / `batchGetUsernames()`）；**必需** |
| `globaluser_url_home` | `null` | 站内首页 URL（`__url()` 生成） |
| `globaluser_url_login` / `globaluser_url_logout` / `globaluser_url_register` | `null` | 登录 / 退出 / 注册 URL |
| `globaluser_view_file_header` / `globaluser_view_file_footer` | `null` | 用户页面的头/尾视图文件（见第 6 节） |
| `globaluser_enable_callback_singleton` | `true` | 回调写成 `[类名, 方法]` 时是否先换成 `类名::_()` 单例 |
| `globaluser_ext_view_data_callback` | `null` | 追加视图数据的回调（可选，没配就跳过） |
| `globaluser_need_login_callback` | `null` | 「未登录怎么办」的自定义处理（可选，见第 5 节） |
| `globaluser_is_authed_redirect` | `true` | 注册/登录/登出成功后自动 302 |

> 三个**必需**键没配时抛的是 `DuckPhpSystemException: need ext options 'globaluser_login_session'`；另两个可选键用 `isset()` 判过，没配只是跳过。

### 4. 会话实现：`UserSessionTrait`

框架自带 [`UserSessionTrait`](../reference/GlobalUser-UserSessionTrait.md)，配上第 2-9 章的 [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) 就是一个完整实现：

```php
namespace MyProj\UserSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;          // 带前缀的会话读写（第 2-9 章）
    use UserSessionTrait;      // getCurrentUserId/Name、getCurrentUser、setCurrentUser、unsetCurrentUser
}
```

它把当前用户存进会话键 **`user`**（数组，含 `id`/`name`）。想用 JWT、Redis、单点登录都行——实现那五个方法即可。

### 5. 未登录怎么办：`throwLoginOn()`

`id(true)` / `name(true)` / `data(true)`（`check_login` 默认 `true`）取不到值时，统一交给 `GlobalUser::throwLoginOn()`，**三选一，三条路最后都会 `exit()`**：

| 条件 | 行为 |
|---|---|
| 配了 `globaluser_need_login_callback` | 跑你的回调，然后 `exit()` |
| 非 Ajax 请求 | `302` 到 `urlForLogin(当前 REQUEST_URI 的 path)`（回跳参数是 `?b=…`），然后 `exit()` |
| Ajax 请求（`X-Requested-With: XMLHttpRequest`） | 输出 `{"error_code":-1,"error_message":"NEED_LOGIN"}`，然后 `exit()` |

因为会 `exit()`，**调用方拿不到返回值**：想自己接管跳转就用 `check_login = false`，自己判断：

```php
$userId = Helper::UserId(false);        // 未登录返回 0，不跳转、不 exit
if (!$userId) {
    Helper::Show302(Helper::User()->urlForLogin('user/profile'));   // 回跳地址自己给
    return;
}
```

登录/权限**不用异常类**：错误码在 [`User`](../reference/GlobalUser-User.md) 的常量上（`User::EXCEPTION_CODE_USER_NEED_LOGIN`、`User::EXCEPTION_MESSAGE_USER_NEED_PERMISSION` 等），未登录由 `throwLoginOn()` 直接处理。

继承 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) 的控制器还会替你兜住「没权限」：`initController()` 里 `checkInstall(null)`（应用没装就 302 到安装页并中断）→ `id(true)` → `canAccess()` 为假时调 `onNeedPermission()`——非 Ajax `302` 到登录页（带 `?b=` 回跳）、Ajax 输出 `{"error_code":-2,"error_message":"NEED_PERMISSION"}`，随后 `exit()`。想换表现就在自己的控制器基类里重写 `onNeedPermission()`（见[第 2-19 章第 4 节](admin.md)的写法）。

### 6. 登录后视图：三份视图数据

用户页面（用户中心、个人资料）要带用户头尾、要在视图里拿到 `__logined_id/__logined_name/__logined_url_logout`。开关是**视图数据**，不是应用选项：

| 视图数据键 | 作用 |
|---|---|
| `__use_logined_view_data` | 为真才走「登录后视图」分支（否则就是普通渲染） |
| `__use_logined_header_footer_file` | 为真才把 `globaluser_view_file_header/footer` 当成页面的头/尾 |
| `__logined_render_header_footer` | 缺省视为真；为假时**不渲染**头尾文件，但仍注入 `__logined_*` |

走这条分支时，是 [`DuckPhp::_Show()`](../reference/DuckPhp.md)（源码 `src/DuckPhp.php` 176–196 行）在做：当前路由的控制器实现 [`UserControllerInterface`](../reference/GlobalUser-UserControllerInterface.md) → 调 `User::_()->mergeViewData()`（注入 `__logined_*`、渲染头尾）；两个接口都不实现 → 回落普通 `_Show()`。

继承 `UserControllerBase` 时**不用手写**，它的 `initController()` 会把前两个键都置真：

```php
Helper::assignViewData('__use_logined_view_data', true);         // 自己的基类里（要手动开时）
Helper::Show(['__use_logined_view_data' => true, 'note' => $n], 'user/profile');  // 只这一次
```

头尾文件的值按 `getOverrideableFile('view', …)` 解析：相对路径落在 **`<应用 path>/view/`** 下（不是 `path_view`），而且**相位可覆盖**——第三个应用想换掉用户页头尾，按[第 3-5 章 重写与覆盖](overriding.md)的覆盖规则放同名文件即可。

## 常见写法

**① 登录检查与跳转**（自己接管，不让它 `exit()`）

```php
public function profile()
{
    $userId = Helper::UserId(false);
    if (!$userId) {
        Helper::Show302(Helper::User()->urlForLogin('user/profile'));
        return;
    }
    Helper::Show(get_defined_vars(), 'user/profile');
}
```

**② 登录 / 注册 / 登出动作**

```php
public function login()
{
    if (Helper::POST()) {
        Helper::User()->login(Helper::POST());      // 校验+落库交给登录服务；成功后 302 到 globaluser_url_home
    }
    Helper::Show([], 'user/login');
}
public function logout()
{
    Helper::User()->logout();                        // 清会话，302 到 globaluser_url_login
}
```

**③ 自定义「未登录」的表现**（例如 API 返回 401，而不是 302 到登录页）

```php
'globaluser_need_login_callback' => function () {
    SystemWrapper::_()->_header('HTTP/1.1 401 Unauthorized', true, 401);
    echo json_encode(['error' => 'USER_NEED_LOGIN']);
},
// 回调返回后组件会 exit()，请求到此为止
```

**④ 判权限、批量取用户名**

```php
if (!Helper::User()->canAccess()) {      // 无参：取当前路由的类/方法/URL
    // 无权限
}
$names = Helper::UserService()->batchGetUsernames([1, 2, 3]);   // 走 local_service
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `DuckPhpSystemException: No GlobalUser Provider.` | `User::_()` 是桩：没挂 `ext`，或 `user_provider_enable` 被关 | 挂 `'ext' => [MyUser::class => true]`（见第 2 节） |
| ` need ext options 'globaluser_login_session'` | 三个必需回调没配齐 | 配 `globaluser_login_session` / `globaluser_login_service` / `globaluser_local_service` |
| 旧键 `user_callback_for_*` / `user_url_*` 没反应 | 那一族键名已不存在（选项族统一成 `globaluser_*`） | 按第 3 节对照表改名 |
| 未登录时页面直接 302/JSON，后面代码不执行 | `throwLoginOn()` 结尾是 `exit()` | 用 `check_login = false` 自己判断（第 5 节） |
| `Helper::UserId()` 未登录返回 0 却没跳转 | 用了 `check_login = false` | 想要默认跳转就传 `true`（默认） |
| 页面没有用户头尾 | 控制器没实现 `UserControllerInterface`，或 `__use_logined_header_footer_file` 没置真 | 继承 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)，或两个视图数据键都置真 |
| 头尾文件找不到 | 值按 `<应用 path>/view/` 解析，不是 `path_view` | 用相对 `<path>/view/` 的名字，或给绝对路径 |
| `Class 'UserException' not found` | 该类已从源码删除 | 用 `User::EXCEPTION_*` 常量 + `throwLoginOn()` 的机制 |

## 下一步

- [第 2-19 章 管理员体系](admin.md)：后台那套（登录、`canAccess`、菜单），与本章同构。
- [第 2-11 章 异常与错误处理](exception.md)：权限不够、登录失效怎么变成跳转或错误页。
- [第 3-5 章 重写与覆盖](overriding.md)：换掉用户视图头尾。
- [第 2-12 章 事件系统](events.md)：`EVENT_ACTION_USER_*`（注册/登录/登出前后）怎么监听。
- 参考手册：[User](../reference/GlobalUser-User.md)、[GlobalUser](../reference/GlobalUser-GlobalUser.md)、[UserActionInterface](../reference/GlobalUser-UserActionInterface.md)、[UserLoginActionInterface](../reference/GlobalUser-UserLoginActionInterface.md)、[UserServiceInterface](../reference/GlobalUser-UserServiceInterface.md)、[UserLoginServiceInterface](../reference/GlobalUser-UserLoginServiceInterface.md)、[UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md)
