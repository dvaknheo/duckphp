# 2-19 管理员体系

> 解决什么问题：后台怎么接、后台登录/登出怎么做、`canAccess()` 与操作日志怎么用、后台菜单（权限树）怎么生成。
> 前置：[第 2-9 章 会话](session.md)、[第 2-18 章 用户体系](user.md)、[第 2-3 章 控制器](controllers.md)。预计 20 分钟。
> 本章与[第 2-18 章](user.md)同构，只讲**不同的地方**；机制细节（回调、`PhaseProxy`、未登录处理）不重复。仓库里能跑的实现见 `tests/GlobalAdmin/GlobalAdminTest.php`，跑法：`wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/GlobalAdmin/GlobalAdminTest.php"`。

## 最小示例

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\AdminSystem\MyAdmin;        // extends \DuckPhp\GlobalAdmin\GlobalAdmin，只写这一行也行
use MyProj\AdminSystem\AdminService;   // 实现 AdminServiceInterface + AdminLoginServiceInterface
use MyProj\AdminSystem\AdminSession;   // 实现 AdminSessionInterface（见第 4 节）

class App extends DuckPhp
{
    public $options = [
        'globaladmin_url_home'   => 'admin/dashboard',
        'globaladmin_url_login'  => 'admin/login',
        'globaladmin_url_logout' => 'admin/logout',

        'globaladmin_login_session' => [AdminSession::class, '_'],
        'globaladmin_login_service' => [AdminService::class, '_'],
        'globaladmin_local_service' => [AdminService::class, '_'],

        'ext' => [
            MyAdmin::class => true,        // 把工程侧子类挂成「管理员组件」
        ],
    ];
}
```

```php
// 后台控制器里：权限判断 + 操作日志
public function update()
{
    Helper::Admin()->canAccess();            // 无参：取当前路由的类/方法/URL；没权限由你决定怎么表现
    Helper::Admin()->log('修改了站点配置', 'update', ['key' => 'site_name']);
    Helper::Show(get_defined_vars(), 'admin/config');
}
```

> `Admin::_()` 与 `GlobalAdmin` 的关系、以及应用级 `globaladmin_*` 选项怎么落到组件上，与用户侧逐字相同（[第 2-18 章第 1–2 节](user.md)），已实测。

## 机制说明

### 1. `Admin` 是「键」，`GlobalAdmin` 是实现

[DuckPhp\GlobalAdmin\Admin](../reference/GlobalAdmin-Admin.md) 是桩（没挂实现时抛 `No GlobalAdmin Provider.` / `Need Provider`），[DuckPhp\GlobalAdmin\GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md) 是完整实现，`init()` 里把自己注册到 `Admin::class` 这个键上。调用方一律写 `Admin::_()` / `Helper::Admin()`。

管理员侧的入口：

| 入口 | 说明 |
|---|---|
| `Helper::Admin()` / `Helper::AdminId()` / `Helper::AdminName()` | 当前管理员（`AdminId()` 默认 `check_login=true`，未登录走第 5 节那三条路） |
| `Helper::AdminService()` | 本地 Service（`globaladmin_local_service`），契约 [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md) |
| `Admin::_()->login($post)` / `logout()` | 登录 / 登出（**没有注册**：后台账号由你自建，所以本章没有 `urlForRegister()`） |
| `Admin::_()->canAccess($url, $class, $method)` | 权限判断，参数顺序是 **`$url` 在前**（与 [`AdminActionInterface`](../reference/GlobalAdmin-AdminActionInterface.md) / `AdminServiceInterface` 一致）；不传参时取当前路由上下文 |
| `Admin::_()->log($string, $type, $ext)` | 操作日志（写什么、写哪里由你的实现决定） |
| `Admin::_()->isSuper()` | 是否超管（管理员侧特有，用户侧没有） |

### 2. 选项对照表（以 `src/GlobalAdmin/GlobalAdmin.php` 的 `$options` 为准）

| 选项 | 默认 | 作用 |
|---|---|---|
| `globaladmin_login_session` | `null` | 会话实现（[`AdminSessionInterface`](../reference/GlobalAdmin-AdminSessionInterface.md)）；**必需** |
| `globaladmin_login_service` | `null` | 登录服务（[`AdminLoginServiceInterface`](../reference/GlobalAdmin-AdminLoginServiceInterface.md)）；**必需** |
| `globaladmin_local_service` | `null` | 本地 Service（`canAccess()` / `log()` / `isSuper()`）；**必需** |
| `globaladmin_url_home` | `null` | 后台首页 URL（App 的隐藏选项 `url_admin_home` 优先于它） |
| `globaladmin_url_login` / `globaladmin_url_logout` | `null` | 登录 / 退出 URL（`url_logout` 同样先看 App 的 `url_admin_logout`） |
| `globaladmin_view_file_header` / `globaladmin_view_file_footer` | `null` | 后台页面的头/尾视图文件（见第 4 节） |
| `globaladmin_enable_callback_singleton` | `true` | 回调写成 `[类名, 方法]` 时是否先换成 `类名::_()` 单例 |
| `globaladmin_ext_view_data_callback` | `null` | 追加视图数据的回调（可选） |
| `globaladmin_need_login_callback` | `null` | 「未登录怎么办」的自定义处理（可选） |
| `globaladmin_is_authed_redirect` | `true` | 登录/登出成功后自动 302 |

> 与用户侧的差别只有两处：**没有 `url_register`**，多了 `isSuper()`；其余键名把 `globaluser_` 换成 `globaladmin_` 即可（见[第 2-18 章第 3 节](user.md)）。

### 3. 未登录怎么办

与用户侧同构（[第 2-18 章第 5 节](user.md)）：`id(true)` / `name(true)` / `data(true)` 取不到值时走 `GlobalAdmin::throwLoginOn()` —— 配了 `globaladmin_need_login_callback` 就只回调、非 Ajax 则 `302` 到 `urlForLogin(当前 path)`、Ajax 则输出 `{"error_code":-1,"error_message":"NEED_LOGIN"}`，三条路最后都 `exit()`。想自己接管就用 `check_login = false`。

登录/权限**不用异常类**：错误码在 [`Admin`](../reference/GlobalAdmin-Admin.md) 的常量上（`Admin::EXCEPTION_CODE_ADMIN_NEED_LOGIN`、`Admin::EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION` 等）。

### 4. 后台页面的头尾：还是那三份视图数据

继承 [`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) 的控制器，`initController()` 会自动：

1. `checkInstall(null)` → 没装应用就 302 到安装页并中断；
2. `Helper::Admin()->id(true)` → 未登录按第 3 节处理；
3. `Helper::Admin()->canAccess()` → 为假时调 `onNeedPermission()`（**可重写的钩子**，无参）：非 Ajax `302` 到 `urlForLogin(当前 path)`、Ajax 输出 `{"error_code":-1,"error_message":"NEED_PERMISSION"}`，随后 `exit()`；
4. `assignViewData('__use_logined_view_data', true)` 与 `assignViewData('__use_logined_header_footer_file', true)`。

所以**头尾是自动的**；想自己控制某次渲染就传视图数据（第 2-18 章第 6 节那张表）。头尾文件的值按 `<应用 path>/view/` 解析，并且**相位可覆盖**（[第 3-5 章](overriding.md)）。

### 5. 后台菜单：`Ext\PermissionMenu`

[DuckPhp\Ext\PermissionMenu](../reference/Ext-PermissionMenu.md) 用 [DuckPhp\Ext\RouteLister](../reference/Ext-RouteLister.md) 扫出**后台控制器**（实现 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 的类）的路由，生成菜单/权限树：

| 模式 | 做法 |
|---|---|
| **注释模式** | 在控制器类/方法上写 `@menu_directory`、`@menu`、`@menu_action`、`@menu_permission` 等注释（示例见 `tests/data_for_tests/Ext/PermissionMenu/Controller/AdminController.php`） |
| **元数据模式** | 控制器实现 [`PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md)，`__permissionMenuMeta()` 直接返回整张表 |
| **落盘模式** | `buildAndSaveToConfigJsonFile()` 把树写进配置，运行时 `loadAdminPermissionMenu()` 读回，避免每请求扫路由 |

- `loadAll()` 会把根应用与各子应用的菜单合并成一棵整树（跨相位安全）；
- 菜单文件由隐藏选项 `permission_menu_tree_for_admin` 指定；
- CLI 里可以用 `RouteLister::_()->command_routes()` 看路由表（[第 2-15 章](cli.md)）。

## 常见写法

**① 每个后台动作前判权限、动作后记日志**

```php
public function delete()
{
    $id = (int)Helper::GET('id');
    if (!Helper::Admin()->canAccess()) {        // 无参：取当前路由上下文
        return;                                  // 跳转/报错由基类的 onNeedPermission() 或你自己决定
    }
    MyService::_()->delete($id);
    Helper::Admin()->log("删除了 #{$id}", 'delete', ['id' => $id]);
    Helper::Show302('admin/list');
}
```

**② 换掉「没权限」的表现**（例如 API 里返回 403 JSON，而不是 302）

```php
class MyAdminControllerBase extends AdminControllerBase
{
    protected function onNeedPermission()
    {
        Helper::ShowJson(['error_code' => -2, 'error_message' => 'NEED_PERMISSION']);
    }
}
```

**③ 超管专属入口**

```php
if (Helper::Admin()->isSuper()) {
    // 只有超管能看到的菜单/按钮
}
```

**④ 菜单落盘（大后台推荐）**

```php
$menu = PermissionMenu::_();
$menu->buildAndSaveToConfigJsonFile();       // 部署或定时任务里跑一次
// 运行时
$tree = $menu->loadAdminPermissionMenu();
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `DuckPhpSystemException: No GlobalAdmin Provider.` | `Admin::_()` 是桩：没挂 `ext`，或 `admin_provider_enable` 被关 | 挂 `'ext' => [MyAdmin::class => true]` |
| ` need ext options 'globaladmin_login_session'` | 三个必需回调没配齐 | 配 `globaladmin_login_session` / `globaladmin_login_service` / `globaladmin_local_service` |
| 旧键 `admin_callback_for_*` / `admin_url_*` 没反应 | 那一族键名已不存在（选项族统一成 `globaladmin_*`） | 按第 2 节对照表改名 |
| `canAccess()` 无参时报路由上下文为空 | 不在路由动作里调用（如 CLI / 子相位） | 显式传 `canAccess($url, $class, $method)`（注意 `$url` 在前） |
| 后台页面没有头尾 | 没继承 `AdminControllerBase`，或两个视图数据键没置真 | 继承 `AdminControllerBase`，或自己置 `__use_logined_view_data` + `__use_logined_header_footer_file` |
| 登录后仍回登录页 | 会话里没有管理员（键/前缀不对） | 检查 `AdminSessionTrait` 的会话键 `admin` 与 `session_prefix`（[第 2-9 章](session.md)） |
| 后台菜单是空的 | 控制器没实现 `AdminControllerInterface`，或没写 `@menu*` 注释 | 实现接口 + 写注释；或用 `__permissionMenuMeta()` |
| `Class 'AdminException' not found` | 该类已从源码删除 | 用 `Admin::EXCEPTION_*` 常量 + `throwLoginOn()` 的机制 |

## 下一步

- [第 2-18 章 用户体系](user.md)：本章的同构章（前台那套）。
- [第 2-11 章 异常与错误处理](exception.md)：权限不够、登录失效怎么变成跳转或错误页。
- [第 3-5 章 重写与覆盖](overriding.md)：换掉后台视图头尾。
- [第 2-15 章 命令行与定时任务](cli.md)：用 CLI 跑菜单落盘、看路由表。
- 参考手册：[Admin](../reference/GlobalAdmin-Admin.md)、[GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md)、[AdminActionInterface](../reference/GlobalAdmin-AdminActionInterface.md)、[AdminLoginActionInterface](../reference/GlobalAdmin-AdminLoginActionInterface.md)、[AdminServiceInterface](../reference/GlobalAdmin-AdminServiceInterface.md)、[AdminLoginServiceInterface](../reference/GlobalAdmin-AdminLoginServiceInterface.md)、[AdminSessionTrait](../reference/GlobalAdmin-AdminSessionTrait.md)、[Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)、[Ext\RouteLister](../reference/Ext-RouteLister.md)
