# Appendix A · Glossary

> This is **the wording baseline for the whole book**. A concept uses only the name fixed here; on first appearance the body text gives the English term and the corresponding code entity, and does not repeat the explanation afterwards.
> The chapter where a term is expanded is noted in parentheses after it.

## Apps and Phases

| Term             | English / code entity                                                                                         | Meaning                                                                                                   |
| -------------- | ------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| **app** (应用)         | [App](../reference/Core-App.md) / an instance of a subclass of `DuckPhp\Core\App`                                        | An initializable runtime unit: it has its own namespace, project path, options and container. Always get the instance via `App::_()`, never `new`.                                        |
| **entry class** (入口类)        | Entry / [`DuckPhp`](../reference/DuckPhp.md), [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) | The application class that actually gets `RunQuickly()` called on it in a project, usually placed at `src/System/App.php`. (Chapter 1-2)                                     |
| **root app** (根应用)        | Root App / `App::Root()`                                                                          | The topmost app of the application tree, whose phase name is the empty string `''`. `App::Root(true)` means "get the root instance and switch the current phase back to root".                                         |
| **child app** (子应用)        | Child App / the `app` option                                                                              | An app mounted under its parent via `'app' => [ChildAppClass => [...]]`; it has an independent phase and container, and may carry its own route prefix, views and configuration. (Chapter 3-1)                          |
| **phase** (相位)         | Phase / `App::Phase()`                                                                            | The "instance space": singletons are bucketed by phase within one process, so the same class is a different instance under different phases. `::_()` always returns the one in the **current phase**. The root phase is `''`; child phases are named like `parent:child`. (Chapters 3-1, 4-1)   |
| **phase switching** (相位切换)       | `App::Phase($name)` / `toThisChild()` / `FromCurrentParent()` / `SwitchRootPhase()`               | The operations for entering an app / returning to the parent / returning to root. They **change the current phase**; called without arguments, `Phase()` only reads. (Chapter 3-1)                                         |
| **instance container** (实例容器)       | [PhaseContainer](../reference/Core-PhaseContainer.md)                                             | The container that holds all singletons: bucketed by phase, plus a `#shared` shared bucket. (Chapter 4-1)                                                           |
| **shared container / shared class** (共享容器 / 共享类) | `shared_classes` / the `#shared` bucket / `EXT_FOLLOW_APP`                                                          | Instances marked as shared are **one single instance across all phases** (e.g. the root app, [Console](../reference/Core-Console.md), the router); this is the foundation of "component sharing". (Chapter 3-4) |
| **local object** (局部对象)       | `createLocalObject()`                                                                             | Forces a fresh instance in the current phase (not shared), for the scenario where each child app should use its own copy of a component. (Chapter 3-4)                                                        |

## URLs and Resources

| Term               | English / code entity                                                                                                    | Meaning                                                    |
| ---------------- | ------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------- |
| **mount prefix** (挂载前缀)         | `controller_url_prefix`                                                                                      | The prefix of all controller URLs of an app; child apps also use it to be "mounted under some path". (Chapter 3-2)         |
| **document root** (文档根)          | `path_document`                                                                                              | The directory exposed by the web server (e.g. `public/`). It decides which URL paths the web server serves directly as files. |
| **resource directory** (资源目录)         | `path_resource` / [`RouteHookResource`](../reference/Component-RouteHookResource.md)                         | In deployments without rewrite, the directory from which the framework serves static resources itself. (Chapter 3-3)              |
| **rewrite** (重写)           | Rewrite / [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md)                                   | Maps one URL to another route (without changing the address the user sees). (Chapter 2-3)                |
| **route map** (路由映射)         | [Route](../reference/Core-Route.md) Map / [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) | Binds a URL directly to a `Class@method`; can be marked as an "important route" to match first. (Chapter 2-3)              |
| **PATH_INFO compat** (PATH_INFO 兼容) | [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md)                               | Passes the route through the query string on servers without PATH_INFO. (Chapter 2-3)                   |

## Layering and Naming

| Term         | English / code entity                                                         | Meaning                                                   |
| ---------- | ----------------------------------------------------------------- | ---------------------------------------------------- |
| **the four layers** (四层)     | Controller / Business / Model / [View](../reference/Core-View.md) | One-way calling: controllers take input and produce output, the business layer holds logic (stateless), models only do data access, views only do display. (Chapter 2-1)  |
| **system layer** (系统层)    | System                                                            | Holds application configuration and framework-related wiring, namespace `src/System`. (Chapter 1-3)            |
| **action class** (动作类)    | Action                                                            | A reusable stateless class in the controller layer, shared by multiple controllers. (Chapter 2-5)                     |
| **service class** (服务类)    | Service                                                           | A reusable class in the business layer, shared by multiple Business classes. (Chapter 2-1)                  |
| **Helper** (Helper) | `DuckPhp\Foundation\<Layer>\<Layer>Helper` / the project's `Helper`                 | Layered helpers: convenient entry points such as `Helper::Show()`, replacing `use`-ing framework classes everywhere. (Chapter 2-9) |

| **controller postfix / method prefix** (控制器后缀 / 方法前缀) | `controller_class_postfix` / `controller_method_prefix` | Decide the conversion between URLs and class/method names; the method prefix is empty by default. (Chapters 2-3, 2-5) |
| **welcome page** (欢迎页) | `controller_welcome_class` / `_method` | Defaults to `Main::index`: both the root path and single-segment paths land on it first. (Chapter 2-3) |

## Components and Extensions

| Term       | English / code entity                                          | Meaning                                                                                                                                                                |
| -------- | -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **component** (组件)   | Component / `DuckPhp\Component\*`                  | Capability units shipped with the framework ([DbManager](../reference/Component-DbManager.md), [Cache](../reference/Component-Cache.md), [Lang](../reference/Component-Lang.md)…), loaded when the app initializes. (Chapter 4-2) |
| **extension** (扩展)   | Ext / `DuckPhp\Ext\*`                              | Optional capabilities attached on demand (JsonRpc, middleware, the permission menu…), declared via the `ext` option. (Chapter 4-2)                                                                                                             |
| **load mode** (装载模式) | the `EXT_*` constants                                         | `EXT_DEFAULT`/`EXT_FOLLOW_APP`/`EXT_SKIP_INIT`/`EXT_RENEW`/`EXT_DISABLE`, deciding how an extension is initialized and whether it is shared. (Chapters 4-2, 2-2)                                                              |
| **override** (覆盖)   | Override                                           | The umbrella term for rewriting existing behavior at the five levels: file, class, route, view, component. (Chapter 3-5)                                                                                                                             |
| **override class** (覆盖类)  | `override_class` / `override_from`                 | Lets another class take over the current app's initialization, typically to "swap out a third party's App class without touching its code". (Chapters 3-5, 4-3)                                                                                                               |
| **phase proxy** (相位代理) | [PhaseProxy](../reference/Component-PhaseProxy.md) | Pins calls onto a given phase through a proxy object, implementing "cross-app calls". (Chapter 3-4)                                                                                                                          |

## Configuration and Data

| Term        | English / code entity                                                               | Meaning                                                                                       |
| --------- | ----------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| **options** (选项)    | options / `$options`                                                    | The configuration array passed to an app or component, with a whitelist and defaults; readable via `App::_()->options`. (Chapter 1-5)                                   |
| **settings** (设置)    | setting / `Setting()`                                                   | Key values from configuration files (`DuckPhpSettings.config.php`, `.env`), typically passwords and environment-related items. (Chapter 1-5)                     |
| **overrideable file** (可覆盖文件) | `getOverrideableFile()`                                                 | File lookup that falls back phase by phase: a child app can "override" a same-named view/config of its parent in its own directory. (Chapter 3-5)                                         |
| **runtime directory** (运行时目录) | `path_runtime`                                                          | The writable directory for logs, cache etc.                                                                              |
| **config directory** (配置目录)  | `path_config`                                                           | `config/`, where [`Configer`](../reference/Component-Configer.md) and the installer read files from.                   |
| **event** (事件)    | Event / [`GlobalEvent`](../reference/Component-GlobalEvent.md)          | Named events broadcast across apps; event names use "in progress / done" suffixes such as `registering`/`registered`. (Chapter 2-13)                      |
| **conditional throw** (条件抛)   | `ThrowOn()` / `*ThrowOn()`                                              | The guard-style pattern "throw if the condition holds", from [`ThrowOnTrait`](../reference/Ext-ThrowOnTrait.md) or a Helper. (Chapter 2-12) |
| **system exception** (系统异常)  | [`DuckPhpSystemException`](../reference/Core-DuckPhpSystemException.md) | Means **only** "the framework itself has a problem"; your project's business/permission exceptions should extend `\Exception` directly. (Chapter 2-12)                                 |

## Writing Conventions

- File paths are written as **repo-relative paths**; classes are written as **fully qualified names** (first occurrence) or **short names** (later in the same chapter).
- The placeholder for "your project namespace" in sample code is uniformly written as `MyProj`; sample project names: volume 3 uniformly uses `ZThirdDemo` (a sample project that really exists), volumes 1/2 point at the ready-made `skeleton/` (scaffold skeleton) and `demo/` (multi-entry sample app) in the repo.
- Do not mix "app" and "child app" with loose namings like "App instance" or "sub-App"; never write "phase" as "stage".
