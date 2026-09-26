# 2-20 使用管理员系统

> 解决什么问题：在你的后台控制器 / 业务代码里回答「现在这个管理员是谁」「他能不能做这件事」「他是不是超管」，以及后台菜单怎么生成。
> 前置：[第 2-19 章 使用用户系统](user.md)（两套入口同构，本章只讲不同的地方）、[第 2-5 章 控制器](controllers.md)、[第 2-11 章 会话](session.md)。预计 20 分钟。
> 本章只讲**怎么用**；「管理员系统是怎么接进来的」（三个实现 + 选项 + `ext` 挂载）见[第 4-12 章 实现管理员系统](impl-admin.md)。**没接之前**这些入口会直接抛 `DuckPhpSystemException`。
> 可跑资产：`tests/Foundation/Controller/AdminControllerBaseTest.php`、`tests/GlobalAdmin/GlobalAdminTest.php`。

## 最小示例

```php
// MyProj\Controller\AdminController
public function dashboard()
{
    $adminId = Helper::AdminId();        // 当前管理员 id；未登录 → 302 到后台登录页并结束请求
    $name    = Helper::AdminName();      // 当前管理员名
    $isSuper = Helper::Admin()->isSuper();

    Helper::Admin()->log('打开了仪表盘', 'dashboard');   // 操作日志，落到你的 AdminServiceInterface::log()
    Helper::Show(get_defined_vars(), 'admin/dashboard');
}
```

业务层拿不到「当前是谁」，但能拿到**管理员服务**：

```php
// MyProj\Business\AdminReportService
public function export(int $adminId): array
{
    Helper::AdminService()->log($adminId, '导出了后台报表', 'export', ['rows' => 120]);
    return ReportModel::_()->all();
}
```

## 机制说明

### 1. 入口表（与用户侧逐项对应）

| 你在哪一层 | 入口 | 拿到什么 |
|---|---|---|
| Controller | `Helper::Admin()` | [`AdminActionInterface`](../reference/GlobalAdmin-AdminActionInterface.md)（当前管理员对象） |
| Controller | `Helper::AdminId()` / `Helper::AdminName()` | 当前管理员 id / 名字 |
| Controller | `Helper::AdminService()` | [`AdminServiceInterface`](../reference/GlobalAdmin-AdminServiceInterface.md) |
| Business | `Helper::AdminService()` | 同上，**同一个服务** |
| 任何地方 | [`Admin::_()`](../reference/GlobalAdmin-Admin.md) | 当前管理员对象（`Helper::Admin()` 就是它） |

`Helper::Admin()` 能做什么（与[第 2-19 章第 2 节](user.md)那张表同构，管理员侧少注册、多 `isSuper()`）：

| 方法 | 返回 | 说明 |
|---|---|---|
| `id(bool $check_login = true)` / `name(...)` / `data(...)` | id / 名字 / 数组 | 当前管理员；未登录见第 2 节 |
| `canAccess(?string $url = null, ?string $class = null, ?string $method = null)` | `bool` | 权限判断；不传参就用当前路由；未登录直接 `false` |
| `isSuper()` | `bool` | 是不是超管（管理员侧特有） |
| `log(string $string, ?string $type = null, array $ext = [])` | — | 记一条操作日志 |
| `urlForHome()` / `urlForLogin($url_back = null)` / `urlForLogout()` | `string` | 后台 URL（**没有 `urlForRegister()`**） |
| `service()` | `AdminServiceInterface` | 等价于 `Helper::AdminService()` |

### 2. 未登录时会发生什么

与用户侧完全一样（[第 2-19 章第 3 节](user.md)）：`id()` / `name()` / `data()` 默认 `$check_login = true`，未登录时三选一——配了 `globaladmin_need_login_callback` 就跑回调、非 Ajax `302` 到 `urlForLogin(当前 path)`、Ajax 输出 `{"error_code":-1,"error_message":"NEED_LOGIN"}`，**三条路最后都结束请求**。要自己判断就传 `false`：

```php
$adminId = Helper::AdminId(false);       // 未登录返回 0
```

### 3. 后台控制器的兜底：`AdminControllerBase`

后台控制器通常继承 [`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md)，它替你做完三件事：

1. `checkInstall(null)`：应用没装就 `302` 到安装页并中断（[第 3-6 章](installer.md)）；
2. `Helper::AdminId(true)`：未登录按上一节处理；
3. `Helper::Admin()->canAccess()`：**没权限时调 `onNeedPermission()`**（默认：非 Ajax `302` 到登录页、Ajax 输出 `{"error_code":-1,"error_message":"NEED_PERMISSION"}`），然后结束请求。

想换「没权限」的表现（403 JSON、记一条越权日志……），在自己的控制器基类里**重写 `onNeedPermission()`**（无参）：

```php
class AdminBaseController extends AdminControllerBase
{
    protected function onNeedPermission()
    {
        Helper::ShowJson(['error_code' => -2, 'error_message' => 'NEED_PERMISSION']);
    }
}
```

它同时会把 `__use_logined_view_data` 与 `__use_logined_header_footer_file` 置真（[第 2-19 章第 6 节](user.md)），所以后台页面的头尾是自动的。

### 4. 超管专属内容

```php
if (Helper::Admin()->isSuper()) {
    // 只有超管能看到的菜单/按钮
}
```

`isSuper()` 每次都会问你的 [`AdminServiceInterface::isSuper($admin_id)`](../reference/GlobalAdmin-AdminServiceInterface.md)，框架不缓存；按钮/菜单都按它的返回值决定。

### 5. 后台菜单：`Ext\PermissionMenu`

[`PermissionMenu`](../reference/Ext-PermissionMenu.md)（`Ext\*` 扩展，要用得在 `ext` 里挂上）用 [`RouteLister`](../reference/Ext-RouteLister.md) 扫出**后台控制器**（实现 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 的类，继承 `AdminControllerBase` 就已经满足）的路由，生成菜单/权限树：

| 模式 | 做法 |
|---|---|
| **注释模式** | 在控制器类/方法上写 `@menu_directory`、`@menu`、`@menu_action`、`@menu_permission` 等注释（示例见 `tests/data_for_tests/Ext/PermissionMenu/Controller/AdminController.php`） |
| **元数据模式** | 控制器实现 [`PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md)，`__permissionMenuMeta()` 直接返回整张表 |
| **落盘模式** | `buildAndSaveToConfigJsonFile()` 把树写进配置，运行时 `loadAdminPermissionMenu()` 读回，避免每请求扫路由 |

```php
// 部署或定时任务里跑一次（CLI 里也行，见第 2-16 章）
PermissionMenu::_()->buildAndSaveToConfigJsonFile();
// 运行时读回
$tree = PermissionMenu::_()->loadAdminPermissionMenu();
```

菜单文件路径由隐藏选项 `permission_menu_tree_for_admin` 指定；`loadAll()` 会把根应用与各子应用的菜单合并成一棵整树（跨相位安全）。装配方式、元数据契约与「用 `RouteLister::_()->command_routes()` 排查」见[第 4-13 章](ext-classes.md) §6。

## 常见写法

**① 每个后台动作前判权限、动作后记日志**

```php
public function delete()
{
    $id = (int)Helper::GET('id');
    if (!Helper::Admin()->canAccess()) {        // 无参：取当前路由上下文
        return;                                  // 跳转/报错交给基类的 onNeedPermission()
    }
    MyService::_()->delete($id);
    Helper::Admin()->log("删除了 #{$id}", 'delete', ['id' => $id]);
    Helper::Show302('admin/list');
}
```

**② 用管理员 id 做数据归属记录**

```php
NoteModel::_()->insert(['admin_id' => Helper::AdminId(), 'body' => $body]);
```

**③ 后台视图里用登录信息**

```php
<?php if (!empty($__logined_id)): ?>
    <?= __h($__logined_name) ?> · <a href="<?= __h($__logined_url_logout) ?>">退出</a>
<?php endif; ?>
```

**④ 给菜单加一条（注释模式）**

```php
/**
 * @menu_directory 系统设置
 * @menu_icon fa fa-folder
 * @menu_weight 10
 */
class ConfigController extends AdminControllerBase
{
    /**
     * @menu 站点配置
     * @menu_icon cog
     */
    public function action_index()
    {
    }
    /**
     * @menu_action 保存
     * @menu_permission #save 保存站点配置
     */
    public function action_save()
    {
        // @menu_permission 声明的权限点会出现在菜单树里，供角色分配用
    }
}
```

> `@menu_permission` 的写法是 `#方法名 权限名`（相对当前控制器）或 `/绝对/路径 权限名`；同一方法可以写多条。完整规则见[参考手册](../reference/Ext-PermissionMenu.md)。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 调用 `Helper::AdminId()` 页面直接 302 走了 | `$check_login` 默认为 `true`，未登录就跳登录页并结束请求 | 要自己判断就传 `false`（第 2 节） |
| 业务层里写 `Helper::AdminId()` 报「方法不存在」 | 业务层 Helper 只有 `AdminService()` | 由控制器把 `$adminId` 传进业务方法 |
| `DuckPhpSystemException: No GlobalAdmin Provider.` | 管理员系统还没接（`ext` 没挂） | 见[第 4-12 章](impl-admin.md) |
| `canAccess()` 无参时报路由上下文为空 | 不在路由动作里调用（CLI / 子相位） | 显式传 `canAccess($url, $class, $method)`（注意 `$url` 在前） |
| 后台页面没有头尾 | 没继承 `AdminControllerBase`，或两个视图数据键没置真 | 继承它，或自己 `assignViewData`（[第 2-19 章第 6 节](user.md)） |
| 后台菜单是空的 | 控制器没实现 `AdminControllerInterface`，或没写 `@menu*` 注释 | 继承 `AdminControllerBase`；或写注释/用 `__permissionMenuMeta()` |
| 越权时页面还是默认的 302/JSON | 重写 `onNeedPermission()` 的位置不对 | 在**自己的控制器基类**里重写（第 3 节） |

## 下一步

- [第 2-19 章 使用用户系统](user.md)：前台那套入口。
- [第 4-12 章 实现管理员系统](impl-admin.md)：本项目的管理员系统由谁提供、选项怎么配。
- [第 2-13 章 事件系统](events.md)：`EVENT_ACTION_ADMIN_*`（登录/登出前后）怎么监听。
- [第 2-16 章 命令行与定时任务](cli.md)：用 CLI 跑菜单落盘、看路由表。
- [第 3-5 章 重写与覆盖](overriding.md)：换掉后台视图头尾。
- 参考手册：[Admin](../reference/GlobalAdmin-Admin.md)、[AdminActionInterface](../reference/GlobalAdmin-AdminActionInterface.md)、[AdminLoginActionInterface](../reference/GlobalAdmin-AdminLoginActionInterface.md)、[GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md)、[AdminControllerInterface](../reference/GlobalAdmin-AdminControllerInterface.md)、[AdminServiceInterface](../reference/GlobalAdmin-AdminServiceInterface.md)、[Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)、[Ext\RouteLister](../reference/Ext-RouteLister.md)
