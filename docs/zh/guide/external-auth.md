# 2-9 会话与用户/管理员体系

> 解决什么问题：Session 怎么读写、登录/注册/登出怎么做、权限与后台菜单怎么生成。
> 前置：[第 2-3 章 控制器](controllers.md)、[第 2-8 章 表单与数据验证](validator.md)。预计 20 分钟。
> 示例片段基于 `MyProj` 工程；`demo/src/Controller/Session.php` 是 [SessionTrait](../reference/Foundation-SessionTrait.md) 的最小骨架，可直接参考。

## 最小示例

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
    ];
}
```

```php
// Controller
public function center()
{
    $userId = Helper::UserId();            // 未登录抛 UserException（见下）
    Helper::Show(get_defined_vars(), 'user/center');
}
```

## 机制说明

### Session：SessionTrait + SuperGlobal

`DuckPhp\Foundation\SessionTrait`（源码 `src/Foundation/SessionTrait.php`）给任意类加上带前缀的 Session 读写：

- 首次访问时自动 `session_start()`（经 [`SystemWrapper`](../reference/Core-SystemWrapper.md)），并把应用选项 **`session_prefix`**（隐藏选项，默认空串）缓存为键前缀；
- `get($key, $default)` / `set($key, $value)` / `unset($key)` 读写 `session_prefix . $key`，底层走 [DuckPhp\Core\SuperGlobal](../reference/Core-SuperGlobal.md) 的 `_SessionGet/_SessionSet/_SessionUnset`。

`demo/src/Controller/Session.php` 就是最小骨架：

```php
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\SessionTrait;

class Session
{
    use SessionTrait;
    // 自行加语义化方法，如 setCurrentUser()/getCurrentUser()
}
```

> 多应用同进程时，给每个应用配不同的 `session_prefix` 可避免会话键互相覆盖。

### GlobalUser / GlobalAdmin：回调配置模式

[DuckPhp\GlobalUser\GlobalUser](../reference/GlobalUser-GlobalUser.md) 与 [DuckPhp\GlobalAdmin\GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md) 是「全局用户/管理员组件」：它们实现 [`UserActionInterface`](../reference/GlobalUser-UserActionInterface.md) / [`AdminActionInterface`](../reference/GlobalAdmin-AdminActionInterface.md)，把「当前是谁、站内 URL、视图头尾、权限与日志」集中到一个组件，具体实现用**选项回调**外包给工程类。

两条接入路线（可混用）：

1. **`user_provider` / `admin_provider`**（[`DuckPhp`](../reference/DuckPhp.md) 选项，源码 `src/DuckPhp.php` 第 154–163 行）：填一个工程类名，框架实例化后经 [`PhaseProxy`](../reference/Component-PhaseProxy.md) 挂到 [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) / [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md) 单例位。适合「整个换掉默认组件」的场景。
2. **`user_callback_*` / `admin_callback_*` 系列**：逐项配置回调，保留默认组件的行为（登录/登出流程、视图头尾注入、事件触发等）。

### 选项对照表（以源码 `src/GlobalUser/GlobalUser.php`、`src/GlobalAdmin/GlobalAdmin.php` 的 `$options` 为准）

| 选项（user 侧 / admin 侧） | 默认 | 作用 |
|---|---|---|
| `user_url_home` / `admin_url_home` | `null` | 站内首页 URL（未配回调时用 `__url()` 生成） |
| `user_url_register` | `null` | 注册 URL（**旧名 `user_url_regist` 已更名**） |
| `user_url_login` / `admin_url_login` | `null` | 登录 URL |
| `user_url_logout` / `admin_url_logout` | `null` | 退出 URL |
| `user_view_file_header` / `admin_view_file_header` | `null` | 页头视图文件 |
| `user_view_file_footer` / `admin_view_file_footer` | `null` | 页脚视图文件 |
| `user_enable_callback_singleton` / `admin_enable_callback_singleton` | `true` | 回调为 `[类名, 方法]` 时是否先转 `类名::_()` |
| `user_callback_for_id` / `admin_callback_for_id` | `null` | 取当前 id |
| `user_callback_for_name` / `admin_callback_for_name` | `null` | 取当前名 |
| `user_callback_for_data` / `admin_callback_for_data` | `null` | 取当前数据（数组） |
| `user_callback_for_local_service` / `admin_callback_for_local_service` | `null` | 返回本地 Service 实现 |
| `user_callback_for_add_ext_view_data` / `admin_callback_for_add_ext_view_data` | `null` | 追加视图数据（不设时默认注入 `__logined_id/name/url_logout`） |
| `user_callback_for_login_service` / `admin_callback_for_login_service` | `null` | 登录服务（`register()/login()/logout()` 经它） |
| `user_callback_for_session` / `admin_callback_for_session` | `null` | 会话实现（返回 [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) / [`AdminSessionInterface`](../reference/GlobalAdmin-AdminSessionInterface.md)）；**配置后 `id()/name()` 优先读会话** |
| `user_callback_for_url_for_*` / `admin_callback_for_url_for_*` | `null` | 生成各 URL 的回调（优先于对应 `*_url_*`） |
| `user_loginout_auto_redirect` / `admin_loginout_auto_redirect` | `true` | 登录/登出后自动 302 |
| `user_default_exception_class` / `admin_default_exception_class` | `null` | 会话模式下未登录时抛的异常类（缺省 [`UserException`](../reference/GlobalUser-UserException.md) / [`AdminException`](../reference/GlobalAdmin-AdminException.md)） |

> ⚠️ **旧键名已失效**：`user_callback_get_id` / `user_callback_get_name` / `user_callback_get_data` / `user_callback_get_service`（以及 admin 侧同名）在源码里**不存在**，请用上表的 `user_callback_for_*` / `admin_callback_for_*`。

### 登录 / 注册 / 登出流程

`GlobalUser` 提供 `login($post)` / `register($post)` / `logout()`（源码 `src/GlobalUser/GlobalUser.php` 第 226–264 行），流程：

1. 触发事件（`ACTION_USER_LOGINING` 等，见 [GlobalUser](../reference/GlobalUser-GlobalUser.md) 事件常量）；
2. 调 `user_callback_for_login_service` 指定的登录服务做校验/落库；
3. 调 `user_callback_for_session` 指定的会话实现 `setCurrentUser($user)`；
4. 触发完成事件；若 `user_loginout_auto_redirect` 为真，自动 302 到首页/登录页。

`GlobalAdmin` 只有 `login()` / `logout()`（无注册）。工程侧的控制器动作通常就是「校验输入 → 调 `Helper::User()->login($post)` → 完」。

### 会话实现：UserSessionTrait / AdminSessionTrait

框架自带两个会话 Trait，组合 `SessionTrait` 使用：

```php
namespace MyProj\UserSystem;

use DuckPhp\Foundation\SessionTrait;
use DuckPhp\GlobalUser\UserSessionInterface;
use DuckPhp\GlobalUser\UserSessionTrait;

class UserSession implements UserSessionInterface
{
    use SessionTrait;
    use UserSessionTrait;     // 提供 getCurrentUserId/Name、setCurrentUser 等
}
```

- [DuckPhp\GlobalUser\UserSessionTrait](../reference/GlobalUser-UserSessionTrait.md) 把当前用户存到 Session 键 `user`（数组，含 `id`/`name`）；
- [DuckPhp\GlobalAdmin\AdminSessionTrait](../reference/GlobalAdmin-AdminSessionTrait.md) 同理，键为 `admin`。

### 权限与菜单：Ext\PermissionMenu

[DuckPhp\Ext\PermissionMenu](../reference/Ext-PermissionMenu.md) 用 [DuckPhp\Component\RouteLister](../reference/Component-RouteLister.md) 扫出**后台控制器**（实现了 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 的类）的路由，按注释或元数据生成菜单树：

- **注释模式**：在控制器类/方法上写 `@menu_directory`、`@menu`、`@menu_action`、`@menu_permission` 等（示例见 `tests/data_for_tests/Ext/PermissionMenu/Controller/AdminController.php`）；
- **元数据模式**：控制器实现 [DuckPhp\Ext\PermissionMenuMetaInterface](../reference/Ext-PermissionMenuMetaInterface.md)，`__permissionMenuMeta()` 返回数组接管整表；
- **落盘模式**：`buildAndSaveToConfigJsonFile()` 把树写成配置，运行时 `loadAdminPermissionMenu()` 读回，避免每次请求都扫路由。

`loadAll()` 会把根应用与各子应用的菜单合并成一棵整树（跨相位安全）。菜单文件由隐藏选项 `permission_menu_tree_for_admin` 指定。

### 视图级开关：use_user_view / use_admin_view

`DuckPhp::_Show()`（源码 `src/DuckPhp.php` 第 176–187 行）在渲染前检查两个**隐藏选项**：

- `use_user_view` 为真**且**当前控制器实现 [`UserControllerInterface`](../reference/GlobalUser-UserControllerInterface.md) → 交给 `GlobalUser::_Show()`（自动注入用户头尾与 `__logined_*` 变量）；
- `use_admin_view` 为真**且**当前控制器实现 `AdminControllerInterface` → 交给 `GlobalAdmin::_Show()`。

命中条件有两个，缺一不可：选项打开 + 控制器实现对应接口。配套基类 [Foundation\Controller\UserControllerBase](../reference/Foundation-Controller-UserControllerBase.md) / [Foundation\Controller\AdminControllerBase](../reference/Foundation-Controller-AdminControllerBase.md) 已在构造函数里做了「未登录跳登录页 / Ajax 抛异常」的兜底。

> 这两个选项是**隐藏选项**（不在默认 `$options` 表里声明，见 `src/DuckPhp.php` 第 92–108 行），读写时按普通选项一样用即可；头尾文件是相位可覆盖的视图名，第三方应用也能换掉——见[第 3-5 章 重写与覆盖](overriding.md)。

## 常见写法

### 1. 登录检查与跳转

```php
// Controller
public function profile()
{
    $userId = Helper::UserId(false);        // false：不抛异常，未登录返回 null/0
    if (!$userId) {
        Helper::Show302(Helper::User()->urlForLogin('user/profile'));
        return;
    }
    Helper::Show(get_defined_vars(), 'user/profile');
}
```

### 2. 直接调登录/登出（动作里）

```php
public function login()
{
    if (Helper::POST()) {
        Helper::User()->login(Helper::POST());   // 成功后自动 302 到 user_url_home
    }
    Helper::Show([], 'user/login');
}
public function logout()
{
    Helper::User()->logout();                    // 清会话，302 到 user_url_login
}
```

### 3. 权限检查与日志

```php
// 控制器构造或动作里
Helper::Admin()->canAccess();            // 无参时取当前路由的类/方法/URL
Helper::Admin()->log('修改了配置', 'update', ['key' => 'site_name']);
if (Helper::Admin()->isSuper()) { /* 超管专属 */ }
```

### 4. 批量取用户名

```php
$names = Helper::UserService()->batchGetUsernames([1, 2, 3]);   // [1 => '张三', …]
```

### 5. 未登录异常定制

```php
// App 选项
'user_default_exception_class' => MyProj\System\NoLoginException::class,
```

会话模式（配了 `user_callback_for_session`）下，`Helper::UserId()` 未登录时抛该类；缺省为 `UserException`（admin 侧为 `AdminException`）。异常沿[第 2-11 章](exception.md)的机制处理（比如 302 到登录页）。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| [`DuckPhpSystemException: No GlobalUser Provider`](../reference/Core-DuckPhpSystemException.md) | 没配任何 provider（session / id / name 回调都没配） | 至少配 `user_callback_for_session` 或 `user_callback_for_id`/`name` |
| `need app options 'user_callback_for_xxx'` | 组件走到了未配置的回调键 | 对照上表补齐选项；或改用 `user_provider` 整体替换 |
| 旧代码 `user_callback_get_id` 报错 | 键名已失效 | 改为 `user_callback_for_id`（name/data/local_service 同理） |
| `urlForRegister()` 报「need app options 'user_url_register'」 | 还在用旧键 `user_url_regist` | 改为 `user_url_register` |
| 开了 `use_user_view` 却没走用户头尾 | 控制器没实现 `UserControllerInterface` | 让控制器实现该接口，或继承 [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md) |
| 后台菜单是空的 | 控制器没实现 `AdminControllerInterface`，或没写 `@menu*` 注释 | 实现接口 + 写注释；或实现 [`PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md) |
| 多应用会话互相覆盖 | 各应用 Session 键前缀相同 | 给每个应用配不同的 `session_prefix` |
| `Helper::UserId()` 未登录时行为不对 | 会话模式与非会话模式抛的异常不同 | 会话模式抛 `user_default_exception_class`（默认 `UserException`）；回调模式由你的回调决定 |

## 下一步

- [第 2-10 章 请求生命周期与钩子点](lifecycle.md)：这一次请求框架内部都做了什么。
- [第 2-11 章 异常与错误处理](exception.md)：`UserException` / `AdminException` 怎么被接住、怎么变成跳转或错误页。
- [第 3-5 章 重写与覆盖](overriding.md)：换掉用户/后台视图头尾的覆盖写法。
- 参考手册：[GlobalUser](../reference/GlobalUser-GlobalUser.md)、[GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md)、[Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)、[Foundation\SessionTrait](../reference/Foundation-SessionTrait.md)、[DuckPhp（user_provider/admin_provider）](../reference/DuckPhp.md)
