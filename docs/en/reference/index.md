# DuckPhp Reference Manual

> This manual answers "**what is there**": the namespace, declaration, options, method signatures and caveats of every class / interface / Trait — every statement here can be found word for word in `src/`.
> To learn "**how to do it**", read the user guide (`docs/en/guide/`): it covers the how, and links the mechanism details back to this manual.

## Quick entry points

| What you want | Where to go |
|---|---|
| The full picture of a class | the [category navigation](#category-navigation) or the [full index (by class name)](#full-index-by-class-name) below |
| The meaning and default of an option | [Application options overview (home)](options.md) → [by class](options-by-class.md) / [A-Z index](options-index.md) |
| How `options` and `setting` divide the work | [Application settings (Setting)](setting.md) |
| Global functions (`__h()` and friends) | [DuckPhp\Core\Functions](Core-Functions.md) |
| How to read a per-class page | see [how to use this manual](#how-to-use-this-manual) below |

## Book statistics

<!-- GEN:stats start -->
| Item | Count |
|---|---|
| Per-class reference pages | 107 pages |
| Classes declaring options | 40 classes |
| Application options (de-duplicated) | 207 options |
| Application options (hidden) | 11 options |
<!-- GEN:stats end -->

## How to use this manual

- **A fixed layout for per-class pages**: Introduction → Class info → Options → Usage → Configuration example → Caveats → All options → Methods → Related links. Jump by that layout when you are looking for something.
- **The method lists**: an entry is "a signature indented by four spaces plus one sentence"; static shells and instance implementations are listed separately (such as `Show()` and `_Show()`); methods provided by a Trait are not repeated, only their origin is named and linked.
- **"Caveats" records the source's temper**: the traps that were stepped in, the counter-intuitive behaviour that is deliberate, and the reminder that "the source is authoritative".
- **A link is proof of existence**: this manual only links files that really exist, so a link that opens means the page is really there.
- **Planned pages** follow the user guide's convention: plain text plus `⏳` and no link, to avoid dead links. All 114 pages are written today, so `⏳` appears only while a **new** page is still unfinished.

## Category navigation

<!-- GEN:nav start -->
## Entry classes

| Class | Description |
|---|---|
| [DuckPhp\DuckPhp](DuckPhp.md) | DuckPhp is a subclass of DuckPhp\Core\App. |
| [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) | DuckPhpAllInOne extends DuckPhp is the "the whole… |

## Core classes

| Class | Description |
|---|---|
| [DuckPhp\Core\App](Core-App.md) | App lives at DuckPhp\Core\App: class App extends… |
| [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | Core\AutoLoader provides a very light "namespace →… |
| [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) | ComponentBase is the base class of the vast majority of… |
| [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) | ComponentInterface is the contract interface for DuckPHP… |
| [DuckPhp\Core\Console](Core-Console.md) | Console is DuckPHP's command-handling root for the CLI:… |
| [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) | CoreHelper is the facade where the framework collects… |
| [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) | The general exception base class thrown by the system,… |
| [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | ExceptionManager (class ExceptionManager extends… |
| [DuckPhp\Core\ExitException](Core-ExitException.md) | Used to turn places where "the program should end early"… |
| [DuckPhp\Core\Functions](Core-Functions.md) | src/Core/Functions.php defines a set of global functions… |
| [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | KernelTrait writes "what an application is and how it… |
| [DuckPhp\Core\Logger](Core-Logger.md) | Logger (PSR-3 by comment, not implements, best-effort… |
| [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) | PhaseContainer is DuckPHP's "phase container", and the… |
| [DuckPhp\Core\Route](Core-Route.md) | Route is DuckPHP's default routing core. |
| [DuckPhp\Core\Runtime](Core-Runtime.md) | Runtime (class Runtime extends ComponentBase) is not a big… |
| [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) | SingletonExTrait is the common source of "singleton-style… |
| [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | SuperGlobal provides ways to operate on the HTTP… |
| [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) | SystemWrapper puts the common "side-effect" system… |
| [DuckPhp\Core\View](Core-View.md) | View is DuckPHP's default view implementation (class View… |

## Components

| Class | Description |
|---|---|
| [DuckPhp\Component\Cache](Component-Cache.md) | Cache extends ComponentBase is the fallback for the whole… |
| [DuckPhp\Component\Command](Component-Command.md) | Command extends ComponentBase is the command pack DuckPHP… |
| [DuckPhp\Component\CommandMetaInterface](Component-CommandMetaInterface.md) | CommandMetaInterface is the "command table metadata"… |
| [DuckPhp\Component\Configer](Component-Configer.md) | Configer extends ComponentBase reads {file_basename}.php… |
| [DuckPhp\Component\DbManager](Component-DbManager.md) | DbManager extends ComponentBase is DuckPHP's single entry… |
| [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | ExtOptionsLoader handles a class of dynamic options that… |
| [DuckPhp\Component\GlobalEvent](Component-GlobalEvent.md) | GlobalEvent extends ComponentBase provides a small… |
| [DuckPhp\Component\Lang](Component-Lang.md) | Lang extends ComponentBase provides simple but complete UI… |
| [DuckPhp\Component\Pager](Component-Pager.md) | Pager extends ComponentBase implements PagerInterface… |
| [DuckPhp\Component\PagerInterface](Component-PagerInterface.md) | PagerInterface defines the minimal interface a pager… |
| [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) | PhaseProxy wraps an object that is "external / needed by a… |
| [DuckPhp\Component\RedisCache](Component-RedisCache.md) | RedisCache extends ComponentBase (comments align with… |
| [DuckPhp\Component\RedisManager](Component-RedisManager.md) | RedisManager extends ComponentBase is the manager DuckPHP… |
| [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | When enabled (path_info_compact_enable not false at init),… |
| [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | RouteHookResource extends ComponentBase has two sides: 1. |
| [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | RouteHookRewrite extends ComponentBase mainly serves… |
| [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | RouteHookRouteMap extends ComponentBase works through two… |
| [DuckPhp\Component\Validator](Component-Validator.md) | Validator is DuckPHP's data validation component, using a… |

## Extensions

| Class | Description |
|---|---|
| [DuckPhp\Ext\CallableView](Ext-CallableView.md) | CallableView extends Core\View: it replaces the "view"… |
| [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | DuckPhpInstaller is the CLI installer behind bin/duckphp,… |
| [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | EmptyView extends Core\View: it does **not render template… |
| [DuckPhp\Ext\EventManager](Ext-EventManager.md) | EventManager is a simple event manager extension: it… |
| [DuckPhp\Ext\ExceptionWrapper](Ext-ExceptionWrapper.md) | ExceptionWrapper is an "exception-safe call wrapper": once… |
| [DuckPhp\Ext\ExtendableStaticCallTrait](Ext-ExtendableStaticCallTrait.md) | ExtendableStaticCallTrait gives a class the ability to… |
| [DuckPhp\Ext\HookChain](Ext-HookChain.md) | HookChain represents a "chain of callbacks": on __invoke()… |
| [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) | JsonRpcClientBase is the JSON-RPC **client** base class:… |
| [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | JsonRpcExt is the master control of the JSON-RPC… |
| [DuckPhp\Ext\JsonView](Ext-JsonView.md) | JsonView extends Core\View: it changes the rendering… |
| [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | MyFacadesAutoLoader implements "facade namespace… |
| [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) | MyFacadesBase is the base class for Facade classes: any… |
| [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | MyMiddlewareManager is the middleware-manager extension:… |
| [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) | PermissionMenu is the builder of the "back-office… |
| [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) | PermissionMenuMetaInterface is the contract interface for… |
| [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | RouteHookApiServer is the "API server" route extension: it… |
| [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | RouteHookDirectoryMode implements "directory/file mode"… |
| [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | RouteHookFunctionRoute is the "function-style routing"… |
| [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) | RouteHookManager is a manager for a route-hook list: it… |
| [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | RouteHookWebInstaller is the **web installation wizard**:… |
| [DuckPhp\Ext\RouteHookWebInstallerView](Ext-RouteHookWebInstallerView.md) | RouteHookWebInstallerView is the **built-in install view**… |
| [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) | RouteLister extends ComponentBase provides the ability to… |
| [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | SqlDumper is the database "schema/data export and… |
| [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) | SqlDumperSupporter is the driver-adapter base class for… |
| [DuckPhp\Ext\SqlDumperSupporterByMysql](Ext-SqlDumperSupporterByMysql.md) | SqlDumperSupporterByMysql is the **MySQL** driver… |
| [DuckPhp\Ext\SqlDumperSupporterByPgsql](Ext-SqlDumperSupporterByPgsql.md) | SqlDumperSupporterByPgsql is the **PostgreSQL** driver… |
| [DuckPhp\Ext\SqlDumperSupporterBySqlite](Ext-SqlDumperSupporterBySqlite.md) | SqlDumperSupporterBySqlite is the **SQLite** driver… |
| [DuckPhp\Ext\StaticReplacer](Ext-StaticReplacer.md) | StaticReplacer can simulate "global variables /… |
| [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) | ThrowOnTrait provides a static conditional-throw method. |

## Database

| Class | Description |
|---|---|
| [DuckPhp\Db\Db](Db-Db.md) | Db is DuckPHP's default database connection object; it… |
| [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) | DbAdvanceTrait is a set of methods added to a Db… |
| [DuckPhp\Db\DbInterface](Db-DbInterface.md) | DbInterface is the contract interface for DuckPHP's… |

## HTTP server

| Class | Description |
|---|---|
| [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | HttpServer is DuckPHP's built-in launcher for "running a… |
| [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) | HttpServerInterface is the contract for DuckPHP's built-in… |

## Helpers

| Class | Description |
|---|---|
| [DuckPhp\Foundation\Business\Base](Foundation-Business-Base.md) | Business\Base is the recommended base class (abstract) for… |
| [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) | BusinessHelper is a collection of static helpers aimed at… |
| [DuckPhp\Foundation\Controller\ActionBase](Foundation-Controller-ActionBase.md) | ActionBase is the recommended base class (abstract) for a… |
| [DuckPhp\Foundation\Controller\AdminControllerBase](Foundation-Controller-AdminControllerBase.md) | AdminControllerBase is the recommended base class for a… |
| [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) | Foundation\Controller\Base is the recommended base class… |
| [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) | ControllerHelper is the collection of static helpers aimed… |
| [DuckPhp\Foundation\Controller\ExceptionReporterTrait](Foundation-Controller-ExceptionReporterTrait.md) | ExceptionReporterTrait is the recommended implementation… |
| [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) | SessionTrait gives the class that composes it **prefixed… |
| [DuckPhp\Foundation\Controller\UserControllerBase](Foundation-Controller-UserControllerBase.md) | UserControllerBase is the recommended base class for a… |
| [DuckPhp\Foundation\Helper](Foundation-Helper.md) | Foundation\Helper is the "union of the four Helper layers"… |
| [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) | Model\Base is the recommended base class (abstract) for a… |
| [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) | Model\ModelHelper is a **thin shell class** for the data… |
| [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) | ModelHelperTrait is a collection of static helpers aimed… |
| [DuckPhp\Foundation\Model\ModelTrait](Foundation-Model-ModelTrait.md) | ModelTrait is the packaging of the common capabilities of… |
| [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) | SingletonTrait is the Foundation layer's **thin wrapper**… |
| [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) | SystemHelper is the collection of static helpers for the… |

## Admin system

| Class | Description |
|---|---|
| [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-Admin.md) | Admin is the **base class (default implementation)** of… |
| [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) | AdminActionInterface is the contract interface for… |
| [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) | AdminControllerInterface is an **empty marker interface**… |
| [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) | AdminLoginActionInterface is the "admin login action"… |
| [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) | AdminLoginServiceInterface is the "admin login service"… |
| [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) | AdminServiceInterface is the contract for the "admin… |
| [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) | AdminSessionInterface is the "admin session" contract: it… |
| [DuckPhp\GlobalAdmin\AdminSessionTrait](GlobalAdmin-AdminSessionTrait.md) | AdminSessionTrait is the default implementation of… |
| [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | GlobalAdmin is the **complete implementation** of the… |

## User system

| Class | Description |
|---|---|
| [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | GlobalUser is the **complete implementation** of the user… |
| [DuckPhp\GlobalUser\User](GlobalUser-User.md) | User is the **base class (default implementation)** of the… |
| [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) | UserActionInterface is the contract interface for "user… |
| [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) | UserControllerInterface is an **empty marker interface**… |
| [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) | UserLoginActionInterface is the "user login action"… |
| [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) | UserLoginServiceInterface is the "user login service"… |
| [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) | UserServiceInterface is the contract for the "user… |
| [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) | UserSessionInterface is the "user session" contract: it… |
| [DuckPhp\GlobalUser\UserSessionTrait](GlobalUser-UserSessionTrait.md) | UserSessionTrait is the default implementation of… |
<!-- GEN:nav end -->

## Full index (by class name)

<!-- GEN:az start -->
| Class | One-liner |
|---|---|
| [DuckPhp\Foundation\Controller\ActionBase](Foundation-Controller-ActionBase.md) | ActionBase is the recommended base class (abstract) for a… |
| [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) | AdminActionInterface is the contract interface for… |
| [DuckPhp\Foundation\Controller\AdminControllerBase](Foundation-Controller-AdminControllerBase.md) | AdminControllerBase is the recommended base class for a… |
| [DuckPhp\GlobalAdmin\AdminControllerInterface](GlobalAdmin-AdminControllerInterface.md) | AdminControllerInterface is an **empty marker interface**… |
| [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-Admin.md) | Admin is the **base class (default implementation)** of… |
| [DuckPhp\GlobalAdmin\AdminLoginActionInterface](GlobalAdmin-AdminLoginActionInterface.md) | AdminLoginActionInterface is the "admin login action"… |
| [DuckPhp\GlobalAdmin\AdminLoginServiceInterface](GlobalAdmin-AdminLoginServiceInterface.md) | AdminLoginServiceInterface is the "admin login service"… |
| [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) | AdminServiceInterface is the contract for the "admin… |
| [DuckPhp\GlobalAdmin\AdminSessionInterface](GlobalAdmin-AdminSessionInterface.md) | AdminSessionInterface is the "admin session" contract: it… |
| [DuckPhp\GlobalAdmin\AdminSessionTrait](GlobalAdmin-AdminSessionTrait.md) | AdminSessionTrait is the default implementation of… |
| [DuckPhp\Core\App](Core-App.md) | App lives at DuckPhp\Core\App: class App extends… |
| [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | Core\AutoLoader provides a very light "namespace →… |
| [DuckPhp\Foundation\Business\Base](Foundation-Business-Base.md) | Business\Base is the recommended base class (abstract) for… |
| [DuckPhp\Foundation\Controller\Base](Foundation-Controller-Base.md) | Foundation\Controller\Base is the recommended base class… |
| [DuckPhp\Foundation\Model\Base](Foundation-Model-Base.md) | Model\Base is the recommended base class (abstract) for a… |
| [DuckPhp\Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) | BusinessHelper is a collection of static helpers aimed at… |
| [DuckPhp\Component\Cache](Component-Cache.md) | Cache extends ComponentBase is the fallback for the whole… |
| [DuckPhp\Ext\CallableView](Ext-CallableView.md) | CallableView extends Core\View: it replaces the "view"… |
| [DuckPhp\Component\Command](Component-Command.md) | Command extends ComponentBase is the command pack DuckPHP… |
| [DuckPhp\Component\CommandMetaInterface](Component-CommandMetaInterface.md) | CommandMetaInterface is the "command table metadata"… |
| [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) | ComponentBase is the base class of the vast majority of… |
| [DuckPhp\Core\ComponentInterface](Core-ComponentInterface.md) | ComponentInterface is the contract interface for DuckPHP… |
| [DuckPhp\Component\Configer](Component-Configer.md) | Configer extends ComponentBase reads {file_basename}.php… |
| [DuckPhp\Core\Console](Core-Console.md) | Console is DuckPHP's command-handling root for the CLI:… |
| [DuckPhp\Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md) | ControllerHelper is the collection of static helpers aimed… |
| [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) | CoreHelper is the facade where the framework collects… |
| [DuckPhp\Db\DbAdvanceTrait](Db-DbAdvanceTrait.md) | DbAdvanceTrait is a set of methods added to a Db… |
| [DuckPhp\Db\Db](Db-Db.md) | Db is DuckPHP's default database connection object; it… |
| [DuckPhp\Db\DbInterface](Db-DbInterface.md) | DbInterface is the contract interface for DuckPHP's… |
| [DuckPhp\Component\DbManager](Component-DbManager.md) | DbManager extends ComponentBase is DuckPHP's single entry… |
| [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) | DuckPhpAllInOne extends DuckPhp is the "the whole… |
| [DuckPhp\DuckPhp](DuckPhp.md) | DuckPhp is a subclass of DuckPhp\Core\App. |
| [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | DuckPhpInstaller is the CLI installer behind bin/duckphp,… |
| [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) | The general exception base class thrown by the system,… |
| [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | EmptyView extends Core\View: it does **not render template… |
| [DuckPhp\Ext\EventManager](Ext-EventManager.md) | EventManager is a simple event manager extension: it… |
| [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | ExceptionManager (class ExceptionManager extends… |
| [DuckPhp\Foundation\Controller\ExceptionReporterTrait](Foundation-Controller-ExceptionReporterTrait.md) | ExceptionReporterTrait is the recommended implementation… |
| [DuckPhp\Ext\ExceptionWrapper](Ext-ExceptionWrapper.md) | ExceptionWrapper is an "exception-safe call wrapper": once… |
| [DuckPhp\Core\ExitException](Core-ExitException.md) | Used to turn places where "the program should end early"… |
| [DuckPhp\Ext\ExtendableStaticCallTrait](Ext-ExtendableStaticCallTrait.md) | ExtendableStaticCallTrait gives a class the ability to… |
| [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | ExtOptionsLoader handles a class of dynamic options that… |
| [DuckPhp\Core\Functions](Core-Functions.md) | src/Core/Functions.php defines a set of global functions… |
| [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | GlobalAdmin is the **complete implementation** of the… |
| [DuckPhp\Component\GlobalEvent](Component-GlobalEvent.md) | GlobalEvent extends ComponentBase provides a small… |
| [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | GlobalUser is the **complete implementation** of the user… |
| [DuckPhp\Foundation\Helper](Foundation-Helper.md) | Foundation\Helper is the "union of the four Helper layers"… |
| [DuckPhp\Ext\HookChain](Ext-HookChain.md) | HookChain represents a "chain of callbacks": on __invoke()… |
| [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | HttpServer is DuckPHP's built-in launcher for "running a… |
| [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) | HttpServerInterface is the contract for DuckPHP's built-in… |
| [DuckPhp\Ext\JsonRpcClientBase](Ext-JsonRpcClientBase.md) | JsonRpcClientBase is the JSON-RPC **client** base class:… |
| [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | JsonRpcExt is the master control of the JSON-RPC… |
| [DuckPhp\Ext\JsonView](Ext-JsonView.md) | JsonView extends Core\View: it changes the rendering… |
| [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | KernelTrait writes "what an application is and how it… |
| [DuckPhp\Component\Lang](Component-Lang.md) | Lang extends ComponentBase provides simple but complete UI… |
| [DuckPhp\Core\Logger](Core-Logger.md) | Logger (PSR-3 by comment, not implements, best-effort… |
| [DuckPhp\Foundation\Model\ModelHelper](Foundation-Model-ModelHelper.md) | Model\ModelHelper is a **thin shell class** for the data… |
| [DuckPhp\Foundation\Model\ModelHelperTrait](Foundation-Model-ModelHelperTrait.md) | ModelHelperTrait is a collection of static helpers aimed… |
| [DuckPhp\Foundation\Model\ModelTrait](Foundation-Model-ModelTrait.md) | ModelTrait is the packaging of the common capabilities of… |
| [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | MyFacadesAutoLoader implements "facade namespace… |
| [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) | MyFacadesBase is the base class for Facade classes: any… |
| [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | MyMiddlewareManager is the middleware-manager extension:… |
| [DuckPhp\Component\Pager](Component-Pager.md) | Pager extends ComponentBase implements PagerInterface… |
| [DuckPhp\Component\PagerInterface](Component-PagerInterface.md) | PagerInterface defines the minimal interface a pager… |
| [DuckPhp\Ext\PermissionMenu](Ext-PermissionMenu.md) | PermissionMenu is the builder of the "back-office… |
| [DuckPhp\Ext\PermissionMenuMetaInterface](Ext-PermissionMenuMetaInterface.md) | PermissionMenuMetaInterface is the contract interface for… |
| [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) | PhaseContainer is DuckPHP's "phase container", and the… |
| [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) | PhaseProxy wraps an object that is "external / needed by a… |
| [DuckPhp\Component\RedisCache](Component-RedisCache.md) | RedisCache extends ComponentBase (comments align with… |
| [DuckPhp\Component\RedisManager](Component-RedisManager.md) | RedisManager extends ComponentBase is the manager DuckPHP… |
| [DuckPhp\Core\Route](Core-Route.md) | Route is DuckPHP's default routing core. |
| [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | RouteHookApiServer is the "API server" route extension: it… |
| [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | RouteHookDirectoryMode implements "directory/file mode"… |
| [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | RouteHookFunctionRoute is the "function-style routing"… |
| [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md) | RouteHookManager is a manager for a route-hook list: it… |
| [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | When enabled (path_info_compact_enable not false at init),… |
| [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | RouteHookResource extends ComponentBase has two sides: 1. |
| [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | RouteHookRewrite extends ComponentBase mainly serves… |
| [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | RouteHookRouteMap extends ComponentBase works through two… |
| [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | RouteHookWebInstaller is the **web installation wizard**:… |
| [DuckPhp\Ext\RouteHookWebInstallerView](Ext-RouteHookWebInstallerView.md) | RouteHookWebInstallerView is the **built-in install view**… |
| [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) | RouteLister extends ComponentBase provides the ability to… |
| [DuckPhp\Core\Runtime](Core-Runtime.md) | Runtime (class Runtime extends ComponentBase) is not a big… |
| [DuckPhp\Foundation\Controller\SessionTrait](Foundation-Controller-SessionTrait.md) | SessionTrait gives the class that composes it **prefixed… |
| [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) | SingletonExTrait is the common source of "singleton-style… |
| [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) | SingletonTrait is the Foundation layer's **thin wrapper**… |
| [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | SqlDumper is the database "schema/data export and… |
| [DuckPhp\Ext\SqlDumperSupporterByMysql](Ext-SqlDumperSupporterByMysql.md) | SqlDumperSupporterByMysql is the **MySQL** driver… |
| [DuckPhp\Ext\SqlDumperSupporterByPgsql](Ext-SqlDumperSupporterByPgsql.md) | SqlDumperSupporterByPgsql is the **PostgreSQL** driver… |
| [DuckPhp\Ext\SqlDumperSupporterBySqlite](Ext-SqlDumperSupporterBySqlite.md) | SqlDumperSupporterBySqlite is the **SQLite** driver… |
| [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) | SqlDumperSupporter is the driver-adapter base class for… |
| [DuckPhp\Ext\StaticReplacer](Ext-StaticReplacer.md) | StaticReplacer can simulate "global variables /… |
| [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | SuperGlobal provides ways to operate on the HTTP… |
| [DuckPhp\Foundation\System\SystemHelper](Foundation-System-SystemHelper.md) | SystemHelper is the collection of static helpers for the… |
| [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) | SystemWrapper puts the common "side-effect" system… |
| [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) | ThrowOnTrait provides a static conditional-throw method. |
| [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) | UserActionInterface is the contract interface for "user… |
| [DuckPhp\Foundation\Controller\UserControllerBase](Foundation-Controller-UserControllerBase.md) | UserControllerBase is the recommended base class for a… |
| [DuckPhp\GlobalUser\UserControllerInterface](GlobalUser-UserControllerInterface.md) | UserControllerInterface is an **empty marker interface**… |
| [DuckPhp\GlobalUser\User](GlobalUser-User.md) | User is the **base class (default implementation)** of the… |
| [DuckPhp\GlobalUser\UserLoginActionInterface](GlobalUser-UserLoginActionInterface.md) | UserLoginActionInterface is the "user login action"… |
| [DuckPhp\GlobalUser\UserLoginServiceInterface](GlobalUser-UserLoginServiceInterface.md) | UserLoginServiceInterface is the "user login service"… |
| [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) | UserServiceInterface is the contract for the "user… |
| [DuckPhp\GlobalUser\UserSessionInterface](GlobalUser-UserSessionInterface.md) | UserSessionInterface is the "user session" contract: it… |
| [DuckPhp\GlobalUser\UserSessionTrait](GlobalUser-UserSessionTrait.md) | UserSessionTrait is the default implementation of… |
| [DuckPhp\Component\Validator](Component-Validator.md) | Validator is DuckPHP's data validation component, using a… |
| [DuckPhp\Core\View](Core-View.md) | View is DuckPHP's default view implementation (class View… |
<!-- GEN:az end -->
