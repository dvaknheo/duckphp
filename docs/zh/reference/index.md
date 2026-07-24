# DuckPhp 参考手册

DuckPhp 参考手册收录了框架所有类、接口、Trait 以及应用选项的完整说明。本页为目录页，按命名空间分组列出所有参考文档，方便快速定位。

## 全部选项

应用选项参考分为三个页面：

- [应用选项总览](options.md) — 选项机制与导航入口。
- [按类分组查看](options-by-class.md) — 按来源类组织的完整选项列表。
- [按字母顺序索引](options-index.md) — 按选项名排序的速查索引。

---

## 核心类

框架最核心、默认加载或全局可见的类与 Trait。

| 类 | 说明 |
|---|---|
| [DuckPhp\DuckPhp](DuckPhp.md) | 框架入口类，扩展自 `DuckPhp\Core\App`，默认加载一组常用组件。 |
| [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) | 绑定所有助手 Trait 的入口类，适合快速演示或小型项目。 |
| [DuckPhp\Core\App](Core-App.md) | 核心应用类，组合 `KernelTrait` 并初始化核心组件。 |
| [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 核心应用初始化流程 Trait，定义 `kernel_options` 与生命周期事件。 |
| [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) | 所有组件的基类，提供选项合并、单例容器等通用机制。 |
| [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) | 组件接口。 |
| [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md) | 单例模式 Trait。 |
| [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) | 相位容器，管理应用实例与子应用的单例对象。 |
| [DuckPhp\Core\Route](Core-Route.md) | 默认 MVC 路由组件，负责解析 PATH_INFO 并调用控制器。 |
| [DuckPhp\Core\Runtime](Core-Runtime.md) | 运行期数据保存组件，管理输出缓冲与运行状态。 |
| [DuckPhp\Core\Logger](Core-Logger.md) | 日志组件，按模板写入文件。 |
| [DuckPhp\Core\View](Core-View.md) | 视图组件，负责模板渲染与视图文件查找。 |
| [DuckPhp\Ext\EventManager](Ext-EventManager.md) | 事件管理组件。 |
| [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 异常与错误处理组件。 |
| [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | 超全局变量上下文组件，支持隔离与保存。 |
| [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) | 系统同名函数替换 Trait，便于测试与拦截。 |
| [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 自动加载类，支持 PSR-4 与项目应用命名空间加载。 |
| [DuckPhp\Core\Console](Core-Console.md) | 命令行模式组件，注册并执行 CLI 命令。 |
| [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) | 核心助手类，提供 HTML 转义、JSON 输出等工具方法。 |
| [DuckPhp\Core\Functions](Core-Functions.md) | 框架全局函数列表。 |
| [DuckPhp\Core\ThrowOnTrait](Core-ThrowOnTrait.md) | 可抛方法 Trait。 |
| [DuckPhp\Core\ExitException](Core-ExitException.md) | 退出异常。 |
| [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) | DuckPhp 系统异常基类。 |

---

## 组件

`DuckPhp\Component` 命名空间下的自带组件。

| 类 | 说明 |
|---|---|
| [DuckPhp\Component\Cache](Component-Cache.md) | 缓存组件基类，提供空实现。 |
| [DuckPhp\Component\Command](Component-Command.md) | 框架默认 CLI 命令集合。 |
| [DuckPhp\Component\Configer](Component-Configer.md) | 配置读取组件，从 `config/` 目录加载 PHP 配置。 |
| [DuckPhp\Component\DbManager](Component-DbManager.md) | 数据库管理组件，支持多库与读写分离。 |
| [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | 额外选项加载组件，支持 `DuckPhpApps.config.php`。 |
| [DuckPhp\Component\Lang](Component-Lang.md) | 多语言（i18n）组件。 |
| [DuckPhp\Component\Pager](Component-Pager.md) | 分页组件，渲染 HTML 分页条。 |
| [DuckPhp\Component\PagerInterface](Component-PagerInterface.md) | 分页接口。 |
| [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) | 相位代理组件。 |
| [DuckPhp\Component\RedisCache](Component-RedisCache.md) | 基于 Redis 的缓存组件。 |
| [DuckPhp\Component\RedisManager](Component-RedisManager.md) | Redis 管理器，支持多 Redis 实例。 |
| [DuckPhp\Component\RouteHookCheckStatus](Component-RouteHookCheckStatus.md) | 路由钩子，检查维护与安装状态。 |
| [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 无 PATH_INFO 兼容模式组件。 |
| [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 资源路由钩子，处理静态资源请求。 |
| [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | 路由重写组件。 |
| [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | 路由映射组件，支持正则与占位符路由。 |
| [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md) | 调用 Trait。 |

---

## 扩展

`DuckPhp\Ext` 命名空间下的可选扩展，非默认加载，可按需启用。

| 类 | 说明 |
|---|---|
| [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 可接受函数调用的视图组件。 |
| [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 项目安装器，支持 `new`、`show` 等命令。 |
| [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 空视图组件，仅填充数据不输出。 |
| [DuckPhp\Ext\ExceptionWrapper](Ext-ExceptionWrapper.md) | 异常包裹组件。 |
| [DuckPhp\Ext\ExtendableStaticCallTrait](Ext-ExtendableStaticCallTrait.md) | 可扩展静态调用的 Trait。 |
| [DuckPhp\Ext\FinderForController](Ext-FinderForController.md) | 控制器枚举组件。 |
| [DuckPhp\Ext\HookChain](Ext-HookChain.md) | 把回调扩展成链的类。 |
| [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) | JsonRpc 客户端基类。 |
| [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | JsonRpc 远程调用组件。 |
| [DuckPhp\Ext\JsonView](Ext-JsonView.md) | JSON 视图组件。 |
| [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 简化版路由组件。 |
| [DuckPhp\Ext\Misc](Ext-Misc.md) | 杂项功能组件，如 DI、Import、Recordset 处理。 |
| [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 门面自动加载组件。 |
| [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) | 门面基类。 |
| [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | 中间件管理组件。 |
| [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 简易 API 服务器路由钩子。 |
| [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | 多目录基准模式路由钩子。 |
| [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 函数模式路由钩子。 |
| [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) | 路由钩子管理器。 |
| [DuckPhp\Ext\StaticReplacer](Ext-StaticReplacer.md) | 适配协程的静态替换写法类。 |
| [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 严格检查模式组件。 |

---

## 数据库

`DuckPhp\Db` 命名空间下的数据库相关类与接口。

| 类 | 说明 |
|---|---|
| [DuckPhp\Db\Db](Db-Db.md) | 数据库类。 |
| [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) | 为 `Db` 增加高级功能的 Trait。 |
| [DuckPhp\Db\DbInterface](Db-DbInterface.md) | `Db` 实现的接口。 |

---

## 命令行安装器

`DuckPhp\FastInstaller` 命名空间下的安装程序相关类。

| 类 | 说明 |
|---|---|
| [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 安装程序入口。 |
| [DuckPhp\FastInstaller\DatabaseInstaller](FastInstaller-DatabaseInstaller.md) | 数据库安装器。 |
| [DuckPhp\FastInstaller\RedisInstaller](FastInstaller-RedisInstaller.md) | Redis 安装器。 |
| [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 数据库结构导出器。 |
| [DuckPhp\FastInstaller\Supporter](FastInstaller-Supporter.md) | 安装器支持基类。 |
| [DuckPhp\FastInstaller\SupporterByMysql](FastInstaller-SupporterByMysql.md) | MySQL 支持类。 |
| [DuckPhp\FastInstaller\SupporterByPgsql](FastInstaller-SupporterByPgsql.md) | PostgreSQL 支持类。 |
| [DuckPhp\FastInstaller\SupporterBySqlite](FastInstaller-SupporterBySqlite.md) | SQLite 支持类。 |

---

## HTTP 服务器

`DuckPhp\HttpServer` 命名空间下的内嵌 HTTP 服务器。

| 类 | 说明 |
|---|---|
| [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 内嵌 HTTP 服务器。 |
| [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) | HTTP 服务器接口。 |

---

## 助手

`DuckPhp\Helper` 与 `DuckPhp\Foundation` 命名空间下的助手类与 Trait。

### Helper

| 类 | 说明 |
|---|---|
| [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md) | 应用助手 Trait。 |
| [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md) | 业务助手 Trait。 |
| [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md) | 控制器助手 Trait。 |
| [DuckPhp\Helper\ModelHelperTrait](Helper-ModelHelperTrait.md) | 模型助手 Trait。 |

### Foundation

| 类 | 说明 |
|---|---|
| [DuckPhp\Foundation\Helper](Foundation-Helper.md) | 助手集合类。 |
| [DuckPhp\Foundation\Business\Helper](Foundation-Business-Helper.md) | 业务助手类。 |
| [DuckPhp\Foundation\Controller\Helper](Foundation-Controller-Helper.md) | 控制器助手类。 |
| [DuckPhp\Foundation\Model\Helper](Foundation-Model-Helper.md) | 模型助手类。 |
| [DuckPhp\Foundation\System\Helper](Foundation-System-Helper.md) | 系统助手类。 |
| [DuckPhp\Foundation\ExceptionReporterTrait](Foundation-ExceptionReporterTrait.md) | 错误报告 Trait。 |
| [DuckPhp\Foundation\BusinessTrait](Foundation-BusinessTrait.md) | 简单业务 Trait。 |
| [DuckPhp\Foundation\ControllerTrait](Foundation-ControllerTrait.md) | 简单控制器 Trait。 |
| [DuckPhp\Foundation\ExceptionTrait](Foundation-ExceptionTrait.md) | 简单异常 Trait。 |
| [DuckPhp\Foundation\ModelTrait](Foundation-ModelTrait.md) | 简单模型 Trait。 |
| [DuckPhp\Foundation\SessionTrait](Foundation-SessionTrait.md) | 简单会话 Trait。 |
| [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) | 简单单例 Trait。 |
| [DuckPhp\Foundation\FastInstallerTrait](Foundation-FastInstallerTrait.md) | 快速安装器助手 Trait。 |

---

## 全局管理

`DuckPhp\GlobalAdmin` 与 `DuckPhp\GlobalUser` 命名空间下的全局管理组件。

### GlobalAdmin

| 类 | 说明 |
|---|---|
| [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 全局管理员组件。 |
| [DuckPhp\GlobalAdmin\GlobalAdminTrait](GlobalAdmin-GlobalAdminTrait.md) | 管理员 Trait：事件/服务/视图。 |
| [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) | 管理员操作接口。 |
| [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) | 管理员控制器接口（标记）。 |
| [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) | 管理员服务接口。 |
| [DuckPhp\GlobalAdmin\AdminException](GlobalAdmin-AdminException.md) | 管理员异常。 |

### GlobalUser

| 类 | 说明 |
|---|---|
| [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 全局用户组件。 |
| [DuckPhp\GlobalUser\GlobalUserTrait](GlobalUser-GlobalUserTrait.md) | 用户 Trait：事件/服务/视图。 |
| [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) | 用户操作接口。 |
| [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) | 用户控制器接口（标记）。 |
| [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) | 用户服务接口。 |
| [DuckPhp\GlobalUser\UserException](GlobalUser-UserException.md) | 用户异常。 |
