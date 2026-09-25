# 4-13 实现管理员系统

> 解决什么问题：让[第 2-20 章 使用管理员系统](admin.md)里那些 `Helper::Admin*()` 真的有东西可用——你提供「会话 / 登录服务 / 本地服务」三件实现，再把组件挂进应用。
> 前置：[第 2-20 章](admin.md)、[第 4-12 章 实现用户系统](impl-user.md)（两套实现同构，本章只讲不同的地方）、[第 2-11 章 会话](session.md)。预计 20 分钟。
> 可跑资产：`tests/GlobalAdmin/GlobalAdminTest.php`（`FakeAdminApp` / `FakeAdminSession` / `FakeAdminLoginService` / `FakeAdminService`）、`tests/Foundation/Controller/AdminControllerBaseTest.php`。

## 最小可跑接入

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\AdminSystem\MyAdmin;        // extends \DuckPhp\GlobalAdmin\GlobalAdmin
use MyProj\AdminSystem\AdminService;   // 实现 AdminServiceInterface + AdminLoginServiceInterface
use MyProj\AdminSystem\AdminSession;   // 实现 AdminSessionInterface

class App extends DuckPhp
{
    public $options = [
        'globaladmin_url_home'   => 'admin/dashboard',
        'globaladmin_url_login'  => 'admin/login',
        'globaladmin_url_logout' => 'admin/logout',

        'globaladmin_login_session' => [AdminSession::class, '_'],   // 当前是谁
        'globaladmin_login_service' => [AdminService::class, '_'],   // 登录/登出
        'globaladmin_local_service' => [AdminService::class, '_'],   // canAccess/log/isSuper

        'ext' => [
            MyAdmin::class => true,
        ],
    ];
}
```

```php
namespace MyProj\AdminSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\AdminSessionTrait;

class AdminSession implements AdminSessionInterface
{
    use SessionTrait;          // 带前缀的会话读写（第 2-11 章）
    use AdminSessionTrait;     // getCurrentAdminId/Name、getCurrentAdmin、setCurrentAdmin、unsetCurrentAdmin
}
```

```php
namespace MyProj\AdminSystem;

use DuckPhp\GlobalAdmin\AdminLoginServiceInterface;
use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminService implements AdminServiceInterface, AdminLoginServiceInterface
{
    public function canAccess($admin_id, ?string $url, string $class, string $method): bool
    {
        return MyRoleModel::_()->can($admin_id, $url, $class, $method);   // 你的权限规则
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        MyAdminLogModel::_()->add($admin_id, $string, $type, $ext);
    }
    public function isSuper($admin_id): bool
    {
        return $admin_id === 1;                     // 你的超管判定；用户侧没有这个方法
    }
    public function login(array $post)
    {
        $admin = MyAdminModel::_()->findByLogin($post['username'], $post['password']);
        return $admin ?: [];                        // 组件会把返回值原样写进会话（见第 2 节）
    }
    public function logout($id)
    {
    }
}
```

> 管理员侧**没有注册**：后台账号由你自建（在安装流程里、在 CLI 命令里、或直接插入数据），所以既没有 `register()`，也没有 `globaladmin_url_register`。

## 1. 与用户系统的差异清单

| 项 | 用户系统（[第 4-12 章](impl-user.md)） | 管理员系统 |
|---|---|---|
| 容器键 / 实现 | `User` / `GlobalUser` | `Admin` / `GlobalAdmin` |
| 选项前缀 | `globaluser_` | `globaladmin_` |
| 会话契约 | [`UserSessionInterface`](../reference/GlobalUser-UserSessionInterface.md) + `UserSessionTrait`（会话键 `user`） | [`AdminSessionInterface`](../reference/GlobalAdmin-AdminSessionInterface.md) + `AdminSessionTrait`（会话键 **`admin`**） |
| 登录服务 | [`UserLoginServiceInterface`](../reference/GlobalUser-UserLoginServiceInterface.md)：`register()` / `login()` / `logout()` | [`AdminLoginServiceInterface`](../reference/GlobalAdmin-AdminLoginServiceInterface.md)：`login()` / `logout()` |
| 本地服务 | [`UserServiceInterface`](../reference/GlobalUser-UserServiceInterface.md)：`canAccess()` / `log()` / `batchGetUsernames()` | [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md)：`canAccess()` / `log()` / **`isSuper()`** |
| URL 选项 | `url_home` / `url_login` / `url_logout` / `url_register` | `url_home` / `url_login` / `url_logout`（无注册） |
| 应用级覆盖选项 | `url_user_home` / `url_user_logout` | `url_admin_home` / `url_admin_logout` |
| 事件常量 | `User::EVENT_ACTION_USER_*` / `EVENT_SERVICE_USER_*` | `Admin::EVENT_ACTION_ADMIN_*` / `EVENT_SERVICE_ADMIN_*`（[第 2-13 章第 3 节](events.md)） |
| 后台专属 | — | 控制器实现 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 后会被 [`PermissionMenu`](../reference/Ext-PermissionMenu.md) 扫进菜单（[第 2-20 章第 5 节](admin.md)） |

错误码常量同样在顶层类上：[`Admin`](../reference/GlobalAdmin-Admin.md) 的 `EXCEPTION_CODE_ADMIN_NEED_LOGIN`、`EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION` 等。

## 2. `globaladmin_*` 选项

| 选项 | 默认 | 作用 |
|---|---|---|
| `globaladmin_login_session` | `null` | 会话实现；**必需** |
| `globaladmin_login_service` | `null` | 登录服务（登录/登出）；**必需** |
| `globaladmin_local_service` | `null` | 本地 Service（`canAccess()`/`log()`/`isSuper()`）；**必需** |
| `globaladmin_url_home` / `globaladmin_url_login` / `globaladmin_url_logout` | `null` | 后台首页 / 登录 / 退出 URL（App 的 `url_admin_home`、`url_admin_logout` 优先于前两者对应项） |
| `globaladmin_view_file_header` / `globaladmin_view_file_footer` | `null` | 后台页面的头/尾视图文件（解析规则同用户侧：相对 `<应用 path>/view/`，相位可覆盖） |
| `globaladmin_enable_callback_singleton` | `true` | 回调写成 `[类名, 方法]` 时先换成 `类名::_()` 单例 |
| `globaladmin_ext_view_data_callback` | `null` | 追加视图数据的回调（可选） |
| `globaladmin_need_login_callback` | `null` | 「未登录怎么办」的自定义处理（可选） |
| `globaladmin_is_authed_redirect` | `true` | 登录/登出成功后自动 302 |

`ext` 的三种写法、`admin_provider_enable`（默认 `true`，关掉就退回桩）与用户侧逐字相同，见[第 4-12 章第 1 节](impl-user.md)。

## 3. 登录服务：`AdminLoginServiceInterface`

| 方法 | 被谁调用 | 返回什么 |
|---|---|---|
| `login(array $post)` | `Helper::Admin()->login($post)` | 管理员数组（会被 `setCurrentAdmin()` 写进会话） |
| `logout($id)` | `Helper::Admin()->logout()`（`$id` 是 `id(false)` 的结果） | 无 |

跟上文用户侧一样：**组件不判断「登录成功没有」**，它把 `login()` 的返回值原样写进会话，再发完成事件、再按 `globaladmin_is_authed_redirect` 302；「账号密码不对」的表现由你的服务决定（返回空数组最省事）。

## 4. 后端专属：`isSuper()` 与 `onNeedPermission()`

- `isSuper()`：「这个管理员是不是超管」，管理员侧才有；每次调用都直接问你的 [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md) `isSuper($admin_id)`，框架不缓存也不替你判断。想在菜单/按钮上做「只有超管可见」，用它（[第 2-20 章第 3 节](admin.md)）。
- `onNeedPermission()`：**控制器的兜底钩子**。继承 [`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) 的控制器在 `canAccess()` 为假时会调它，默认实现是「非 Ajax 302 到登录页、Ajax 出 `{"error_code":-1,"error_message":"NEED_PERMISSION"}`」。要实现「返回 403 JSON」「记一条越权日志」这类行为，在自己的控制器基类里**重写这个方法**（无参，不要改签名）。

```php
namespace MyProj\AdminSystem;

use DuckPhp\Foundation\Controller\AdminControllerBase;

class AdminBaseController extends AdminControllerBase
{
    protected function onNeedPermission()
    {
        MyAuditLog::_()->warn('越权访问', ['admin' => Helper::AdminId(false)]);
        parent::onNeedPermission();          // 保留默认的 302 / JSON 行为
    }
}
```

## 5. 和 `PermissionMenu` 的配合

菜单是「用」侧的能力（[第 2-20 章第 5 节](admin.md)），实现侧只需要记住契约：**控制器实现 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 才会被扫进菜单**（该接口是空标记接口）。所以后台控制器的写法就两种：

- 继承 `AdminControllerBase`（父类已经 `implements AdminControllerInterface`）；
- 或者自己 `implements AdminControllerInterface`。

## 6. 怎么验证自己接对了

```bash
wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/GlobalAdmin/GlobalAdminTest.php"
```

自检四条与用户侧同构：`Helper::AdminId(false)` 未登录返回 `0`；`Helper::Admin()->isSuper()` 走你的 Service；`Helper::Admin()->login($post)` 后 `Helper::AdminId()` 有值且 302 到 `globaladmin_url_home`；把 `ext` 里那行注释掉，`Helper::AdminId()` 抛 `DuckPhpSystemException: No GlobalAdmin Provider.`。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| ` need ext options 'globaladmin_login_session'` | 三个必需回调没配齐 | 按第 2 节对照表补齐 |
| `DuckPhpSystemException: No GlobalAdmin Provider.` | `Admin::_()` 是桩：`ext` 没挂，或 `admin_provider_enable` 被关 | 挂 `'ext' => [MyAdmin::class => true]` |
| 会话里有管理员，`Helper::AdminId()` 还是 0 | 会话键/前缀与 `AdminSessionTrait` 不一致 | 用 `AdminSessionTrait`（键 `admin`），或让你的实现返回正确的 `id` |
| 后台菜单扫不到自己写的控制器 | 控制器没实现 `AdminControllerInterface` | 继承 `AdminControllerBase`，或显式 `implements` |
| 越权时页面还是默认的 302/JSON | 重写 `onNeedPermission()` 时忘了 `parent::` 或没生效 | 在**自己的控制器基类**里重写（不是控制器动作里） |

## 下一步

- [第 4-12 章 实现用户系统](impl-user.md)：前台那套的同构实现。
- [第 2-20 章 使用管理员系统](admin.md)：本章面向调用方的那一半。
- 参考手册：[GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md)、[Admin](../reference/GlobalAdmin-Admin.md)、[AdminLoginActionInterface](../reference/GlobalAdmin-AdminLoginActionInterface.md)、[AdminSessionInterface](../reference/GlobalAdmin-AdminSessionInterface.md)、[AdminServiceInterface](../reference/GlobalAdmin-AdminServiceInterface.md)、[AdminLoginServiceInterface](../reference/GlobalAdmin-AdminLoginServiceInterface.md)、[AdminSessionTrait](../reference/GlobalAdmin-AdminSessionTrait.md)、[Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)
