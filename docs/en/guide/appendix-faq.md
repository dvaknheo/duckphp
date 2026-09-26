# Appendix D · FAQ and Troubleshooting Index

> Purpose: when you **arrive with a question**, check here first. The upper half is a FAQ organized by topic (one-line answer + which chapter to read); the lower half is an index by symptom (details live in the [Chapter 4-9 Troubleshooting Manual](../guide/troubleshooting.md)).

## 1. FAQ

### Getting Started and Choosing

| Question | Answer | Where to look |
|---|---|---|
| What kind of project suits DuckPHP? | PHP projects that want "zero dependencies, runnable as a single file, yet able to grow into multiple apps"; not for teams counting on an out-of-the-box ORM/ecosystem | [Chapter 1-1](../guide/intro.md) |
| Is Composer required? | No. The bundled [`AutoLoader`](../reference/Core-AutoLoader.md) finds classes by itself; the root `autoload.php` is the minimal loader | [Chapter 4-4](../guide/embed.md) |
| Can I stuff it into a legacy project and add just one page? | Yes: one entry file + `RunQuickly()`, or the [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) single-file form | [Chapter 4-4](../guide/embed.md) |
| Is there an ORM / migration tool / queue? | None of them. Data access is [`ModelTrait`](../reference/Foundation-Model-ModelTrait.md) + hand-written SQL; table-creation SQL goes into your install flow; wire up a queue yourself | [Chapter 2-7](../guide/database.md) |
| Supported PHP versions? | `>=7.4` (the repo runs tests in both 7.4 and 8.4 containers) | [Chapter 4-7](../guide/coverage.md) |
| How does the mental model differ from Laravel / ThinkPHP? | No PSR-7/15, no injection container, no annotation routes, no ORM or template engine; in exchange you get phase isolation and the override mechanism | [Appendix C](appendix-migration.md) |

### Structure and Rules

| Question | Answer | Where to look |
|---|---|---|
| Do I have to write a Business layer? | The framework does not force it, but **not writing one means giving up** overriding / multi-app / testability; business rules only make sense in one copy | [Chapter 2-1](../guide/layers.md) |
| Can a controller query the database directly? | No (that is a layer violation). Queries go in Model, rules go in Business | [Chapter 2-1](../guide/layers.md) |
| How do I choose between `Helper` and the global functions? | In views use the global functions (`__h`/`__url`/`__l`); in PHP code use the `Helper::` of the corresponding layer | [Chapter 2-9](../guide/helper.md) |
| What is `::_()`? | The mutable-singleton entry: `Xxx::_()` fetches the instance, `Xxx::_($new)` replaces it | [Chapter 4-1](../guide/container-phases.md) |
| Why is the method I want missing from some layer's Helper? | That is a boundary hint: the thing should not be done in that layer | [Chapter 2-9](../guide/helper.md) |

### Routing and Views

| Question | Answer | Where to look |
|---|---|---|
| How does a URL map to a controller? | The last segment is the method name, what comes before is the class path, and the class name gets the `Controller` postfix auto-appended; single-segment paths land on a method of the welcome class | [Chapter 2-3](../guide/routing.md) |
| How do I bind a URL to a specific class@method? | `route_map_important` (priority) or `route_map` (fallback); the callback is written as `Class@method` | [Chapter 2-3](../guide/routing.md) |
| How do I keep old links working? | `assignRewrite('/old', 'new/path')` — **the key must carry a leading `/`** | [Chapter 2-3](../guide/routing.md) |
| Where do view files live, and how are they found? | `view/<view name>.php`; lookup falls back phase by phase (so a parent app's views can be overridden) | [Chapter 2-6](../guide/views.md) |
| How do I add a uniform header/footer? | In the controller constructor: `Helper::setViewHeaderFooter('header', 'footer')` | [Chapter 2-6](../guide/views.md) |
| Is "rendering a view" the only output? | Four kinds: view, `Render()` for a string, `ShowJson()`, and a direct `echo`; plus `Show302`/`Show404` | [Chapter 2-5](../guide/controllers.md) |
| How do I prevent XSS? | Pipe all output through `__h()` (use `__hl()` for translate+escape) | [Chapter 2-6](../guide/views.md) |

### Data

| Question | Answer | Where to look |
|---|---|---|
| How is the database configured? | `database` (a single entry) or `database_list` (multiple / read-write splitting); passwords go through settings | [Chapter 2-7](../guide/database.md) |
| How is the table prefix handled? | Write the `` `'TABLE'` `` macro in the SQL; it is replaced with "prefix + table name" before execution | [Chapter 2-7](../guide/database.md) |
| Why can't `ModelTrait`'s `add()` be called directly? | It is `protected` (deliberate): what you expose to the outside is up to the public methods you open yourself | [Chapter 2-8](../guide/model.md) |
| How do I paginate? | The model returns `[$total, $list]`; the view uses `Helper::PageHtml($total)` | [Chapter 2-7](../guide/database.md) |
| How do I write a transaction? | `beginTransaction/commit/rollBack` on [`Helper::Db()->PDO()`](../reference/Db-Db.md); mind that it is the same connection | [Chapter 2-7](../guide/database.md) |
| How does the cache take effect? | The default [`Cache`](../reference/Component-Cache.md) is a null implementation; install [`RedisCache`](../reference/Component-RedisCache.md) to get real caching (`Helper::Cache()` usage stays the same) | [Chapter 2-14](../guide/cache.md) |

### Cross-Cutting: Hooks, Events, Middleware

| Question | Answer | Where to look |
|---|---|---|
| I want to do something before/after a request — which one? | For interception use route hooks (the pre chain returning a truthy value) or middleware short-circuiting (`return` a response); for before/after decoration use middleware | [Chapter 2-4 Route Hooks](../guide/route-hooks.md) |
| My middleware `return`ed a response — why did it not take effect? | Returning `null`/`false` (or writing no return value) is treated as "not handled" and passes through; to short-circuit, `return 'content'` or `true` | [Chapter 2-4 Route Hooks](../guide/route-hooks.md) |
| To run something before every controller, do I need a base class to extend? | Consider hooks first; inheritance turns a mutable capability into bloodline | [Chapter 2-4 Route Hooks](../guide/route-hooks.md) |
| The difference between events and hooks? | Events are broadcasts (no return value, cross-phase); hooks are a single chain (return values, short-circuiting) | [Chapter 2-13](../guide/events.md) |
| How do I know who attached which hooks, and in what order? | [`RouteHookManager::_()->dump()`](../reference/Ext-RouteHookManager.md) | [Chapter 2-4 Route Hooks](../guide/route-hooks.md) |

### Multi-App and Operations

| Question | Answer | Where to look |
|---|---|---|
| How does one process run multiple apps? | Phases (phase name + container bucketing); child apps are mounted in the `app` option | [Chapter 3-1](../guide/advanced-phase.md) |
| Are components shared between apps? | Some components initialized by the root app are shared (the `#shared` bucket); to get an independent one use `local_*` or `createLocalObject()` | [Chapter 3-4](../guide/component-sharing.md) |
| How do I replace some framework behavior? | Override class / file / singleton / system wrapper — see the "what you want to change → where to touch" decision table | [Chapter 4-3](../guide/replace-behavior.md) |
| How do I deploy to production? | Point the document root at `public/`, rewrite to the entry; turn off `is_debug`; make `runtime/` writable | [Chapter 1-7](../guide/deployment.md), [Chapter 2-18](../guide/security-performance.md) |
| Can the built-in HTTP server go to production? | No, it is only for development / intranet | [Chapter 4-5](../guide/http-server.md) |
| How do I add scheduled tasks? | Write a `command_xxx()` command; in crontab: `cd project-dir && php cli.php xxx` | [Chapter 2-16](../guide/cli.md) |

### Testing and Docs

| Question | Answer | Where to look |
|---|---|---|
| How do I run the tests? | Tests always run in WSL/containers; day to day, run a single file at a time | [Chapter 2-17](../guide/testing.md) |
| Why do tests fail on Windows? | Environment differences such as the missing redis extension in Windows PHP | [Chapter 2-17](../guide/testing.md) |
| Where is the coverage data? | Per-class dumps in `test_coveragedumps/`, the report at `test_reports/index.html` | [Chapter 4-7](../guide/coverage.md) |
| After changing source code, which docs must be updated? | Reference pages (method/option tables) + the related guide chapters + the necessary maintenance records | [Chapter 4-8](../guide/doc-maintenance.md) |
| How do I keep in-site links from breaking? | `python3 docs/scripts/check-doc-links.py docs/zh`; unwritten chapters carry no link | [Chapter 4-8](../guide/doc-maintenance.md) |

## 2. Index by Symptom

The detailed steps are in the [Chapter 4-9 Performance Tuning and Troubleshooting Manual](../guide/troubleshooting.md); here it is only "symptom → where to look first".

| Symptom | Look first at | Most likely cause |
|---|---|---|
| 404 | [Chapter 2-3](../guide/routing.md) §7 | prefix mismatch (E001) / class not found (E003) / the single-segment path rule |
| The route hits the wrong controller | [Chapter 3-5](../guide/overriding.md) | `controller_class_map`, same-named classes, override order |
| White screen | [Chapter 4-9](../guide/troubleshooting.md) §B | view not found / route not matched / include failure |
| A variable is `null` in the view | [Chapter 2-6](../guide/views.md) | not passed into `Show()`; `view_skip_notice_error` masked the notice |
| An override has no effect | [Chapter 3-5](../guide/overriding.md) | used `new`; phase name mismatch |
| Got another app's singleton | [Chapter 3-4](../guide/component-sharing.md) | the component is shared (`#shared`) |
| An option passed in does nothing | [Chapter 1-5](../guide/configuration.md) | not declared in the whitelist / misspelled key / overridden by `data_file_enable` |
| Always redirected to the install page | [Chapter 3-6](../guide/installer.md) | `installed` is `false` |
| Stack traces visible in production | [Chapter 2-18](../guide/security-performance.md) | `is_debug` (child apps included) or `duckphp_is_debug` |
| Cannot connect to the database / table does not exist | [Chapter 2-7](../guide/database.md) | DSN, the table-prefix macro, directory permissions |
| Pagination numbers are wrong | [Chapter 2-7](../guide/database.md) | the count SQL carried a limit |
| The cache has no effect | [Chapter 2-14](../guide/cache.md) | `RedisCache` not installed (the default is a null implementation) |
| Session cannot be read | [Chapter 2-11](../guide/session.md) | `session_prefix` inconsistent; not started |
| Still not logged in after logging in | [Chapter 2-19](../guide/user.md) | wrong `globaluser_login_session` key name / phase (the old names `user_callback_for_*` are defunct) |
| Command not found | [Chapter 2-16](../guide/cli.md) | not registered / method name missing `command_` / missing the command-group prefix |
| An event listener never fires | [Chapter 2-13](../guide/events.md) | [`GlobalEvent`](../reference/Component-GlobalEvent.md) is off by default (`EXT_DISABLE`) |
| Translations do not take effect | [Chapter 2-15](../guide/i18n.md) | the language file name does not match `lang_final` |
| Pages are slow | [Chapter 4-9](../guide/troubleshooting.md) §I | debug switches, OPcache, SQL, cache, unneeded ext |
| Links 404 after subdirectory deployment | [Chapter 2-3](../guide/routing.md) | hand-written absolute paths instead of `__url()` |
| Tests falsely fail on Windows | [Chapter 2-17](../guide/testing.md) | environment differences (redis etc.); switch to WSL |
| `ZAllDemoTest`'s `files` go red | [Chapter 2-17](../guide/testing.md) | the output byte length changes with the source; update the expected value |
| The docs link checker reports broken links | [Chapter 4-8](../guide/doc-maintenance.md) | target not written yet → change to "plain text + `⏳`" and swap in the link once written; path written wrong → just fix it |
| A reference page disagrees with the source | [Chapter 4-8](../guide/doc-maintenance.md) | only the source was changed, not the docs; run the drift scan |

## 3. Still Can't Find It?

1. **Check the reference manual**: `docs/zh/reference/`, one page per class, plus `options.md` (options cheat sheet) and `index.md` (the index);
2. **Check the pitfall tables in the maintenance guides**: `docs/zh/reference-maintenance-guide.md` §7, `docs/zh/guide-maintenance-guide.md` §5;
3. **Confirm whether it is deliberate design**: [Chapter 4-10 Design Trade-Offs and Known Pitfalls](../guide/design-notes.md);
4. **Still stuck**: follow the "is it a bug or by design" flow in [Chapter 4-10](../guide/design-notes.md) §3, and add the conclusion to the maintenance guides' pitfall tables (so the next person does not step on it again).
