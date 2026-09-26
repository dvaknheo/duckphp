# 4-9 Performance Tuning and Troubleshooting Handbook

> What this solves: **symptom → troubleshooting path**. The left side is your symptom; the right side is "look here first, then there, and the likely cause".
> Prerequisites: [Chapter 2-2 The Request Lifecycle](lifecycle.md) (the sequence diagram is the map for troubleshooting), [Chapter 2-18 The Security and Performance Checklist](security-performance.md) (the performance switches).
> Usage: find your symptom below, then follow "what to look at" item by item; every item lands on a concrete method or command.

## Minimal example

Three commands solve most "why doesn't it run the way I think":

```php
Route::_()->getRouteError();          // why the route did not hit (E001/E003/E008/E009...)
Route::_()->PathInfo();               // what path the framework actually received
\DuckPhp\Core\PhaseContainer::Dump(); // which singletons are in the current container (a must-see for override/sharing problems)
```

Plus a hook inventory:

```php
echo \DuckPhp\Ext\RouteHookManager::_()->dump();   // who is actually attached to the three hook chains, and in what order
```

## How it works

Troubleshooting has only two principles: **get evidence first, change one variable per step**. The framework's execution path is deterministic (the sequence diagram in [Chapter 2-2](lifecycle.md) is the map):

```
init()（onPrepare → onInit → onInited） → serve()（onRequest → Route::run() → runChildren() → _On404）  # init() (onPrepare → onInit → onInited) → serve() (onRequest → Route::run() → runChildren() → _On404)
                                                    ↑ 三段链：pre 钩子 → 默认路由 → post 钩子（finally 在 clear()）  # the three-segment chain: pre hooks → default route → post hooks (finally in clear())
```

So a problem always lands in one of four places: **the option did not take effect**, **the route did not hit**, **the instance/file you got is not the one you think**, **the output timing is wrong**. The toolbox below is built to verify these four classes separately.

### The toolbox

| Tool | What it can answer |
|---|---|
| [`Route::_()->getRouteError()`](../reference/Core-Route.md) | The reason code of a routing failure |
| `Route::_()->PathInfo()` | The request path the framework "thinks" it got |
| `Helper::getRouteCallingClass()` / `getRouteCallingMethod()` | Which class/method was actually hit |
| [`RouteHookManager::_()->dump()`](../reference/Ext-RouteHookManager.md) | The members and order of the hook chains |
| [`PhaseContainer::Dump()`](../reference/Core-PhaseContainer.md) / `dumpAllObject()` | Which singletons are in the container, and which phase they belong to |
| [`App::_()->options`](../reference/Core-App.md) | The effective options (compare against what the whitelist blocked) |
| `Helper::Setting()` | What the settings file actually loaded |
| `__debug_log()` / `__var_log()` / `__trace_dump()` | Write the scene into the log ([Chapter 1-6](debugging.md)) |
| `database_log_sql_query = true` | Which SQL was actually executed |
| `__is_debug()` / `__is_real_debug()` | Why the debug flag is on |

## Symptoms → Troubleshooting Paths

### A. Routing and requests

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| 404, the page says `404 File Not Found` | `Route::_()->getRouteError()` | Class does not exist (E003) → check the namespace and the `Controller` suffix; prefix mismatch (E001) → the child app's `controller_url_prefix` ([Chapter 3-2](mount-app.md)) |
| `/about` reports class not found | The single-segment path rule | A single segment = "a method of the welcome class", not a controller; a controller needs at least two segments, or use `route_map` ([Chapter 2-3](routing.md)) |
| `/Main/index` is rejected | Error code E009 | `controller_welcome_class_visible` defaults to `false`; set it to `true` if you really need it |
| The wrong controller was hit | `getRouteCallingClass()` | Same-named classes in two namespaces; or a mapping in `controller_class_map` |
| `assignRewrite()` has no effect | Does the key carry a leading `/` | It must be written as `'/legacy'` ([Chapter 2-3](routing.md)) |
| `Class::method` in `route_map` does not work | The callback spelling | Only `Class@method` / `Class->method` / callable are supported |
| The child app never receives the request | The entry URL | Missing `controller_url_prefix`, or the parent app grabs it first ([Chapter 3-2](mount-app.md)) |
| The controller runs twice per request | What the middleware returns when short-circuiting | [`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md): a short-circuit must return a response or `true`; returning `null`/`false` is treated as "not handled", and `Route::run()` then runs the default callback again ([Chapter 2-4](route-hooks.md)) |

### B. Output and views

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| White screen, no output | Logs + `Route::_()->getRouteError()` | The route did not hit (the 404 branch ran); or the view include failed |
| View not found | The view name vs `path_view` | A view name is a path relative to `view/`; omitting the view name at the root path looks for `Main/index` ([Chapter 2-6](views.md)) |
| Variables in the view are all `null` without any error | `view_skip_notice_error` defaults to true | The variables were not passed into `Show()`; turn the option off temporarily while debugging |
| `Helper::Show('main', $data)` white-screens | The parameter order | The real signature is `Show($data = [], $view = '')` |
| Header/footer do not appear | Did you `setViewHeaderFooter()` in the constructor | The header/footer view names are likewise relative to `path_view`; `Render()` does **not** include them |
| `header()` reports "output already sent" | Output timing | Send headers before output; or enable `use_output_buffer` (mind the semantic change) |
| The page shows `Maintaining.` / `Internal Error` placeholders | `is_maintain` / unset `error_*` | Set the `error_maintain`/`error_500` views ([Chapter 2-12](exception.md)) |
| Stack traces visible in production | `is_debug` (including child apps) and the setting | `App::_IsDebug()` is an **OR** of "setting or root or this app"; wrap debug blocks in `__is_debug()` in error views |

### C. Overrides, multiple apps, and the container ("who wins" problems)

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| An overriding class has no effect | `PhaseContainer::Dump()` | The call site used `new` instead of `::_()`; the override mechanism relies on the container ([Chapter 3-5](overriding.md)) |
| An overriding view has no effect | The view lookup order | Override **falls back by phase**; make sure the parent app's directory name matches the child app's phase name ([Chapter 3-5](overriding.md)) |
| A child app got the parent app's instance | The `#shared` bucket of `dumpAllObject()` | The component is shared (marked shared when Root initializes); for independence use `createLocalObject()` or `local_database`/`local_redis` ([Chapter 3-4](component-sharing.md)) |
| Changed a shared component and the other app changed too | Same as above | Expected behavior; either accept it or localize per app |
| Added an extension to `ext` but nothing happens | The `EXT_*` value | `EXT_DISABLE` means off; `EXT_RENEW` rebuilds per request ([Chapter 4-1](container-phases.md)) |
| A child app outputs 404 instead of handing over to the parent | `skip_404` | Call `App::_()->skip404Handler()` in the child app ([Chapter 2-2](lifecycle.md)) |

### D. Configuration and settings

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| An option was passed but has no effect | The `$options` declared by that component | Component initialization filters with `array_intersect_key`: **undeclared keys are dropped** ([Chapter 1-5](configuration.md), [Chapter 4-2](custom-component.md)) |
| `Helper::Setting()` cannot get a value | The settings file name and load timing | The file is `config/DuckPhpSettings.config.php`; settings load only in the root app's `onPrepare()` ([Chapter 1-5](configuration.md)) |
| Still the old config after a change | Is persistence enabled | `data_file_enable` writes ext options into JSON; clear it or change the source |
| Front/back office 302s to the install page | `installed` | The constructors of [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)/[`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) check it; set it to `true` ([Chapter 3-6](installer.md)) |
| A child app's settings are overridden by the parent's | `Setting()` reads the **root app** | A child app that wants its own value should use options, or not use Setting |

### E. Database and cache

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| Cannot connect / DSN error | `Helper::DatabaseDriver()` + [`Helper::Db()->PDO()`](../reference/Db-Db.md) | The DSN is misassembled; `database_list` and `database` are mixed up ([Chapter 2-7](database.md)) |
| `no such table` | `table_prefix` and the table names in the SQL | Use the `` `'TABLE'` `` macro; do not hand-write table names ([Chapter 2-7](database.md)) |
| `execute()` returns 0 | The semantics | Success = affected rows, failure = 0; an `UPDATE` with no change is also 0 |
| The read side cannot see just-written data | Read/write splitting | When you need strong consistency, explicitly use the write connection ([Chapter 2-7](database.md)) |
| [`Helper::Cache()->set()`](../reference/Component-Cache.md) returns false | Is [`RedisCache`](../reference/Component-RedisCache.md) installed | Without it, `Cache` is a null implementation (it never caches, but never errors either) ([Chapter 2-14](cache.md)) |
| Two apps' caches overwrite each other | `redis_cache_prefix` | Give each app a different prefix |
| The pagination total is wrong | The counting SQL | Use `Helper::SqlForCountSimply()` |

### F. CLI and scheduled tasks

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| [`(xxx)Command Not Found In All`](../reference/Component-Command.md) | Is the command registered | `regConsoleCommand()`; the method name carries the `command_` prefix ([Chapter 2-16](cli.md)) |
| The command works locally but not from crontab | The working directory and the php path | `cd /srv/myproj && /usr/bin/php cli.php …` |
| `--key=value` cannot be read | The parameter structure | Keys do not carry `--`; positional parameters live in `['--']` ([Chapter 2-16](cli.md)) |
| A child app's command cannot be reached | The command-group prefix | Use `child-app-name:command` |

### G. Sessions, users, and security

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| Session written but unreadable | `session_prefix` and the start timing | A different prefix = a different key; [`SessionTrait`](../reference/Foundation-Controller-SessionTrait.md) calls `session_start()` only on first read/write ([Chapter 2-11](session.md)) |
| Still shown as logged out after login | The callback configuration of [`GlobalUser`](../reference/GlobalUser-GlobalUser.md) | The three required keys such as `globaluser_login_session` must be right (the old names `user_callback_for_session` / `user_callback_get_*` are all dead) ([Chapter 4-11](impl-user.md)) |
| The admin menu is empty | The controller's doc comments | Annotations like `@menu_directory` are missing; [`PermissionMenu`](../reference/Ext-PermissionMenu.md) only accepts navigable nodes (see its reference page) |
| Unescaped user data in the output | `<?=` in views | Always use `__h()` |
| Someone can see the back office after go-live | The authorization check | The permission system only answers "is an admin"; **resource ownership must be checked by yourself in Business** ([Chapter 2-18](security-performance.md)) |

### H. i18n and events

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| The original keys keep showing | `lang_final` and the language file name | After normalization it must look like `zh_CN`; put the file at `config/lang-zh_CN.php` (the old placement `config/lang/zh_CN.php` needs `'lang_file_path' => 'lang/'`) ([Chapter 2-15](i18n.md)) |
| `?lang=xx` has no effect | `lang_final` is already computed | Changing `$_GET` at runtime does nothing; do it in the `onPrepare` phase or set `lang_final` directly |
| Event listeners never fire | Is [`GlobalEvent`](../reference/Component-GlobalEvent.md) enabled | It defaults to `EXT_DISABLE`; turn it on in `ext` ([Chapter 2-13](events.md)) |
| `::_()` in an event callback gets the wrong instance | Phase binding | `fire()` switches to the phase at **registration time** ([Chapter 2-13](events.md)) |

### I. Performance (check in this order; it usually stops at step 1)

| # | Action | Expected gain |
|---|---|---|
| 1 | Confirm `is_debug = false` and OPcache is on | The most common and the most rewarding |
| 2 | Enable `database_log_sql_query` and look for repeated/full-table queries | Find N+1 and missing indexes |
| 3 | Cache hot data (`Helper::Cache()`) with a TTL | Directly reduces database pressure ([Chapter 2-14](cache.md)) |
| 4 | Turn off capabilities you do not use: `path_info_compact_enable`, `data_file_enable`, superfluous `ext` | Cuts the fixed cost of every request |
| 5 | Order the route-map rules: frequent first; prefer exact matches over regexes | Fewer match attempts |
| 6 | Check log volume and rotation (the exception log is on by default) | Avoid disk and IO slowdowns |
| 7 | Only then consider "switching to a resident runtime" (the framework's built-in [`HttpServer`](../reference/HttpServer-HttpServer.md) suits development/intranet only) | —— |

### J. Tests and the toolchain

| Symptom | What to look at | Likely cause / fix |
|---|---|---|
| Redis test cases fail falsely on Windows | The extension is missing | Always use WSL ([Chapter 2-17](testing.md)) |
| `ZAllDemoTest`'s `files` goes red | Output byte lengths | Source changes alter the method table/option table/line numbers — update the expectations to the actual values ([Chapter 2-17](testing.md)) |
| Coverage stays at 0 | `XDEBUG_MODE=coverage` | See [Chapter 4-7](coverage.md) |
| `gen-reference.php verify` reports "extra methods" | A known script defect | Judge by the drift scan ([Chapter 4-8](doc-maintenance.md)) |

## Common patterns

**① Three lines of evidence (do this before touching any code)**

```php
Route::_()->getRouteError();                  // routing-layer facts
\DuckPhp\Core\PhaseContainer::Dump();         // container/phase-layer facts
var_dump(array_key_exists('我的选项', App::_()->options));   // option-layer facts ('我的选项' = "my option")
```

**② Bisect to find "which segment broke"**

- Can the simplest path (e.g. `/`) render a page? Yes ⇒ the problem is in your routing/controller; no ⇒ the problem is in initialization or the environment;
- Can a minimal entry point (like `demo/public/helloworld.php`) run? Yes ⇒ the framework is fine; it is your project configuration;
- Does `php cli.php routes` list the route you expect ⇒ if not, it is a configuration/registration problem.

**③ Use logs instead of breakpoints**

```php
__debug_log('path=%s opts=%s', $path, json_encode(App::_()->options));
// output goes to the log under runtime/ (Chapter 1-6)
```

**④ Find out "who changed it"**

- Hook order: `RouteHookManager::_()->dump()`;
- A file being overridden: `App::_()->getOverrideableFile('view', 'main.php')` prints the file actually hit ([Chapter 3-5](overriding.md));
- An instance being replaced: compare class names in `PhaseContainer::Dump()` ([Chapter 4-1](container-phases.md)).

**⑤ Turn the conclusion into a test**

Reproduce → write it into `tests/` (temporarily revert the bug to confirm the test goes red) → then fix ([Chapter 2-17](testing.md)). This is a hard rule of this repository: a "fixed" without a test is not fixed.

## Common errors (in the troubleshooting method itself)

| Symptom | Cause | Fix |
|---|---|---|
| Diving into the source right away | No evidence taken first | Print the "facts" with the three calls in the toolbox first |
| Changed the place in `src/` that "looked right" | Did not confirm whether it is deliberate | Check [Chapter 4-10](design-notes.md) and the pitfall tables of the two maintenance guides first — many behaviors are deliberate designs |
| Troubleshooting redis/test problems on Windows | Wrong environment | Reproduce under WSL before judging |
| Reading only the business logs | The framework's warnings/notices are ignored | Turn on `is_debug` and raise the error level while developing ([Chapter 1-6](debugging.md)) |
| Restarting the service over and over without reading the logs | Guess-style troubleshooting | Verify item by item per this table, one variable per step |

## Next steps

- [Chapter 4-10 Design Trade-offs and Known Pitfalls](design-notes.md): which behaviors are deliberate and which are real pitfalls.
- [Chapter 2-18 The Security and Performance Checklist](security-performance.md): the pre-go-live checklist.
- [Chapter 4-7 Test Infrastructure and the Coverage Pipeline](coverage.md): turn troubleshooting conclusions into tests.
