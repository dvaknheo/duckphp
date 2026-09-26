# 2-2 The request lifecycle

> What this solves: what the framework does, in what order, from the entry point to the output of one request; **which built-in components it assembles for you**; and which method to override when you want to step in.
> Prerequisites: [Chapter 2-1 The four-layer architecture and calling rules](layers.md). About 20 minutes.
> Division of labour: this chapter covers **the timeline and the built-in components** only. To *intercept a request* see [Chapter 2-4 Route hooks](route-hooks.md); for exceptions see [Chapter 2-12](exception.md), events [Chapter 2-13](events.md), and the CLI branch [Chapter 2-16](cli.md).
> Examples: `demo/src/System/App.php` (real `onPrepare()`/`onInited()` overrides).

## Minimal example

**① Overriding lifecycle methods** — all the wiring lives in `src/System/App.php` (the real file is `demo/src/System/App.php`):

```php
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public function onPrepare(): void
    {
        parent::onPrepare();
        // preparation stage: components are not assembled yet, so you can only change options and mount child apps
        $this->options['app']['AdminApp'] = ['controller_url_prefix' => 'admin/'];
    }
    protected function onInit(): void
    {
        parent::onInit();
        // components are ready: register events and (if needed) route hooks here
    }
    protected function onInited(): void
    {
        parent::onInited();
        // everything is ready: register commands, do the final wiring
    }
}
```

**② Seeing which components exist in this phase** — the first troubleshooting move, faster than reading the source:

```php
\DuckPhp\Core\PhaseContainer::_()->dumpAllObject();   // print the instance list of the current phase container
```

The component tables in §2 below can be checked against its output (that is exactly what `tests/Core/PhaseContainerTest.php` prints).

## How it works

### 1. Startup: the eight steps of `init()`

What `RunQuickly($options, $after_init = null)` does is minimal: `init()` → run the `$after_init` callback you passed → under CLI go to `execute()`, otherwise to `serve()`.

The order inside `init()` (`src/Core/KernelTrait.php`):

| # | Call | What you can do then | Typical use |
|---|---|---|---|
| 1 | `initOptions()` | only options | filling in `namespace`/`path` |
| 2 | `initContainer()` (containing `onAfterCreatePhases()`) | the phase container exists | the rare cases that need to touch the container |
| 3 | `initException()` | exception/error handlers are in place | — |
| 4 | **`onPrepare()`** | components are **not** assembled yet | change options, mount child apps (`app`) |
| 5 | `initComponents()` | components are assembled one after another (see §2) | reading components here is not advised |
| 6 | **`onInit()`** | components are ready | register events/commands/hooks |
| 7 | `initChildren()` | initialise child apps one by one ([Chapter 3-1](advanced-phase.md)) | — |
| 8 | **`onInited()`** | everything is ready, `is_inited = true` | the final wiring window |

Note that in the **root app** `onPrepare()` has one extra job: the framework's [`App::onPrepare()`](../reference/Core-App.md) calls `loadSetting()` to read the settings file, so `Setting()` keys are only available in the root app and only after `onPrepare()` (see [Chapter 1-5](configuration.md)).

### 2. Assembly: which components the framework ships you

Step 5, `initComponents()`, assembles in three layers, and **the assembly scope (which phases share one copy) is the premise for understanding everything that follows**:

**① root layer** — only the root app assembles these, and their class names are registered as "shared classes", so **every phase's `::_()` returns the one instance from the root app**:

| Component                                                                                                            | How it is assembled                                                                               | What it does                                                                      |
| ------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| [`Console`](../reference/Core-Console.md)                                                                     | follows the app options                                                                             | the CLI command root ([Chapter 2-16](cli.md))                                             |
| [`SystemWrapper`](../reference/Core-SystemWrapper.md)                                                         | instantiate only, no `init()`                                                                    | the replaceable system-call wrapper (`header`/`exit`/`setcookie`…, [Chapter 4-3](replace-behavior.md)) |
| [`Logger`](../reference/Core-Logger.md)                                                                       | instantiate only, no `init()`                                                                    | logging                                                                      |
| [`CoreHelper`](../reference/Core-CoreHelper.md)                                                               | instantiate only, no `init()`                                                                    | the implementation behind the `Helper::` static facade                                                     |
| [`DbManager`](../reference/Component-DbManager.md) / [`RedisManager`](../reference/Component-RedisManager.md) | **placeholder only**; `init()`ed only when `database`/`redis` (or `database_list`/`redis_list` in settings) exist | connection management ([Chapter 2-7](database.md), [Chapter 2-14](cache.md))                       |
| [`Admin`](../reference/GlobalAdmin-Admin.md) / [`User`](../reference/GlobalUser-User.md)                      | **placeholder only**                                                                            | the container keys of the admin/user systems ([Chapter 2-19](user.md), [Chapter 2-20](admin.md))                  |
| [`GlobalEvent`](../reference/Component-GlobalEvent.md)                                                        | **placeholder only**                                                                            | the event bus ([Chapter 2-13](events.md))                                             |
| [`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md)                                              | only when `data_file_enable` is true                                                           | the runtime config file (options written to disk)                                                           |

**② inner layer** — **one per phase** (a child app has its own routes and views):

| Component | How it is assembled | What it does |
|---|---|---|
| [`Route`](../reference/Core-Route.md) | follows the app options | routing ([Chapter 2-3](routing.md)) |
| [`View`](../reference/Core-View.md) | follows the app options | view rendering ([Chapter 2-6](views.md)) |
| [`Configer`](../reference/Component-Configer.md) | on by default | config reading (`config/<name>.php`, [Chapter 1-5](configuration.md)) |

**③ ext layer** — whatever the app option `ext` declares. These are **on by default**:

| Component | What it does |
|---|---|
| [`Lang`](../reference/Component-Lang.md) | multi-language ([Chapter 2-15](i18n.md)) |
| [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) / [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) / [`RouteHookResource`](../reference/Component-RouteHookResource.md) / [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) | the four built-in route hooks ([Chapter 2-4](route-hooks.md)); the last one is controlled by `path_info_compact_enable` |

The ways of writing `ext` (`true` / an array / `'@method'` / an option key / the `EXT_*` constants) and the nine meanings are in [Chapter 4-2 Developing components and extensions](custom-component.md).

**④ Two more things that are not "components" but are in place**:

- [`ExceptionManager`](../reference/Core-ExceptionManager.md): in place at step 3, `initException()`, **earlier than the components** ([Chapter 2-12](exception.md));
- [`Runtime`](../reference/Core-Runtime.md): created on demand; the `use_output_buffer` option's output buffering relies on it ([Chapter 2-18](security-performance.md)).

**Reading the tables** (these points decide "why does my replacement not work in a child app"):

- **The difference between root and inner is cross-phase sharing**: `DbManager`, `Admin`/`User` and `GlobalEvent` are root-level, so calling `Admin::_()` inside a child app still returns the root app's copy; whereas `Route::_()` and `View::_()` are **another instance** in a child app.
- **"Placeholder only" = register the class name but do not create it here**: the assembly value `EXT_ROOT_HOLD_POSISION_ONLY` (whose value is `EXT_DISABLE`) only does the "register as a shared class" step, leaving the actual creation to the first `::_()`, so an app with no database configured does not build a pointless `DbManager`.
- **"Instantiate only, no `init()`" = `EXT_SKIP_INIT`**: `SystemWrapper`/`CoreHelper` are pure tools with no options to read. `Logger` is **not** in that column — it needs `path_log`/`log_file_template`/`log_prefix`, so it is initialised normally with `EXT_DEFAULT` during root init (it once used `EXT_SKIP_INIT` by mistake, and every logging setting in the app options was silently ignored).
- A component switched off by `ext` (e.g. `GlobalEvent`, which is not in the table by default) is **simply not assembled**; `GlobalEvent::_()` still works, it just is not pre-initialised by the framework ([Chapter 2-13](events.md)).

### 3. The request: the full `serve()` timeline

```
serve()
 ├─ prepareServe()            switch back to your own phase + rebuild EXT_RENEW components
 ├─ onRequest()               * runs once per request (a child app asked by its parent counts too)
 ├─ Runtime::_()->run()       optional: enable output buffering (option use_output_buffer)
 ├─ Route::_()->run()         the three routing stages (below)
 │    ├─ pre_run_hook_list     run in order; **as soon as one returns truthy, routing ends immediately**
 │    ├─ the default route callback  <- the controller method runs here (when enable_default_callback is true)
 │    └─ post_run_hook_list    the fallback after the default route misses (404 view, resources…)
 ├─ runChildren()             the parent app missed -> ask each child app in turn (Volume 3)
 ├─ phaseToCurrent()          return to your own phase
 ├─ no match -> _On404()      404 handling (replaceable by the error_404 option, see Chapter 2-12)
 ├─ an exception -> runException()  handed to the exception manager (Chapter 2-12)
 └─ finally                   phaseToCurrent() + Route::_()->clear() + Runtime::_()->clear()
                              ^ Route::clear() runs finally_run_hook_list
```

Three easily missed points:

- **`onRequest()` is "once per app", not "once per process"**: when the parent app misses it hands the request to a child app, and that child app's `serve()` runs its own `onRequest()` again.
- **[`Route::clear()`](../reference/Core-Route.md) is inside `finally`**, so the `finally-inner`/`finally-outter` hooks always run (including on the exception path) — good for wind-down, cleanup and reporting.
- **`run()` is the fork between `serve()` and `execute()`**: when `cli_enable` is true and we are on the CLI it goes to [`Console::_()->run()`](../reference/Core-Console.md) ([Chapter 2-16](cli.md)), and on the web to `serve()`. Use `App::_()->isCli()` to tell the current form, never guess from `PHP_SAPI`.

> The three hook lists in this timeline are the subject of the next chapter: [Chapter 2-4 Route hooks](route-hooks.md).

### 4. Output: `onBeforeOutput()`

Both `App::_Show()` and the 404/500 error view paths call `onBeforeOutput()` first and only then hand over to [`View`](../reference/Core-View.md) for rendering. It is **the last hook before output**, good for injecting variables uniformly, adding instrumentation, or a final response-header change. It **gets called several times** (once for normal output; once for each error view path), so do not put "must run exactly once" logic in it.

## Common patterns

**① Register every piece of wiring in `onInited()`** (commands, events, hooks) — the components are ready by then, which is safer than `onPrepare()`.

**② Use `onRequest()` for request-level initialisation**, remembering that it runs once per app:

```php
protected function onRequest(): void
{
    parent::onRequest();
    if ($this->isCli()) { return; }   // no web-only logic under CLI
    // e.g. switch settings by domain, initialise a tenant context
}
```

**③ Inject uniformly in `onBeforeOutput()`** (variables every view can see, a final header change):

```php
public function onBeforeOutput()          // note: it is public; do not narrow it to protected when overriding
{
    parent::onBeforeOutput();
    // the app class sits in the System layer, which has no assignViewData(); use the View component directly
    \DuckPhp\Core\View::_()->assignViewData('site_name', static::Setting('site_name', 'DuckPHP'));
}
```

**④ When troubleshooting, ask three things first**: `PhaseContainer::_()->dumpAllObject()` (what is in this phase), `App::_()->isCli()` (which branch am I on), `App::_()->isRoot()` (am I the root app).

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| Overriding `onBeforeRun()`/`onAfterRun()` has no effect at all | Those two methods **do not exist** (the framework has no such hooks) | What really exists is `onAfterCreatePhases()`, `onPrepare()`, `onInit()`, `onInited()`, `onRequest()` (all `protected`) and `onBeforeOutput()` (**`public`**); visibility may only be widened, never narrowed |
| Reading a component inside `onPrepare()` errors or comes back empty | Components are not assembled yet | Put option changes in `onPrepare()` and component reads in `onInit()`/`onInited()` |
| The logic in `onBeforeOutput()` ran twice | The error view path calls it too | Use a flag, or move the "run once" logic into `onRequest()` |
| Inside a child app, `Admin::_()`/`DbManager::_()` return the same copy as the root app, and changes affect both | These are **root-level shared classes** (shared across phases) | For one copy per phase use an inner-level component, or `new` it yourself ([Chapter 4-1](container-phases.md)) |
| You assume `GlobalEvent::_()`/`Admin::_()` are already initialised | In the root table they are only **placeholders** | Assemble them explicitly (declare in `ext`) before use, or do not rely on them being init'ed |
| No database is configured, yet you want to know where `DbManager` is | Without configuration it is not init'ed (only the class name is registered) | `::_()` still works; configure `database`/`database_list` for a real connection |
| You detect the runtime form with `PHP_SAPI === 'cli'` and get it wrong inside a child app | The form should be decided by the app uniformly | Use `App::_()->isCli()` |
| With `use_output_buffer` enabled, `header()` reports "output already sent" | Output buffering changes the response timing | Call `header()` before anything is output, or turn the buffer off ([Chapter 2-18](security-performance.md)) |

## Next steps

- [Chapter 2-4 Route hooks](route-hooks.md): using the three hook lists from this timeline, their order, and how to intercept a request.
- [Chapter 2-12 Exceptions and error handling](exception.md): the full flow after `_On404()`/`runException()`.
- [Chapter 2-13 The event system](events.md): the broadcast-style intervention point.
- [Chapter 2-16 The command line and scheduled tasks](cli.md): the `execute()` branch.
- [Chapter 3-1 The application tree and phase basics](advanced-phase.md): the multi-app machinery behind `runChildren()`.
- Reference manual: [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md), [DuckPhp\Core\App](../reference/Core-App.md), [DuckPhp\Core\PhaseContainer](../reference/Core-PhaseContainer.md).
