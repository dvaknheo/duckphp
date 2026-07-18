# DuckPhp 框架架构分析

> 基于 `src/` 源代码全面研读结果
> 框架版本：1.3.5 | 命名空间：`DuckPhp` | 源码文件：约 86 个 PHP 文件

---

## 一、整体架构层次

```
DuckPhp/
├── Core/          # 核心层 — 框架基础
├── Component/     # 组件层 — 可插拔功能
├── Ext/           # 扩展层 — 额外功能
├── Db/            # 数据库层 — PDO 封装
├── Helper/        # 辅助层 — 便捷静态方法 trait
├── Foundation/    # 基础层 — 预置的 MVC 基类
├── GlobalAdmin/   # 管理员认证抽象
├── GlobalUser/    # 用户认证抽象
├── HttpServer/    # PHP 内置服务器运行器
├── FastInstaller/ # 快速安装器
├── DuckPhp.php          # 主入口类
└── DuckPhpAllInOne.php  # All-in-One 便捷版本
```

### 1.1 源码文件清单

| 目录 | 文件 | 数量 |
|------|------|------|
| **Core/** | App, KernelTrait, ComponentBase, PhaseContainer, SingletonTrait, Route, Runtime, Console, View, ExceptionManager, SuperGlobal, SystemWrapper, Logger, EventManager, AutoLoader, CoreHelper, Functions, ThrowOnTrait, ComponentInterface, DuckPhpSystemException, ExitException | ~22 |
| **Component/** | Cache, Command, Configer, DbManager, ExtOptionsLoader, GlobalEvent, Lang, Pager, PagerInterface, PhaseProxy, RedisCache, RedisManager, RouteHookCheckStatus, RouteHookPathInfoCompat, RouteHookResource, RouteHookRewrite, RouteHookRouteMap, ZCallTrait | ~18 |
| **Db/** | Db, DbAdvanceTrait, DbInterface | 3 |
| **Ext/** | CallableView, DuckPhpInstaller, EmptyView, ExceptionWrapper, ExtendableStaticCallTrait, FinderForController, HookChain, JsonRpcClientBase, JsonRpcExt, JsonView, MiniRoute, Misc, MyFacadesAutoLoader, MyFacadesBase, MyMiddlewareManager, RouteHookApiServer, RouteHookDirectoryMode, RouteHookFunctionRoute, RouteHookManager, StaticReplacer, StrictCheck | ~21 |
| **Helper/** | AppHelperTrait, BusinessHelperTrait, ControllerHelperTrait, ModelHelperTrait | 4 |
| **Foundation/** | Helper, ExceptionReporterTrait; Controller/Base, Controller/Helper; Model/Base, Model/Helper; Business/Base, Business/Helper; BusinessTrait, ControllerTrait, ExceptionTrait, ModelTrait, SessionTrait, SingletonTrait; System/Helper | ~16 |
| **GlobalAdmin/** | GlobalAdmin, AdminActionInterface, AdminControllerInterface, AdminException, AdminServiceInterface | 5 |
| **GlobalUser/** | GlobalUser, UserActionInterface, UserControllerInterface, UserException, UserServiceInterface | 5 |
| **HttpServer/** | HttpServer, HttpServerInterface | 2 |
| **FastInstaller/** | FastInstaller, DatabaseInstaller, RedisInstaller, SqlDumper, Supporter, SupporterByMysql, SupporterByPgsql, SupporterBySqlite | 8 |
| 根目录 | DuckPhp, DuckPhpAllInOne | 2 |

---

## 二、核心层详解 (Core/)

### 2.1 App.php — 应用主类

- 继承 `ComponentBase`，使用 `KernelTrait`
- 常量 `VERSION = '1.3.5'`
- `__construct()` 中合并 `kernel_options` + `core_options` + `common_options` + 用户 options
- 派生了 `DuckPhp` 和 `DuckPhpAllInOne` 两个子类

**核心方法：**
- `version()` / `Setting()` / `IsDebug()` / `Platform()` — 配置与状态
- `_On404()` — 404 处理，支持自定义视图
- `_OnDefaultException($ex)` — 500 异常处理，记录日志 + 显示错误
- `_OnDevErrorHandler()` — 开发期错误显示
- `getOverrideableFile()` — Phase 感知的文件覆盖查找
- `skip404Handler()` / `adjustViewFile()` — 实用辅助

### 2.2 KernelTrait.php — 核心逻辑

所有应用逻辑的实际载体。

**初始化流程 (`init()`)：**
```
initOptions → initContainer → initException → onPrepare
→ initComponents → initExtensions → onIniting
→ onBeforeChildrenInit → initChildren → is_inited=true → onInited
```

**请求处理流程 (`serve()`)：**
```
prepareServe → onServe → onBeforeRun → Runtime::run()
→ Route::run() (含 Hook 链) → runChildren() → _On404()(fallback)
→ finally → Runtime::clear() → onAfterRun
```

**CLI 处理流程 (`execute()`)：**
```
phaseToCurrent → Runtime::run() → Console::run() → (异常) → Runtime::clear()
```

**关键设计：**
- `initChildren()` — 解析 `app` 选项，创建子应用，每个子应用独立 Phase
- `runChildren()` — 遍历子应用执行 `serve()`
- `phaseToCurrent()` — 切换到当前 Phase
- `initExtensions()` — 初始化 `ext` 扩展

### 2.3 ComponentBase.php — 组件基类

- 使用 `SingletonTrait`
- `init(array $options, ?object $context)` — 模板方法，子类可重写 `initOptions()` 和 `initContext()`
- `init_once` 防止重复初始化
- `context()` 获取所属 App 实例
- `extendFullFile()` — 文件路径查找（支持 App 的 Phase 覆盖）
- `IsAbsPath()` / `SlashDir()` — 跨平台路径工具

### 2.4 PhaseContainer.php — 阶段/Phase 容器

框架的核心创新设计。

- **Phase** = 一个独立的实例空间，用字符串标识（如 `""` 为根 Phase，`"sub:ns/Controller"` 为子应用 Phase）
- 默认共享容器为 `@public@`
- `_GetObject()` 查找顺序：当前 Phase → 公共容器 → 自动创建
- `addPublicClasses()` / `createLocalObject()` / `removeLocalObject()`
- 支持 `dumpAllObject()` 调试输出

### 2.5 SingletonTrait.php — 单例 Trait

```php
public static function _($object = null)
{
    return PhaseContainer::GetObject(static::class, $object);
}
```

所有组件通过 `ClassName::_()` 获取实例，由 `PhaseContainer` 统一管理。

### 2.6 Route.php — 路由核心

**URL → Controller/Method 解析：**
1. `getPathInfo()` 获取 PATH_INFO
2. `pathToClassAndMethod()` — 解析路径到类名和方法
3. `getCallbackFromClassAndMethod()` — 通过反射验证并创建实例

**路由 Hook 机制：**
- `addRouteHook($callback, $position)` — 4 个位置：
  - `'prepend-outter'` → 最外前置
  - `'prepend-inner'` → 最内前置
  - `'append-inner'` → 最内后置
  - `'append-outter'` → 最外后置
- 执行顺序：`prepend-outter → prepend-inner → 默认路由 → append-inner → append-outter`

**路由配置选项：**
- `controller_url_prefix` / `controller_welcome_class` / `controller_welcome_method`
- `controller_class_postfix` / `controller_method_prefix`
- `controller_class_map` / `controller_class_base`
- `controller_fix_mistake_path_info` / `controller_path_ext`
- `controller_resource_prefix` / `controller_prefix_post`

**URL 管理 (`Route_UrlManager`)：**
- `_Url()` — 生成应用内 URL
- `_Res()` — 生成资源 URL（支持 CDN）
- `_Domain()` — 获取当前域名

### 2.7 Console.php — CLI 命令行处理

- 解析 `$_SERVER['argv']` 为命名参数和位置参数
- 支持命名空间式命令：`namespace:sub:command`
- `getCommandCallback()` — 查找匹配的命令类/方法
- `callObject()` — 通过反射调用命令方法，自动注入参数
- `readLines()` — 交互式命令行输入

### 2.8 ExceptionManager.php — 异常/错误处理

- 注册 `set_error_handler()` 和 `set_exception_handler()`
- 按异常类层次分发到已注册的 handler
- `assignExceptionHandler($class, $callback)` — 注册特定异常处理器
- 支持 `exception_reporter` 用于项目级异常报告

### 2.9 SuperGlobal.php — 超全局变量封装

- 属性：`$_GET` / `$_POST` / `$_REQUEST` / `$_SERVER` / `$_COOKIE` / `$_SESSION` / `$_FILES`
- `DefineSuperGlobalContext()` — 定义 `__SUPERGLOBAL_CONTEXT` 常量
- 定义后，通过 `__SUPERGLOBAL_CONTEXT()->_SERVER` 等方式访问
- 支持 `_LoadSuperGlobalAll()` / `_SaveSuperGlobalAll()` 批量导入/导出
- 提供 `_GET()` / `_POST()` / `_SERVER()` / `_SESSION()` / `_CookieSet()` 等方法

### 2.10 SystemWrapper.php — 系统函数包装

可替换的系统函数：`header`, `setcookie`, `exit`, `set_exception_handler`, `register_shutdown_function`, `session_start`, `session_id`, `session_destroy`, `session_set_save_handler`, `mime_content_type`

- `system_wrapper_replace()` — 注入自定义实现
- `__SYSTEM_WRAPPER_REPLACER` 常量完全替换

### 2.11 其他核心文件

| 文件 | 功能简述 |
|------|----------|
| **View.php** | 视图渲染，`_Show()` / `_Display()` / `_Render()`，支持 head/foot 包裹 |
| **Runtime.php** | output buffer 控制，运行状态追踪 |
| **Logger.php** | PSR-3 风格日志，写入文件，支持日期模板 |
| **EventManager.php** | `on()` / `fire()` / `remove()` 事件系统 |
| **AutoLoader.php** | PSR-4 自动加载，支持 opcache 预编译 |
| **CoreHelper.php** | 辅助函数集，暴露为全局 `__h()` `__l()` `__url()` 等 |
| **Functions.php** | 全局函数定义 |
| **ThrowOnTrait.php** | `ThrowOn()` 快速条件异常抛出 |

---

## 三、关键设计模式

### 3.1 Phase 容器机制

PhaseContainer 是框架的核心 DI 容器：

```
每个 App 实例运行在一个 "Phase"（阶段空间）中
├── 根 Phase: ""（空字符串）
├── 子应用 Phase: "sub:namespace/Controller"
└── 共享对象 Phase: "@public@"

查找对象：当前 Phase → 公共容器 → 自动创建并注册
```

- `App::Phase($new)` — 切换/获取当前 Phase
- `App::Root()` — 获取根 App 实例
- `App::FromCurrentParent()` — 获取父级 App
- `PhaseProxy` — 跨 Phase 调用代理，`_Z()` 方法创建

### 3.2 组件化初始化

```
ComponentBase::init()
  ├── initOptions($options)    ← 子类覆盖
  ├── initContext($context)    ← 子类覆盖
  └── is_inited = true
```

每个组件可独立初始化，`init_once` 防止重复。

### 3.3 路由钩子系统

```
pre_run_hook_list (从外到内):
  prepend-outter → prepend-inner → 默认路由

post_run_hook_list (从内到外):
  append-inner → append-outter
```

框架组件通过这个机制插入：
- `RouteHookRewrite` → prepend-outter（URL 重写）
- `RouteHookRouteMap` → prepend-inner + append-outter（路由映射）
- `RouteHookResource` → append-outter（静态资源）
- `RouteHookCheckStatus` → prepend-outter（状态检查）

### 3.4 多应用/子应用架构

通过 `app` 选项配置：

```php
$options = [
    'app' => [
        'MyApp\\Admin\\App' => [
            'namespace' => 'MyApp\\Admin',
            'controller_url_prefix' => 'admin',
        ],
        'MyApp\\Api\\App' => [
            'namespace' => 'MyApp\\Api',
            'controller_url_prefix' => 'api',
        ],
    ],
];
```

每个子应用：
- 拥有独立 Phase 空间和独立对象实例
- 通过 `controller_url_prefix` 与 URL 路径匹配
- 可独立 `serve()` 处理请求
- URL 按前缀分发到对应子应用

### 3.5 单例与对象管理

```php
// 获取实例
$obj = ClassName::_();

// 设置实例
ClassName::_($instance);

// 跨 Phase 代理
$proxy = SomeClass::_Z('target-phase');
$proxy->someMethod();
```

---

## 四、组件层 (Component/)

| 类名 | 功能 | 初始化时机 |
|------|------|-----------|
| **Configer** | 从 `config/` 目录加载 PHP 配置数组，惰性加载 | 首次 `_Config()` 调用时 |
| **DbManager** | 数据库连接管理，支持读写分离标签（TAG_WRITE=0, TAG_READ=1），惰性连接 | App.init() 时 |
| **RedisManager** | Redis 连接管理 | App.init() 时 |
| **RouteHookRewrite** | URL 重写，支持正则/精确匹配两种模式 | 通过 `Route::addRouteHook()` |
| **RouteHookRouteMap** | 路由映射表，支持 `@compile` 编译模式、`*` 通配符、`^regex$` 正则 | 通过 `Route::addRouteHook()` |
| **RouteHookResource** | 静态资源文件服务，直接从 `res/` 目录提供 | 通过 `Route::addRouteHook()` |
| **RouteHookPathInfoCompat** | PATH_INFO 兼容性处理 | App.init() 可选 |
| **RouteHookCheckStatus** | 安装/维护状态检查 | 通过 `Route::addRouteHook()` |
| **Lang** | 多语言，支持 URL参数/Cookie/HTTP头/CLI变量/默认 五级检测 | 作为 `ext` 初始化 |
| **Pager** | 分页，自动从 URL 获取当前页码，生成分页 HTML | 按需使用 |
| **Cache** | 简单缓存接口（默认空实现） | 按需使用 |
| **PhaseProxy** | Phase 间调用代理 | 通过 `_Z()` 创建 |
| **ZCallTrait** | 提供 `_Z()` 方法 | 作为 trait 使用 |
| **ExtOptionsLoader** | 扩展选项持久化存储（JSON 文件） | App.init() 可选 |
| **Command** | 内置 CLI 命令：`version`, `help`, `run`, `fetch`, `call`, `debug` | App CLI 模式 |
| **GlobalEvent** | 全局事件发布 | App.init() 时 |

### 4.1 URL 重写 (RouteHookRewrite)

- 精确匹配：直接比较路径
- 正则匹配：`~` 前缀，使用 `preg_replace`
- 替换后同时更新 `$_GET` 和 PATH_INFO

### 4.2 路由映射 (RouteHookRouteMap)

- 前置映射 (`route_map_important`)：在默认路由前匹配
- 后置映射 (`route_map`)：在默认路由后匹配
- 三种匹配模式：`/path` 精确、`/path*` 通配符、`^regex$` 正则
- `@compile` 模式编译路由参数：`@/article/{id:\d+}` → `(?<id>\d+)`
- 回调类型：字符串 `Class@method`、`Class->method`、可调用数组

---

## 五、数据库层 (Db/)

### DbInterface

```php
interface DbInterface {
    close(), PDO(), quote(), fetchAll(), fetch(), fetchColumn(), execute(), rowCount(), lastInsertId()
}
```

### Db — PDO 实现

- 基于 `PDO`，属性：`pdo`, `config`, `driver`, `tableName`
- 连接配置：`dsn`, `username`, `password`, `driver_options`
- 支持 SQLite 路径自动补全
- `exec()` — 统一的执行入口，支持命名参数和位置参数
- `table('table_name')` — 设置表名，在 SQL 中 `\`'TABLE'\`` 被替换
- `beforeQueryHandler` — SQL 执行前回调（用于日志记录）
- 驱动程序检测：`mysql`, `sqlite`, `pgsql`

### DbManager

- 管理数据库连接池 `databases[]`
- 标签索引：0=写库, 1=读库
- `_DbForRead()` — 若没有配置读库则回退到写库
- `_DbCloseAll()` — 关闭所有连接
- 从 `setting` 或 `options` 加载数据库配置

---

## 六、Helper 与 Foundation 层

### 6.1 Helper Trait 体系

四个 Helper Trait 提供便捷静态方法：

| Trait | 主要方法 |
|-------|---------|
| **ModelHelperTrait** | `Db()`, `DbForRead()`, `DbForWrite()`, `SqlForPager()`, `SqlForCountSimply()` |
| **BusinessHelperTrait** | `Setting()`, `Config()`, `XpCall()`, `BusinessThrowOn()`, `Cache()`, `FireEvent()`, `OnEvent()`, `AdminService()`, `UserService()` |
| **ControllerHelperTrait** | `Setting()`, `Config()`, `Url()`, `Res()`, `Domain()`, `Parameter()`, `Render()`, `Show()`, `ShowJson()`, `Show302()`, `Show404()`, `header()`, `setcookie()`, `exit()`, `GET()`, `POST()`, `REQUEST()`, `COOKIE()`, `SERVER()`, `Pager()`, `Admin()`, `User()`, `ControllerThrowOn()` |
| **AppHelperTrait** | `addRouteHook()`, `replaceController()`, `assignRoute()`, `assignRewrite()`, `DbCloseAll()`, `SessionSet()`, `SessionGet()`, `CookieSet()`, `CookieGet()`, `system_wrapper_replace()`, `Redis()`, `getCliParameters()` |

### 6.2 Foundation\Helper

将四个 Helper Trait 合并为一个类，解决方法冲突（如 `Setting`/`Config` 优先使用 Business 版本）。

### 6.3 MVC 基类

- `Foundation\Model\Base` — 使用 `ModelTrait`
- `Foundation\Controller\Base` — 使用 `ControllerTrait`
- `Foundation\Business\Base` — 使用 `BusinessTrait`

### 6.4 ControllerTrait

重写了 `_()` 方法，判断当前类是否符合 Controller 规则（postfix + base_class 检查），如果是则通过反射创建实例（跳过构造函数），否则走容器管理。

- `_Z()` — 创建 Phase 代理
- `OverrideParent()` — 替换父类控制器映射

---

## 七、用户认证体系

### GlobalAdmin

| 方法 | 用途 |
|------|------|
| `service()` | 获取管理员服务实现 |
| `id($check_login)` | 获取管理员 ID |
| `name($check_login)` | 获取管理员名称 |
| `login($post)` | 登录 |
| `logout()` | 登出 |
| `checkAccess($class, $method)` | 权限检查 |
| `isSuper()` | 是否超级管理员 |
| `log($string, $type)` | 操作日志 |

接口：`AdminActionInterface`, `AdminControllerInterface`, `AdminServiceInterface`

### GlobalUser

接口：`UserActionInterface`, `UserControllerInterface`, `UserServiceInterface`

两者均使用 `ZCallTrait` 支持跨 Phase 调用。

---

## 八、重要常量与特性

| 常量 | 用途 |
|------|------|
| **`__SUPERGLOBAL_CONTEXT`** | 定义后所有对 `$_GET`/`$_POST`/`$_SERVER` 等的访问改为通过 `SuperGlobal` 对象 |
| **`__SYSTEM_WRAPPER_REPLACER`** | 系统函数完全替换器 |
| **`__EXIT_EXCEPTION`** | `exit()` 调用改为抛出此类异常（可捕获，不终止） |

---

## 九、初始化流程详细时序

```
用户代码:  $app = MyApp::_()->init($options);

1. initOptions($options)
   └── 合并 kernel_options + core_options + common_options + 用户 options

2. initContainer($context)
   ├── 根 App: 注册 PhaseContainer，设置 Phase=""
   └── 子 App: 生成 Phase 名 "parent:name"，注册到容器

3. initException($options)
   └── ExceptionManager 初始化，注册 error/exception handler

4. onPrepare() ← 可重写

5. initComponents()
   ├── Console.init() — CLI 命令注册
   ├── Route.init() — 路由初始化
   └── Runtime.init() — 运行时设置

6. initExtensions($ext)
   └── 遍历 ext 配置，初始化扩展组件

7. onIniting() ← 可重写（回调）

8. onBeforeChildrenInit() ← 可重写

9. initChildren($app)
   └── 遍历 app 配置，创建子 App

10. is_inited = true

11. onInited() ← 可重写（回调）
```

---

## 十、请求处理详细时序

```
$app->serve():

1. prepareServe()
   ├── phaseToCurrent()
   └── 维护模式检查（可选）

2. onServe() ← 可重写

3. onBeforeRun() ← 可重写

4. try:
   ├── Runtime::run() — 开启 output buffer（可选）
   ├── Route::run()
   │   ├── prepend-outter hooks（如 RouteHookRewrite）
   │   ├── prepend-inner hooks（如 RouteHookRouteMap 重要路由）
   │   ├── 默认路由（PathInfo → Controller::action）
   │   ├── append-inner hooks
   │   └── append-outter hooks（如 RouteHookResource, RouteHookRouteMap 普通路由）
   ├── runChildren() — 子应用尝试处理
   └── _On404() — 兜底 404
   catch: runException($ex)
   finally: Runtime::clear() — 刷新 output buffer

5. onAfterRun() ← 可重写
```

---

## 十一、扩展机制 (Ext/)

| 扩展 | 功能 |
|------|------|
| **CallableView** | 可调用方法作为视图（用于 `DuckPhpAllInOne`） |
| **EmptyView** | 空视图实现 |
| **JsonView** | JSON 视图 |
| **MiniRoute** | 简化版路由（无 class_postfix/method_prefix） |
| **HookChain** | Hook 链管理 |
| **RouteHookManager** | Route Hook 的增删改查管理 |
| **RouteHookApiServer** | API 服务器路由 Hook |
| **RouteHookDirectoryMode** | 目录模式路由 Hook |
| **RouteHookFunctionRoute** | 函数路由 Hook |
| **RouteHookManager** | Route Hook 管理器 |
| **JsonRpcExt** / **JsonRpcClientBase** | JSON-RPC 支持 |
| **ExceptionWrapper** | 异常包装 |
| **StaticReplacer** | 静态方法替换 |
| **ExtendableStaticCallTrait** | 可扩展静态调用 |
| **StrictCheck** | 严格模式检查 |
| **FinderForController** | 控制器查找器 |
| **MyFacadesAutoLoader** / **MyFacadesBase** | 门面模式自动加载 |
| **MyMiddlewareManager** | 中间件管理器 |
| **Misc** | 杂项工具 |
| **DuckPhpInstaller** | 安装器扩展 |

---

## 十二、DuckPhpAllInOne 便捷模式

`DuckPhpAllInOne` 继承了 `DuckPhp`，将 App/Controller/View 合并为一个类：

- Controller 方法：`action_xxx()`
- View 方法：`view_xxx()`
- 默认内嵌 index 页面
- 自动设置 head/foot 视图包裹

适合简单应用或快速原型开发。

---

## 十三、项目位置

- 仓库：`E:\ProjectGoat\DNMVCS`
- 源码：`E:\ProjectGoat\DNMVCS\src`
- PHP 版本要求：PHP 8.0+（使用 `declare(strict_types=1)`, 反射等特性）

---

*文档生成日期：2026-06-18*
*基于 DuckPhp v1.3.5 源码分析*
