# 2-19 管理员体系

> 解决什么问题：后台怎么接、后台登录/登出怎么做、`canAccess()` 与操作日志怎么用、后台菜单（权限树）怎么生成。
> 前置：[第 2-9 章 会话](session.md)、[第 2-18 章 用户体系](user.md)、[第 2-3 章 控制器](controllers.md)。预计 20 分钟。
> 示例片段基于 `MyProj` 工程；菜单的完整示例见 `tests/data_for_tests/Ext/PermissionMenu/Controller/AdminController.php`。

//TODO（参考手册同步轮 2026-09-24 记：本轮只同步了 reference，本章未改）：本章多处 API 已随 `doced..HEAD` 的源码改动失效，下次改本章时逐条按源码重写：
//  · 选项族整体改名：`admin_callback_for_*` / `admin_url_*` / `admin_loginout_auto_redirect` / `admin_view_file_*` → **`globaladmin_*`**（`globaladmin_login_session`、`globaladmin_local_service`、`globaladmin_login_service`、`globaladmin_ext_view_data_callback`、`globaladmin_need_login_callback`、`globaladmin_url_{home,login,logout}`、`globaladmin_is_authed_redirect`、`globaladmin_view_file_{header,footer}`、`globaladmin_enable_callback_singleton`），详见 [GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md)。
//  · 调用侧：现在是 [`Admin`](../reference/GlobalAdmin-Admin.md)（`GlobalAdmin` 只是它的完整实现），一律 `Admin::_()` 或 `Helper::Admin()/AdminId()/AdminName()`；不要再写 `GlobalAdmin::_()`。
//  · `AdminException` 类**已从源码删除**（本章 2 处链接指向已删页 `GlobalAdmin-AdminException.md`）：异常码/消息改用 `Admin::EXCEPTION_CODE_ADMIN_NEED_LOGIN`、`Admin::EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION` 这类常量；**未登录不再抛异常**，而是走 `throwLoginOn()`——配了 `globaladmin_need_login_callback` 就回调、非 Ajax 则 `302` 到 `urlForLogin(当前 path)`、Ajax 则输出 `{"error_code":-1,"error_message":"NEED_LOGIN"}`，三条路最后都 `exit()`。
//  · 视图级开关改名：`__logined_enable_view` → `__use_logined_view_data`，`__logined_enable_header_footer` → `__use_logined_header_footer_file`；另有 `__logined_render_header_footer`（缺省视为真）决定要不要渲染头尾文件。
//  · `Foundation\Controller\AdminControllerBase` 的钩子改名：`onLoginedException()` → `onNeedPermission()`（无参；Ajax 分支硬编码 `error_code = -1`、`error_message = 'NEED_PERMISSION'`）。
//  · `AdminSessionTrait` 与会话键 `admin` 未变，但 `admin_callback_for_session` 这个键名已不存在，改成 `globaladmin_login_session`。

## 最小示例

与用户体系同构：一个工程类继承 [`DuckPhp\GlobalAdmin\GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)，用回调接上「当前是谁 / 登录服务 / 会话」，再挂进 `ext`：

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;
use MyProj\AdminSystem\AdminAction;
use MyProj\AdminSystem\AdminService;
use MyProj\AdminSystem\AdminSession;

class App extends DuckPhp
{
    public $options = [
        'admin_url_login'  => 'admin/login',
        'admin_url_logout' => 'admin/logout',
        'admin_url_home'   => 'admin/dashboard',

        'admin_callback_for_session'       => [AdminSession::class, '_'],
        'admin_callback_for_login_service' => [AdminService::class, '_'],
        'admin_callback_for_local_service' => [AdminService::class, '_'],
        'admin_callback_for_id'            => [AdminAction::class, 'id'],
        'admin_callback_for_name'          => [AdminAction::class, 'name'],

        'ext' => [
            MyAdmin::class => ['admin_enable' => true],   // 把工程侧 GlobalAdmin 子类挂成「管理员组件」
        ],
    ];
}
```

```php
// 后台控制器里：权限判断 + 操作日志
public function update()
{
    Helper::Admin()->canAccess();                                    // 无参：取当前路由的类/方法/URL
    Helper::Admin()->log('修改了站点配置', 'update', ['key' => 'site_name']);
    Helper::Show(get_defined_vars(), 'admin/config');
}
```

## 机制说明

### 1. GlobalAdmin：与用户体系同构

[DuckPhp\GlobalAdmin\GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md) 实现 [`AdminActionInterface`](../reference/GlobalAdmin-AdminActionInterface.md) 与 [`AdminLoginActionInterface`](../reference/GlobalAdmin-AdminLoginActionInterface.md)，提供：

| 入口                                                                              | 说明                                                             |
| ------------------------------------------------------------------------------- | -------------------------------------------------------------- |
| `Helper::Admin()` / `Helper::AdminId()` / `Helper::AdminName()`                 | 当前管理员（`AdminId()` 默认 `check_login=true`，未登录抛 `AdminException`） |
| `Helper::AdminService()`                                                        | 本地 Service（`admin_callback_for_local_service`），服务侧契约 [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md) |
| `login($post)` / `logout()`（源码 `src/GlobalAdmin/GlobalAdmin.php` 第 249 / 260 行） | 登录/登出，**没有注册**（后台账号由你自建）                                       |
| `canAccess($class, $method, $url)`（第 272 行）                                     | 权限判断；不传参时取当前路由上下文                                              |
| `log($string, $type, $ext)`（第 293 行）                                            | 操作日志（写什么、写哪里由你的实现决定）                                           |
| `isSuper()`（第 297 行）                                                            | 是否超管                                                           |
| `_Show()`                                                                       | 后台页面渲染（头尾 + `__logined_*` 注入）                                  |

### 2. 接入方式

和用户体系一样，**只有一条路**：写 `class MyAdmin extends \DuckPhp\GlobalAdmin\GlobalAdmin {}`（配好 `admin_callback_for_*`），然后 `'ext' => [MyAdmin::class => ['admin_enable' => true]]`；它的 `init()` 会经 [`PhaseProxy`](../reference/Component-PhaseProxy.md) 把自己注册成 `GlobalAdmin` 相位的实现。

> ⚠️ 旧文档里的 `admin_provider` 选项**源码里已经不存在**；`admin_enable` 是挂载时的开关，应用选项 `admin_provider_enable`（默认 `true`）可以整体关掉上述自我注册。

### 3. 选项对照表（以 `src/GlobalAdmin/GlobalAdmin.php` 的 `$options` 为准）

| 选项 | 默认 | 作用 |
|---|---|---|
| `admin_url_home` / `admin_url_login` / `admin_url_logout` | `null` | 后台首页 / 登录 / 退出 URL（未配回调时用 `__url()` 生成） |
| `admin_view_file_header` / `admin_view_file_footer` | `null` | 后台页面头/尾视图文件 |
| `admin_enable_callback_singleton` | `true` | 回调写成 `[类名, 方法]` 时是否先转 `类名::_()` |
| `admin_callback_for_id` / `_for_name` / `_for_data` | `null` | 取当前管理员 id / 名字 / 数据 |
| `admin_callback_for_local_service` | `null` | 返回本地 Service 实现（服务侧契约 [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md)） |
| `admin_callback_for_login_service` | `null` | 登录服务（服务侧契约 [`AdminLoginServiceInterface`](../reference/GlobalAdmin-AdminLoginServiceInterface.md)）：`login()/logout()` 经它校验 |
| `admin_callback_for_session` | `null` | 会话实现（[`AdminSessionInterface`](../reference/GlobalAdmin-AdminSessionInterface.md)）；配了它 `id()/name()` 优先读会话 |
| `admin_callback_for_url_for_home` / `_login` / `_logout` | `null` | 生成对应 URL 的回调（优先于 `admin_url_*`） |
| `admin_loginout_auto_redirect` | `true` | 登录/登出成功后自动 302 |

### 4. 会话实现：AdminSessionTrait

```php
namespace MyProj\AdminSystem;

use DuckPhp\Foundation\Controller\SessionTrait;
use DuckPhp\GlobalAdmin\AdminSessionInterface;
use DuckPhp\GlobalAdmin\AdminSessionTrait;

class AdminSession implements AdminSessionInterface
{
    use SessionTrait;        // 带前缀的会话读写（第 2-9 章）
    use AdminSessionTrait;   // getCurrentAdminId/Name、setCurrentAdmin …
}
```

[`AdminSessionTrait`](../reference/GlobalAdmin-AdminSessionTrait.md) 把当前管理员存进会话键 **`admin`**（数组，含 `id`/`name`）。未登录时 `Helper::AdminId()` 走 `throwLoginOn()`（旧文写的是「抛 `AdminException`」，该类已删除；也没有可配的 `admin_default_exception_class`）。

### 5. 后台页面的头尾：`__logined_enable_view`

后台页面要走 `GlobalAdmin::_Show()`（带 `admin_view_file_header/footer`、注入 `__logined_*`）才会包头尾，开关是一份**视图数据** `__logined_enable_view`（机制与用户侧完全一致，详见[第 2-18 章第 6 节](user.md)）：

- 继承 [`Foundation\Controller\AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) → `initController()` 自动 `assignViewData('__logined_enable_view', true)` 与 `__logined_enable_header_footer`，并做「未登录跳登录页 / Ajax 抛 `AdminException`」的兜底；
- 旧选项 `use_admin_view` **已失效**（源码只剩注释行）。

### 6. 后台菜单：Ext\PermissionMenu

[DuckPhp\Ext\PermissionMenu](../reference/Ext-PermissionMenu.md) 用 [DuckPhp\Ext\RouteLister](../reference/Ext-RouteLister.md) 扫出**后台控制器**（实现 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 的类）的路由，生成菜单/权限树：

| 模式 | 做法 |
|---|---|
| **注释模式** | 在控制器类/方法上写 `@menu_directory`、`@menu`、`@menu_action`、`@menu_permission` 等注释（示例见 `tests/data_for_tests/Ext/PermissionMenu/Controller/AdminController.php`） |
| **元数据模式** | 控制器实现 [PermissionMenuMetaInterface](../reference/Ext-PermissionMenuMetaInterface.md)，`__permissionMenuMeta()` 直接返回整张表 |
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
    Helper::Admin()->canAccess();                       // 不通过则抛 AdminException / 302
    MyService::_()->delete($id);
    Helper::Admin()->log("删除了 #{$id}", 'delete', ['id' => $id]);
    Helper::Show302('admin/list');
}
```

**② 超管专属入口**

```php
if (Helper::Admin()->isSuper()) {
    // 只有超管能看到的菜单/按钮
}
```

**③ 菜单落盘（大后台推荐）**

```php
$menu = PermissionMenu::_();
$menu->buildAndSaveToConfigJsonFile();       // 部署或定时任务里跑一次
// 运行时
$tree = $menu->loadAdminPermissionMenu();
```

**④ 用自己的表结构接管菜单**

```php
class AdminController implements AdminControllerInterface, PermissionMenuMetaInterface
{
    public function __permissionMenuMeta(): array
    {
        return [ /* name / type / url / weight / children … */ ];
    }
}
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 后台菜单是空的 | 控制器没实现 `AdminControllerInterface`，或没写 `@menu*` 注释 | 实现接口 + 写注释；或用 `__permissionMenuMeta()` |
| `DuckPhpSystemException: No GlobalAdmin Provider.` | 没配 `admin_callback_for_*`（或没挂 provider 类） | 至少配 `admin_callback_for_session` 或 `_for_id`/`_for_name` |
| `admin_provider` 选项没反应 | 该选项源码里已不存在 | 用 `'ext' => [MyAdmin::class => ['admin_enable' => true]]` |
| `canAccess()` 无参时报路由上下文为空 | 不在路由动作里调用（如 CLI / 子相位） | 显式传 `canAccess($class, $method, $url)` |
| 后台页面没有头尾 | 只开了 `__logined_enable_view`，没开 `__logined_enable_header_footer` | 两个都置真（继承 `AdminControllerBase` 时已自动） |
| 登录后仍回登录页 | `admin_callback_for_id` 读不到（会话键/前缀不对） | 检查 `AdminSessionTrait` 的会话键 `admin` 与 `session_prefix`（第 2-9 章） |

## 下一步

- [第 2-11 章 异常与错误处理](exception.md)：登录/权限异常现在怎么被接住、怎么变成跳转或错误页（旧文里的 `AdminException` 已删除）。
- [第 3-5 章 重写与覆盖](overriding.md)：换掉后台视图头尾。
- [第 2-15 章 命令行与定时任务](cli.md)：用 CLI 跑菜单落盘、看路由表。
- 参考手册：[GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md)、[Admin](../reference/GlobalAdmin-Admin.md)、[AdminActionInterface](../reference/GlobalAdmin-AdminActionInterface.md)、[AdminLoginActionInterface](../reference/GlobalAdmin-AdminLoginActionInterface.md)、[AdminServiceInterface](../reference/GlobalAdmin-AdminServiceInterface.md)、[AdminLoginServiceInterface](../reference/GlobalAdmin-AdminLoginServiceInterface.md)、[AdminSessionTrait](../reference/GlobalAdmin-AdminSessionTrait.md)、[Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)、[Ext\RouteLister](../reference/Ext-RouteLister.md)