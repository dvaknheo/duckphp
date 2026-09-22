# DuckPhp 参考手册

> 本手册回答「**有什么**」：每个类 / 接口 / Trait 的命名空间、声明、选项、方法签名与注意事项——这里写到的每一项都能在 `src/` 里逐字找到。
> 想学「**怎么做**」，请看用户指南（`docs/zh/guide/`）：那边只讲做法，机制细节链回本手册。

## 速查入口

| 想做什么 | 去哪里 |
|---|---|
| 找某个类的全貌 | 下面的[分类导航](#分类导航)或[全量索引（按类名）](#全量索引按类名) |
| 查某个选项的含义与默认值 | [应用选项总览（首页）](options.md) → [按类分组](options-by-class.md) / [按字母顺序索引](options-index.md) |
| 搞清 `options` 与 `setting` 的分工 | [应用设置 Setting](setting.md) |
| 全局函数（`__h()` 等） | [DuckPhp\Core\Functions](Core-Functions.md) |
| 看懂逐类页的版式 | 见下面的[怎么用这本手册](#怎么用这本手册) |

## 全书统计

<!-- GEN:stats start -->
| 项目 | 数量 |
|---|---|
| 逐类参考页 | 109 篇 |
| 声明了选项的类 | 42 个 |
| 应用选项（去重后） | 218 个 |
| 应用选项（隐藏） | 9 个 |
<!-- GEN:stats end -->

## 怎么用这本手册

- **逐类页固定版式**：简介 → 类信息 → 选项 → 使用方式 → 配置示例 → 注意事项 → 全部选项 → 方法列表 → 相关链接。找东西时照着版式跳即可。
- **方法列表**：条目是「4 空格缩进的签名 + 一句中文说明」；静态壳与实例实现分开列（如 `Show()` 与 `_Show()`）；由 Trait 提供的方法不重复列，只说明来源并链过去。
- **「注意事项」写的是源码的脾气**：踩过的坑、刻意为之的反直觉行为，以及「以源码为准」的提醒。
- **链接即存在性证明**：本手册只链真实存在的文件，所以某个链接点得开，就说明那个页面确实在。
- **计划中的页面**沿用用户指南的约定：用 `⏳` 标注且不给链接，避免死链。

## 分类导航

<!-- GEN:nav start -->
## 入口类

| 类 | 说明 |
|---|---|
| [DuckPhp\DuckPhp](DuckPhp.md) | DuckPhp\Core\App 的子类，本身不定义复杂的业务，而是 |
| [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) | DuckPhpAllInOne extends DuckPhp，是“整个应用只… |

## 核心类

| 类 | 说明 |
|---|---|
| [DuckPhp\Core\App](Core-App.md) | DuckPhp\Core\App |
| [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | Core\AutoLoader 提供一套很轻的“命名空间 → 目录”自动加载 |
| [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) | DuckPHP 框架中绝大多数组件的基类 |
| [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) | DuckPHP 组件的契约接口，规定了“框架内一个组件必须具备哪些公开入口” |
| [DuckPhp\Core\Console](Core-Console.md) | DuckPHP 对 CLI 的命令处理根 |
| [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) | 框架把“常用小功能”收纳为一组**静态便捷方法**的门面 |
| [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) | 系统抛出的、携带 ThrowOn 能力的通用异常基类 |
| [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | ExceptionManager（class ExceptionManager… |
| [DuckPhp\Core\ExitException](Core-ExitException.md) | 直接 exit/die |
| [DuckPhp\Core\Functions](Core-Functions.md) | src/Core/Functions.php 定义了一组以双下划线 __ 开头… |
| [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | KernelTrait 把“一个应用是什么、怎么跑”这件事写在一个 Trait… |
| [DuckPhp\Core\Logger](Core-Logger.md) | Logger（PSR-3 注释，非 implements，尽力对齐接口）适合“… |
| [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) | DuckPHP 框架的“Phase 容器”，也是 SingletonExTra… |
| [DuckPhp\Core\Route](Core-Route.md) | DuckPHP 的默认路由核心 |
| [DuckPhp\Core\Runtime](Core-Runtime.md) | Runtime（class Runtime extends Component… |
| [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) | DuckPHP 里“单例式访问”的常见来源 Trait |
| [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | SuperGlobal 提供在不污染全局符号的前提下操作 HTTP 超全局的手段 |
| [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) | SystemWrapper 把常见“副作用型”系统函数放到一处，便于 |
| [DuckPhp\Core\View](Core-View.md) | DuckPHP 的默认视图实现（class View extends Comp… |

## 组件

| 类 | 说明 |
|---|---|
| [DuckPhp\Component\Cache](Component-Cache.md) | Cache extends ComponentBase 为整条目录的缓存接口提… |
| [DuckPhp\Component\Command](Component-Command.md) | Command extends ComponentBase 是 DuckPHP… |
| [DuckPhp\Component\CommandMetaInterface](Component-CommandMetaInterface.md) | 「命令表元数据」契约接口 |
| [DuckPhp\Component\Configer](Component-Configer.md) | Configer extends ComponentBase 负责把 {fil… |
| [DuckPhp\Component\DbManager](Component-DbManager.md) | DbManager extends ComponentBase 是 DuckP… |
| [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | ExtOptionsLoader处理一类“要记得、要能在下次跑时仍生效”的动态… |
| [DuckPhp\Component\GlobalEvent](Component-GlobalEvent.md) | GlobalEvent extends ComponentBase 提供跨组件… |
| [DuckPhp\Component\Lang](Component-Lang.md) | Lang extends ComponentBase 提供简单而完整的界面翻译 |
| [DuckPhp\Component\Pager](Component-Pager.md) | Pager extends ComponentBase implements … |
| [DuckPhp\Component\PagerInterface](Component-PagerInterface.md) | PagerInterface 定义实现分页器所需的最小接口 |
| [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) | PhaseProxy 把一个“外部/由非默认 Phase 需要访问”的对象包起… |
| [DuckPhp\Component\RedisCache](Component-RedisCache.md) | RedisCache extends ComponentBase（注释 ali… |
| [DuckPhp\Component\RedisManager](Component-RedisManager.md) | RedisManager extends ComponentBase 是 Du… |
| [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | RouteHookPathInfoCompat extends Compone… |
| [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | RouteHookResource extends ComponentBase… |
| [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | RouteHookRewrite extends ComponentBase … |
| [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | RouteHookRouteMap extends ComponentBase… |
| [DuckPhp\Component\Validator](Component-Validator.md) | DuckPHP 的数据验证组件，采用“字段 => 规则字符串”的声明式写法 |

## 扩展

| 类 | 说明 |
|---|---|
| [DuckPhp\Ext\CallableView](Ext-CallableView.md) | Core\View 的扩展 |
| [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | bin/duckphp 背后的命令行安装器，提供三个 CLI 命令 |
| [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | Core\View 的扩展 |
| [DuckPhp\Ext\EventManager](Ext-EventManager.md) | 简单的事件管理器扩展 |
| [DuckPhp\Ext\ExceptionWrapper](Ext-ExceptionWrapper.md) | 一个“异常安全调用包装” |
| [DuckPhp\Ext\ExtendableStaticCallTrait](Ext-ExtendableStaticCallTrait.md) | ExtendableStaticCallTrait 为类提供「外部扩展静态方法… |
| [DuckPhp\Ext\HookChain](Ext-HookChain.md) | HookChain 表示一串「回调链」 |
| [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) | JSON-RPC **客户端**基类 |
| [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | JSON-RPC 扩展的总控 |
| [DuckPhp\Ext\JsonView](Ext-JsonView.md) | Core\View 的扩展 |
| [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 极简版 MVC 路由（Core\Route 的子集） |
| [DuckPhp\Ext\Misc](Ext-Misc.md) | Misc 收集若干“杂项”工具 |
| [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | MyFacadesAutoLoader 实现“Facade（门面）命名空间自动… |
| [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) | Facade（门面）类的基类 |
| [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | 中间件管理器扩展 |
| [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) | 「后台权限菜单树」构建器 |
| [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) | 「权限菜单元数据」契约接口 |
| [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | “API 服务器”路由扩展 |
| [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | RouteHookDirectoryMode 实现“目录/文件模式”路由 |
| [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | “函数式路由”扩展 |
| [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) | 路由钩子列表的管理器 |
| [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | **网页安装向导** |
| [DuckPhp\Ext\RouteHookWebInstallerView](Ext-RouteHookWebInstallerView.md) | RouteHookWebInstaller 的**内置安装视图** |
| [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) | RouteLister extends ComponentBase 提供“把系… |
| [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 数据库“结构/数据导出与安装”扩展 |
| [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) | SqlDumper 的驱动适配基类 |
| [DuckPhp\Ext\SqlDumperSupporterByMysql](Ext-SqlDumperSupporterByMysql.md) | SqlDumperSupporter 的 **MySQL** 驱动实现 |
| [DuckPhp\Ext\SqlDumperSupporterByPgsql](Ext-SqlDumperSupporterByPgsql.md) | SqlDumperSupporter 的 **PostgreSQL** 驱动实现 |
| [DuckPhp\Ext\SqlDumperSupporterBySqlite](Ext-SqlDumperSupporterBySqlite.md) | SqlDumperSupporter 的 **SQLite** 驱动实现 |
| [DuckPhp\Ext\StaticReplacer](Ext-StaticReplacer.md) | StaticReplacer 提供“把全局变量 / 函数内静态变量 / 类静态… |
| [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) | ThrowOnTrait 提供静态条件抛出异常方法 |

## 数据库

| 类 | 说明 |
|---|---|
| [DuckPhp\Db\Db](Db-Db.md) | DuckPHP 默认的数据库连接对象，实现 DbInterface 并组合 D… |
| [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) | 为 Db 连接的“便捷数据操作”补充的方法集合，被 DuckPhp\Db\Db… |
| [DuckPhp\Db\DbInterface](Db-DbInterface.md) | DuckPHP 数据库连接对象的契约接口 |

## HTTP 服务器

| 类 | 说明 |
|---|---|
| [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | DuckPHP 内置的“用 PHP 内置服务器跑项目”的启动器 |
| [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) | DuckPHP 内置 HTTP 服务器启动器的契约接口，规定实现方必须提供 |

## 助手

| 类 | 说明 |
|---|---|
| [DuckPhp\Foundation\Business\Base](Foundation-Business-Base.md) | 工程「Business（业务层）」的推荐基类（abstract） |
| [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) | 面向 **Business（业务层）** 的静态助手集合（方法就在本类里，不再… |
| [DuckPhp\Foundation\Controller\ActionBase](Foundation-Controller-ActionBase.md) | 工程「Action 复用类」的推荐基类（abstract） |
| [DuckPhp\Foundation\Controller\AdminControllerBase](Foundation-Controller-AdminControllerBase.md) | 工程「后台管理员控制器」的推荐基类 |
| [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) | 工程「控制器层」的推荐基类（abstract） |
| [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) | 面向 **Controller（控制器层）** 的静态助手集合（方法就在本类里… |
| [DuckPhp\Foundation\Controller\ExceptionReporterTrait](Foundation-Controller-ExceptionReporterTrait.md) | 「项目异常报告器」的推荐实现 |
| [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) | SessionTrait 为组合它的类提供**带前缀的 Session 读写** |
| [DuckPhp\Foundation\Controller\UserControllerBase](Foundation-Controller-UserControllerBase.md) | 工程「前台登录用户控制器」的推荐基类 |
| [DuckPhp\Foundation\Helper](Foundation-Helper.md) | 「四层 Helper 并集」门面 |
| [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) | 工程「Model（数据层）」的推荐基类（abstract） |
| [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) | 数据层的**薄壳类** |
| [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) | 面向 **Model（数据层）** 的静态助手集合 |
| [DuckPhp\Foundation\Model\ModelTrait](Foundation-Model-ModelTrait.md) | 数据模型（Model）的常用能力封装，被 Foundation\Model\B… |
| [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) | Foundation 层对 Core\SingletonExTrait 的**… |
| [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) | **应用/接线层（System）**的静态助手集合（方法就在本类里，不再有 t… |

## 全局管理

| 类 | 说明 |
|---|---|
| [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) | 「管理员会话动作」的契约接口 |
| [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) | 一个**空的标记接口**（marker interface，不声明任何方法） |
| [DuckPhp\GlobalAdmin\AdminException](GlobalAdmin-AdminException.md) | 管理员（后台）领域的异常类 |
| [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) | 「管理员登录动作」契约接口，与 AdminActionInterface 分离… |
| [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) | 「管理员登录服务」契约接口，描述服务侧的登录/退出能力 |
| [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) | “管理员服务”的契约接口，定义后台服务侧需要实现的三件事 |
| [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) | 「管理员会话」契约接口 |
| [DuckPhp\GlobalAdmin\AdminSessionTrait](GlobalAdmin-AdminSessionTrait.md) | AdminSessionInterface 的默认实现 Trait |
| [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | DuckPHP 的「全局管理员组件」 |
| [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | DuckPHP 的「全局用户组件」 |
| [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) | 「用户会话动作」的契约接口 |
| [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) | 一个**空的标记接口**（marker interface，不声明任何方法） |
| [DuckPhp\GlobalUser\UserException](GlobalUser-UserException.md) | 用户（前台）领域的异常类 |
| [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) | 「用户登录动作」契约接口，与 UserActionInterface 分离，描… |
| [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) | 「用户登录服务」契约接口，描述服务侧的注册/登录/退出 |
| [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) | “用户服务”的契约接口，定义前台服务侧需要实现的三件事 |
| [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) | 「用户会话」契约接口 |
| [DuckPhp\GlobalUser\UserSessionTrait](GlobalUser-UserSessionTrait.md) | UserSessionInterface 的默认实现 Trait |
<!-- GEN:nav end -->

## 全量索引（按类名）

<!-- GEN:az start -->
| 类 | 一句话 |
|---|---|
| [DuckPhp\Foundation\Controller\ActionBase](Foundation-Controller-ActionBase.md) | 工程「Action 复用类」的推荐基类（abstract） |
| [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) | 「管理员会话动作」的契约接口 |
| [DuckPhp\Foundation\Controller\AdminControllerBase](Foundation-Controller-AdminControllerBase.md) | 工程「后台管理员控制器」的推荐基类 |
| [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) | 一个**空的标记接口**（marker interface，不声明任何方法） |
| [DuckPhp\GlobalAdmin\AdminException](GlobalAdmin-AdminException.md) | 管理员（后台）领域的异常类 |
| [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) | 「管理员登录动作」契约接口，与 AdminActionInterface 分离… |
| [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) | 「管理员登录服务」契约接口，描述服务侧的登录/退出能力 |
| [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) | “管理员服务”的契约接口，定义后台服务侧需要实现的三件事 |
| [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) | 「管理员会话」契约接口 |
| [DuckPhp\GlobalAdmin\AdminSessionTrait](GlobalAdmin-AdminSessionTrait.md) | AdminSessionInterface 的默认实现 Trait |
| [DuckPhp\Core\App](Core-App.md) | DuckPhp\Core\App |
| [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | Core\AutoLoader 提供一套很轻的“命名空间 → 目录”自动加载 |
| [DuckPhp\Foundation\Business\Base](Foundation-Business-Base.md) | 工程「Business（业务层）」的推荐基类（abstract） |
| [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) | 工程「控制器层」的推荐基类（abstract） |
| [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) | 工程「Model（数据层）」的推荐基类（abstract） |
| [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) | 面向 **Business（业务层）** 的静态助手集合（方法就在本类里，不再… |
| [DuckPhp\Component\Cache](Component-Cache.md) | Cache extends ComponentBase 为整条目录的缓存接口提… |
| [DuckPhp\Ext\CallableView](Ext-CallableView.md) | Core\View 的扩展 |
| [DuckPhp\Component\Command](Component-Command.md) | Command extends ComponentBase 是 DuckPHP… |
| [DuckPhp\Component\CommandMetaInterface](Component-CommandMetaInterface.md) | 「命令表元数据」契约接口 |
| [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) | DuckPHP 框架中绝大多数组件的基类 |
| [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) | DuckPHP 组件的契约接口，规定了“框架内一个组件必须具备哪些公开入口” |
| [DuckPhp\Component\Configer](Component-Configer.md) | Configer extends ComponentBase 负责把 {fil… |
| [DuckPhp\Core\Console](Core-Console.md) | DuckPHP 对 CLI 的命令处理根 |
| [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) | 面向 **Controller（控制器层）** 的静态助手集合（方法就在本类里… |
| [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) | 框架把“常用小功能”收纳为一组**静态便捷方法**的门面 |
| [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) | 为 Db 连接的“便捷数据操作”补充的方法集合，被 DuckPhp\Db\Db… |
| [DuckPhp\Db\Db](Db-Db.md) | DuckPHP 默认的数据库连接对象，实现 DbInterface 并组合 D… |
| [DuckPhp\Db\DbInterface](Db-DbInterface.md) | DuckPHP 数据库连接对象的契约接口 |
| [DuckPhp\Component\DbManager](Component-DbManager.md) | DbManager extends ComponentBase 是 DuckP… |
| [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) | DuckPhpAllInOne extends DuckPhp，是“整个应用只… |
| [DuckPhp\DuckPhp](DuckPhp.md) | DuckPhp\Core\App 的子类，本身不定义复杂的业务，而是 |
| [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | bin/duckphp 背后的命令行安装器，提供三个 CLI 命令 |
| [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) | 系统抛出的、携带 ThrowOn 能力的通用异常基类 |
| [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | Core\View 的扩展 |
| [DuckPhp\Ext\EventManager](Ext-EventManager.md) | 简单的事件管理器扩展 |
| [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | ExceptionManager（class ExceptionManager… |
| [DuckPhp\Foundation\Controller\ExceptionReporterTrait](Foundation-Controller-ExceptionReporterTrait.md) | 「项目异常报告器」的推荐实现 |
| [DuckPhp\Ext\ExceptionWrapper](Ext-ExceptionWrapper.md) | 一个“异常安全调用包装” |
| [DuckPhp\Core\ExitException](Core-ExitException.md) | 直接 exit/die |
| [DuckPhp\Ext\ExtendableStaticCallTrait](Ext-ExtendableStaticCallTrait.md) | ExtendableStaticCallTrait 为类提供「外部扩展静态方法… |
| [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | ExtOptionsLoader处理一类“要记得、要能在下次跑时仍生效”的动态… |
| [DuckPhp\Core\Functions](Core-Functions.md) | src/Core/Functions.php 定义了一组以双下划线 __ 开头… |
| [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | DuckPHP 的「全局管理员组件」 |
| [DuckPhp\Component\GlobalEvent](Component-GlobalEvent.md) | GlobalEvent extends ComponentBase 提供跨组件… |
| [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | DuckPHP 的「全局用户组件」 |
| [DuckPhp\Foundation\Helper](Foundation-Helper.md) | 「四层 Helper 并集」门面 |
| [DuckPhp\Ext\HookChain](Ext-HookChain.md) | HookChain 表示一串「回调链」 |
| [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | DuckPHP 内置的“用 PHP 内置服务器跑项目”的启动器 |
| [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) | DuckPHP 内置 HTTP 服务器启动器的契约接口，规定实现方必须提供 |
| [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) | JSON-RPC **客户端**基类 |
| [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | JSON-RPC 扩展的总控 |
| [DuckPhp\Ext\JsonView](Ext-JsonView.md) | Core\View 的扩展 |
| [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | KernelTrait 把“一个应用是什么、怎么跑”这件事写在一个 Trait… |
| [DuckPhp\Component\Lang](Component-Lang.md) | Lang extends ComponentBase 提供简单而完整的界面翻译 |
| [DuckPhp\Core\Logger](Core-Logger.md) | Logger（PSR-3 注释，非 implements，尽力对齐接口）适合“… |
| [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 极简版 MVC 路由（Core\Route 的子集） |
| [DuckPhp\Ext\Misc](Ext-Misc.md) | Misc 收集若干“杂项”工具 |
| [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) | 数据层的**薄壳类** |
| [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) | 面向 **Model（数据层）** 的静态助手集合 |
| [DuckPhp\Foundation\Model\ModelTrait](Foundation-Model-ModelTrait.md) | 数据模型（Model）的常用能力封装，被 Foundation\Model\B… |
| [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | MyFacadesAutoLoader 实现“Facade（门面）命名空间自动… |
| [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) | Facade（门面）类的基类 |
| [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | 中间件管理器扩展 |
| [DuckPhp\Component\Pager](Component-Pager.md) | Pager extends ComponentBase implements … |
| [DuckPhp\Component\PagerInterface](Component-PagerInterface.md) | PagerInterface 定义实现分页器所需的最小接口 |
| [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) | 「后台权限菜单树」构建器 |
| [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) | 「权限菜单元数据」契约接口 |
| [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) | DuckPHP 框架的“Phase 容器”，也是 SingletonExTra… |
| [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) | PhaseProxy 把一个“外部/由非默认 Phase 需要访问”的对象包起… |
| [DuckPhp\Component\RedisCache](Component-RedisCache.md) | RedisCache extends ComponentBase（注释 ali… |
| [DuckPhp\Component\RedisManager](Component-RedisManager.md) | RedisManager extends ComponentBase 是 Du… |
| [DuckPhp\Core\Route](Core-Route.md) | DuckPHP 的默认路由核心 |
| [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | “API 服务器”路由扩展 |
| [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | RouteHookDirectoryMode 实现“目录/文件模式”路由 |
| [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | “函数式路由”扩展 |
| [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) | 路由钩子列表的管理器 |
| [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | RouteHookPathInfoCompat extends Compone… |
| [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | RouteHookResource extends ComponentBase… |
| [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | RouteHookRewrite extends ComponentBase … |
| [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | RouteHookRouteMap extends ComponentBase… |
| [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | **网页安装向导** |
| [DuckPhp\Ext\RouteHookWebInstallerView](Ext-RouteHookWebInstallerView.md) | RouteHookWebInstaller 的**内置安装视图** |
| [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) | RouteLister extends ComponentBase 提供“把系… |
| [DuckPhp\Core\Runtime](Core-Runtime.md) | Runtime（class Runtime extends Component… |
| [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) | SessionTrait 为组合它的类提供**带前缀的 Session 读写** |
| [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) | DuckPHP 里“单例式访问”的常见来源 Trait |
| [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) | Foundation 层对 Core\SingletonExTrait 的**… |
| [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 数据库“结构/数据导出与安装”扩展 |
| [DuckPhp\Ext\SqlDumperSupporterByMysql](Ext-SqlDumperSupporterByMysql.md) | SqlDumperSupporter 的 **MySQL** 驱动实现 |
| [DuckPhp\Ext\SqlDumperSupporterByPgsql](Ext-SqlDumperSupporterByPgsql.md) | SqlDumperSupporter 的 **PostgreSQL** 驱动实现 |
| [DuckPhp\Ext\SqlDumperSupporterBySqlite](Ext-SqlDumperSupporterBySqlite.md) | SqlDumperSupporter 的 **SQLite** 驱动实现 |
| [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) | SqlDumper 的驱动适配基类 |
| [DuckPhp\Ext\StaticReplacer](Ext-StaticReplacer.md) | StaticReplacer 提供“把全局变量 / 函数内静态变量 / 类静态… |
| [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | SuperGlobal 提供在不污染全局符号的前提下操作 HTTP 超全局的手段 |
| [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) | **应用/接线层（System）**的静态助手集合（方法就在本类里，不再有 t… |
| [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) | SystemWrapper 把常见“副作用型”系统函数放到一处，便于 |
| [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) | ThrowOnTrait 提供静态条件抛出异常方法 |
| [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) | 「用户会话动作」的契约接口 |
| [DuckPhp\Foundation\Controller\UserControllerBase](Foundation-Controller-UserControllerBase.md) | 工程「前台登录用户控制器」的推荐基类 |
| [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) | 一个**空的标记接口**（marker interface，不声明任何方法） |
| [DuckPhp\GlobalUser\UserException](GlobalUser-UserException.md) | 用户（前台）领域的异常类 |
| [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) | 「用户登录动作」契约接口，与 UserActionInterface 分离，描… |
| [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) | 「用户登录服务」契约接口，描述服务侧的注册/登录/退出 |
| [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) | “用户服务”的契约接口，定义前台服务侧需要实现的三件事 |
| [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) | 「用户会话」契约接口 |
| [DuckPhp\GlobalUser\UserSessionTrait](GlobalUser-UserSessionTrait.md) | UserSessionInterface 的默认实现 Trait |
| [DuckPhp\Component\Validator](Component-Validator.md) | DuckPHP 的数据验证组件，采用“字段 => 规则字符串”的声明式写法 |
| [DuckPhp\Core\View](Core-View.md) | DuckPHP 的默认视图实现（class View extends Comp… |
<!-- GEN:az end -->
