# 2-18 用户体系

> 解决什么问题：前台用户怎么接进来（当前是谁）、登录/注册/登出怎么做、未登录怎么跳、用户页面的头尾怎么来。
> 前置：[第 2-9 章 会话](session.md)、[第 2-3 章 控制器](controllers.md)、[第 2-11 章 异常与错误处理](exception.md)。预计 20 分钟。
> 示例片段基于 `MyProj` 工程；测试里的最小实现见 `tests/GlobalUser/GlobalUserTest.php` 的 `MyUser`/`MyUserAction`。

//TODO（参考手册同步轮 2026-09-24 记：本轮只同步了 reference，本章未改）：本章多处 API 已随 `doced..HEAD` 的源码改动失效，下次改本章时逐条按源码重写：
//  · 选项族整体改名：`user_callback_for_*` / `user_url_*` / `user_loginout_auto_redirect` / `user_enable` / `user_view_file_*` → **`globaluser_*`**（`globaluser_login_session`、`globaluser_local_service`、`globaluser_login_service`、`globaluser_ext_view_data_callback`、`globaluser_need_login_callback`、`globaluser_url_{home,register,login,logout}`、`globaluser_is_authed_redirect`、`globaluser_view_file_{header,footer}`、`globaluser_enable_callback_singleton`），详见 [GlobalUser](../reference/GlobalUser-GlobalUser.md)。
//  · 调用侧：现在是 [`User`](../reference/GlobalUser-User.md)（`GlobalUser` 只是它的完整实现），一律 `User::_()` 或 `Helper::User()/UserId()/UserName()`；不要再写 `GlobalUser::_()`。
//  · `UserException` 类**已从源码删除**（本章 2 处链接指向已删页 `GlobalUser-UserException.md`）：异常码/消息改用 `User::EXCEPTION_CODE_USER_NEED_LOGIN`、`User::EXCEPTION_MESSAGE_USER_NEED_PERMISSION` 这类常量；**未登录不再抛异常**，而是走 `throwLoginOn()`——配了 `globaluser_need_login_callback` 就回调、非 Ajax 则 `302` 到 `urlForLogin(当前 path)`、Ajax 则输出 `{"error_code":-1,"error_message":"NEED_LOGIN"}`，三条路最后都 `exit()`。
//  · 视图级开关改名：`__logined_enable_view` → `__use_logined_view_data`，`__logined_enable_header_footer` → `__use_logined_header_footer_file`；另有 `__logined_render_header_footer`（缺省视为真）决定要不要渲染头尾文件。
//  · `UserSessionTrait` 与会话键 `user` 未变，但 `user_callback_for_session` 这个键名已不存在，改成 `globaluser_login_session`。

## 最小示例

一个工程类继承 [`DuckPhp\GlobalUser\GlobalUser`](../reference/GlobalUser-GlobalUser.md)，把「当前是谁」「登录服务」「会话实现」用**回调**接上；再在 `App` 的 `ext` 里挂上去：

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\UserSystem\UserAction;
use MyProj\UserSystem\UserService;
use MyProj\UserSystem\UserSession;

class App extends DuckPhp
{
    public $options = [
        'user_url_login'    => 'user/login',
        'user_url_logout'   => 'user/logout',
        'user_url_home'     => 'user/center',
        'user_url_register' => 'user/register',

        'user_callback_for_session'       => [UserSession::class, '_'],
        'user_callback_for_login_service' => [UserService::class, '_'],
        'user_callback_for_local_service' => [UserService::class, '_'],
        'user_callback_for_id'            => [UserAction::class, 'id'],
        'user_callback_for_name'          => [UserAction::class, 'name'],

        'ext' => [
            MyUser::class => true,          // 把工程侧的 GlobalUser 子类挂成「用户组件」
        ],
    ];
}
```

```php
// 控制器里：取当前用户
public function center()
{
    $userId = Helper::UserId();            // 未登录抛 UserException（见下）
    Helper::Show(get_defined_vars(), 'user/center');
}
```

## 机制说明

### 1. GlobalUser：一个组件 + 一组回调

[DuckPhp\GlobalUser\GlobalUser](../reference/GlobalUser-GlobalUser.md) 实现 [`UserActionInterface`](../reference/GlobalUser-UserActionInterface.md) 与 [`UserLoginActionInterface`](../reference/GlobalUser-UserLoginActionInterface.md)，把「当前是谁、站内 URL、视图头尾、登录/注册/登出流程」集中在一个组件里；**具体实现用选项回调外包给工程类**。

- `Helper::User()` / `Helper::UserId()` / `Helper::UserName()` / `Helper::UserService()` 都进到它；
- 默认它在 `ext` 里是**关闭**的（`src/DuckPhp.php` 的 `common_options` 里 `GlobalUser::class => EXT_DISABLE`），要显式挂上工程侧的子类才会启用。

**接入方式（现在只有这一条路）**：写一个 `class MyUser extends \DuckPhp\GlobalUser\GlobalUser {}`（配好 `user_callback_for_*` 选项），然后在应用里 `'ext' => [MyUser::class => true]`。它的 `init()` 会把自己注册成 `GlobalUser` 相位的实现（内部经 [`PhaseProxy`](../reference/Component-PhaseProxy.md) 包装），于是 `Helper::User()` 拿到的就是**你的**类。

> ⚠️ 旧文档里的 `user_provider` 选项**在源码里已经不存在**（`src/DuckPhp.php` 里只剩注释）。另有应用选项 `user_provider_enable`（默认 `true`）用来整体关掉上面那次自我注册；`user_enable` 是组件自身 `$options` 里的开关。

### 2. 选项对照表（以 `src/GlobalUser/GlobalUser.php` 的 `$options` 为准）

| 选项                                                                    | 默认     | 作用                                                                                                                                     |
| --------------------------------------------------------------------- | ------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| `user_url_home`                                                       | `null` | 站内首页 URL（未配回调时用 `__url()` 生成）                                                                                                          |
| `user_url_login` / `user_url_logout`                                  | `null` | 登录 / 退出 URL                                                                                                                            |
| `user_url_register`                                                   | `null` | 注册 URL（旧名 `user_url_regist` 已更名）                                                                                                       |
| `user_view_file_header` / `user_view_file_footer`                     | `null` | 用户页面头/尾视图文件                                                                                                                            |
| `user_enable`                                                         | `true` | 组件自身的启用开关                                                                                                                              |
| `user_enable_callback_singleton`                                      | `true` | 回调写成 `[类名, 方法]` 时是否先转成 `类名::_()`                                                                                                       |
| `user_callback_for_id` / `_for_name` / `_for_data`                    | `null` | 取当前 id / 名字 / 整个数据数组                                                                                                                   |
| `user_callback_for_local_service`                                     | `null` | 返回本地 Service 实现（`Helper::UserService()` 用它）                                                                                            |
| `user_callback_for_login_service`                                     | `null` | 登录服务（服务侧契约 [`UserLoginServiceInterface`](../reference/GlobalUser-UserLoginServiceInterface.md)）：`register()/login()/logout()` 经它做校验与落库 |
| `user_callback_for_session`                                           | `null` | 会话实现（[`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md)）；**配了它，`id()/name()` 优先读会话**                            |
| `user_callback_for_add_ext_view_data`                                 | `null` | 追加视图数据                                                                                                                                 |
| `user_callback_for_url_for_home` / `_login` / `_logout` / `_register` | `null` | 生成各 URL 的回调（优先于对应 `user_url_*`）                                                                                                        |
| `user_loginout_auto_redirect`                                         | `true` | 登录/登出成功后自动 302                                                                                                                         |

> ⚠️ **旧键名已失效**：`user_callback_get_id` / `_get_name` / `_get_data` / `_get_service` 在源码里**不存在**，请用 `user_callback_for_*`。

### 3. 登录 / 注册 / 登出流程

`GlobalUser::login($post)` / `register($post)` / `logout()`（源码 `src/GlobalUser/GlobalUser.php` 第 256 / 268 / 279 行）的流程：

1. 触发事件（`EVENT_ACTION_USER_LOGINING` 等，见[第 2-12 章 事件系统](events.md)与 [GlobalUser 常量表](../reference/GlobalUser-GlobalUser.md)）；
2. 调 `user_callback_for_login_service` 指定的登录服务做校验/落库；
3. 调 `user_callback_for_session` 指定的会话实现 `setCurrentUser($user)`；
4. 触发完成事件；`user_loginout_auto_redirect` 为真时自动 302 到 `user_url_home` / `user_url_login`。

工程侧的动作通常就是「校验输入 → 调 `Helper::User()->login($post)` → 完」。

### 4. 会话实现：UserSessionTrait

用户体系自己不存会话，而是要求你给一个实现 [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) 的类，框架自带 [`UserSessionTrait`](../reference/GlobalUser-UserSessionTrait.md) 帮你写：

```php
namespace MyProj\UserSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;          // 带前缀的会话读写（第 2-9 章）
    use UserSessionTrait;      // getCurrentUserId/Name、setCurrentUser、getCurrentUser…
}
```

`UserSessionTrait` 把当前用户存进会话键 **`user`**（数组，含 `id`/`name`）；配了 `globaluser_login_session` 之后，`Helper::UserId()` / `Helper::UserName()` 优先读会话，未登录时走 `throwLoginOn()`（旧文写的是「抛 `UserException`」，该类已删除；`check_login=false` 时返回 `0`/空）。

### 5. 未登录怎么办

- **异常**：会话模式下，`Helper::UserId()`（`$check_login = true`，默认）在未登录时抛 `UserException`（常量 `MESSAGE_NEED_LOGIN`/`CODE_NEED_LOGIN`）；这个类名是写死的，**没有** 可配的 `user_default_exception_class`（旧文档里的那个选项源码里不存在）。想换异常就换 provider 类或在回调里自己抛。
- **跳转/输出**：继承 [`Foundation\Controller\UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) 的控制器，`initController()` 会替你兜底：不能访问时 Ajax 抛 `UserException`、非 Ajax `302` 到登录页。
- 异常怎么被接住、怎么变成错误页见[第 2-11 章](exception.md)。

### 6. 登录后视图：`__logined_enable_view`

用户页面（用户中心、个人资料）通常要带用户头尾、要注入 `__logined_id/__logined_name/__logined_url_logout`。开关是一份**视图数据**：

- `__logined_enable_view` 出现在 `_Show()` 的 `$data` 或 `View::_()->data` 里且为真时，`DuckPhp::_Show()`（源码 `src/DuckPhp.php` 第 168–183 行）才走「登录后视图」分支；
- 当前路由的控制器实现 [`UserControllerInterface`](../reference/GlobalUser-UserControllerInterface.md) → 交给 `GlobalUser::_Show()`（渲染 `user_view_file_header/footer`、注入 `__logined_*`）；
- 两个接口都不实现 → 回落父类 `_Show()`，就是普通渲染。

继承 `UserControllerBase` 时**不用手写**：它的 `initController()` 会 `Helper::assignViewData('__logined_enable_view', true)`，并把 `__logined_enable_header_footer` 也置真（决定要不要把 `user_view_file_header/footer` 套到页面上）。要手动开或只对某次渲染开：

```php
Helper::assignViewData('__logined_enable_view', true);           // 全控制器（自己的基类里）
Helper::Show(['__logined_enable_view' => true, 'note' => $n], 'user/profile');  // 只这一次
```

> 旧选项 `use_user_view` **已失效**（源码只剩注释行）。同理的管理员侧见[第 2-19 章](admin.md)；头尾文件是**相位可覆盖**的视图名，见[第 3-5 章](overriding.md)。

## 常见写法

**① 登录检查与跳转**

```php
public function profile()
{
    $userId = Helper::UserId(false);        // false：不抛异常，未登录返回 0/null
    if (!$userId) {
        Helper::Show302(Helper::User()->urlForLogin('user/profile'));
        return;
    }
    Helper::Show(get_defined_vars(), 'user/profile');
}
```

**② 登录 / 登出动作**

```php
public function login()
{
    if (Helper::POST()) {
        Helper::User()->login(Helper::POST());      // 成功后自动 302 到 user_url_home
    }
    Helper::Show([], 'user/login');
}
public function logout()
{
    Helper::User()->logout();                        // 清会话，302 到 user_url_login
}
```

**③ 批量取用户名（走 Service）**

```php
$names = Helper::UserService()->batchGetUsernames([1, 2, 3]);   // [1 => '张三', …]
```

**④ 让 `id()/name()` 走会话**

```php
'user_callback_for_session' => [MyProj\UserSystem\UserSession::class, '_'],
```

## 常见错误

| 现象                                                         | 原因                                                          | 改法                                                                                         |
| ---------------------------------------------------------- | ----------------------------------------------------------- | ------------------------------------------------------------------------------------------ |
| `DuckPhpSystemException: No GlobalUser Provider.`          | 没配任何回调（session / id / name 都没有）                             | 至少配 `user_callback_for_session` 或 `user_callback_for_id`+`_name`                           |
| `need app options 'user_callback_for_xxx'`                 | 组件走到了未配置的回调键                                                | 对照选项表补齐；或改用自己的 provider 类                                                                  |
| 旧代码 `user_callback_get_id` 报错                              | 键名已失效                                                       | 改成 `user_callback_for_id`（name/data/local_service 同理）                                      |
| `user_provider` 选项没反应                                      | 该选项源码里已不存在                                                  | 用 `'ext' => [MyUser::class => true]` 挂工程侧子类                                                |
| `urlForRegister()` 报「need app options 'user_url_register'」 | 还在用旧键 `user_url_regist`                                     | 改成 `user_url_register`                                                                     |
| 开了 `__logined_enable_view` 却没走用户头尾                         | 控制器没实现 `UserControllerInterface`（或没继承 `UserControllerBase`） | 实现该接口，或继承 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) |
| `Helper::UserId()` 未登录时行为不对                                | 会话模式与非会话模式不同                                                | 会话模式抛 `UserException`；回调模式由你的回调决定（`check_login=false` 时不抛）                                 |

## 下一步

- [第 2-19 章 管理员体系](admin.md)：后台那套（登录、`canAccess`、菜单）。
- [第 2-11 章 异常与错误处理](exception.md)：登录/权限异常现在怎么被接住、怎么变成跳转或错误页（旧文里的 `UserException` 已删除）。
- [第 3-5 章 重写与覆盖](overriding.md)：换掉用户视图头尾。
- 参考手册：[GlobalUser](../reference/GlobalUser-GlobalUser.md)、[User](../reference/GlobalUser-User.md)、[UserActionInterface](../reference/GlobalUser-UserActionInterface.md)、[UserLoginActionInterface](../reference/GlobalUser-UserLoginActionInterface.md)、[UserServiceInterface](../reference/GlobalUser-UserServiceInterface.md)、[UserLoginServiceInterface](../reference/GlobalUser-UserLoginServiceInterface.md)、[UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md)