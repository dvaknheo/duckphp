# 2-18 Security and Performance Checklist

> What this solves: an item-by-item self-review before going live — **security** (don't leak what shouldn't leak, escape what should be escaped, do yourself what only you can do) and **performance** (which switches pay off, which switches cost).
> Prerequisites: [Chapter 1-7 The Minimal Go-Live Checklist](deployment.md) (deployment / document root / permissions — not repeated in this chapter), [Chapter 2-12 Exceptions and Error Handling](exception.md).
> How to use: before launch, go through both checklists and tick each item; every item gives its "evidence" — fix what you can, and items marked "project-side" are things the framework does not provide and you must do yourself.

## Minimal example

A set of production options you can copy as-is (all are real option names; the evidence for each is given item by item in the next section):

```php
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $options = [
        // ── turn off every debug switch ──
        'is_debug' => false,
        'is_maintain' => false,

        // ── take over the error pages yourself (don't let the framework print placeholder text) ──
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'error_maintain' => '_sys/error_maintain',

        // ── turn off the capabilities you don't need ──
        'path_info_compact_enable' => false,   // not needed when you have proper rewrite
        'data_file_enable' => false,           // don't enable it if you don't persist ext options
        'use_env_file' => true,                // sensitive values go via .env / the settings file, not into the repo

        // ── Session ──
        'session_prefix' => 'myproj_',
    ];
}
```

## How it works

### 1. What the framework already does for you

| Item          | How the framework does it                                                                                                 | Evidence                                                           |
| ----------- | ------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------ |
| HTML escaping     | `__h()` / `__hl()` ([`CoreHelper::H()`](../reference/Core-CoreHelper.md))                               | `src/Core/Functions.php`                                     |
| SQL injection      | parameter placeholders + [`Db::quote()`](../reference/Db-Db.md) / `quoteScheme()`; the model layer never exposes `execute()`                  | [Chapter 2-7](database.md), [Chapter 2-8](model.md)                   |
| Replaceable system calls     | [`SystemWrapper`](../reference/Core-SystemWrapper.md) (`header`/`setcookie`/`exit`/`session_start` all go through it) | `Helper::system_wrapper_replace()`                           |
| Session key isolation | the `session_prefix` prefix; multiple apps in one process don't cross keys                                                                          | `Foundation/SessionTrait.php`                                |
| No error leakage     | with `is_debug` false, the error page prints only placeholder text — no stack/paths                                                                       | [`App::_OnDefaultException()`](../reference/Core-App.md)     |
| No 404 leakage     | prints the `404 File Not Found` placeholder by default; debug info is only appended under `is_debug`                                                     | `App::_On404()`                                              |
| Maintenance mode        | `is_maintain` or the setting `duckphp_is_maintain` → renders `error_maintain`                                         | `App::initComponents()`                                      |
| Install gate        | when `installed` is false, the front/admin controller constructors 302 to `url_install`                                                        | `App::checkInstallToPage()`                                  |
| Hidden welcome-class path     | with `controller_welcome_class_visible = false`, `/Main/xxx` is rejected outright (E009)                                    | [`Route::adjustClassBaseName()`](../reference/Core-Route.md) |

### 2. What the framework does not provide — do it yourself

| Item                  | Why the framework doesn't handle it                   | What to do                                                                              |
| ------------------- | ------------------------- | -------------------------------------------------------------------------------- |
| **CSRF protection**         | the framework has no form-token mechanism                | issue your own token (store in Session + hidden form field + verify); at minimum add a SameSite cookie to write operations                            |
| **Upload validation**          | it only provides `Helper::FILES()` to read the data | validate MIME/extension/size, store under a renamed file, **never put uploads in an executable directory** ([Chapter 3-3](static-resources.md))                |
| **Authorization (horizontal/vertical)**       | the permission system only answers "is logged in / is admin"     | every business operation must check "does this resource belong to them" — put it in the Business layer                                                |
| **Rate limiting / anti-abuse**           | not built in                       | Redis counters ([Chapter 2-14](cache.md)) or do it at the gateway layer                                              |
| **Forced HTTPS / HSTS** | no built-in middleware                    | use a pre route hook to check `$_SERVER['HTTPS']`, then `Helper::Show302()` ([Chapter 2-4](route-hooks.md)) |
| **Request body size / timeouts**        | PHP-FPM/nginx's job         | restrict in the server configuration                                                                        |
| **Dependencies and versions**           | `composer.lock` is your responsibility     | run `composer audit` before launch; lock versions, then deploy                                                     |

### 3. Performance: switches that pay off and switches that cost

| Switch / practice                                                                                | Direction     | Notes                                                                                                |
| ------------------------------------------------------------------------------------ | ------ | ------------------------------------------------------------------------------------------------- |
| `is_debug = false`                                                                   | ⬆ faster   | debug mode builds error pages and appends stack traces                                                                                    |
| OPcache (PHP side)                                                                       | ⬆ faster   | the framework is many small files — OPcache pays off the most; always enable it in production                                                                        |
| only install the `ext` you need                                                                          | ⬆ faster   | every ext gets initialized; don't declare anything you don't actually use (see [Chapter 3-5](overriding.md))                                                    |
| the `@compile`/wildcard rules of [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) | ⬆ faster   | rules are **compiled only once** (on first match), but with many rules still order them "most-used first"                                                               |
| `database_list_try_single = true`                                                    | ⬆ faster   | in a single-connection setup, don't route through the multi-connection logic                                                                                      |
| `local_database = false` (default)                                                         | ⬆ faster   | a child app with its own connection builds an extra connection pool ([Chapter 3-4](component-sharing.md))                                                  |
| `path_info_compact_enable = true`                                                    | ⬇ slower   | compat mode goes through query-string parsing; turn it off once rewrite is configured on the server                                                                    |
| `data_file_enable = true`                                                            | ⬇ slower   | one extra file IO per init ([`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md)), plus an extra JSON file written |
| `default_exception_do_log = true` (default)                                                | ⬇ disk grows  | every exception writes a log entry; make sure `runtime/` has rotation, and don't write logs to a ramdisk until it fills up                                                           |
| `use_output_buffer = true`                                                           | ⚠ semantics change | it changes "when headers are sent"; it is not "the more the better" — enable on demand ([Chapter 2-2](lifecycle.md))                                             |
| `view_skip_notice_error = true` (default)                                                  | ⚠ hides problems | undefined variables in views don't warn; you can turn it off temporarily during development to find bugs                                                                         |
|                                                                                      |        |                                                                                                   |

### 4. Logs and privacy

- logs land in `path_log` (default `runtime`) by default, with the filename template `log_%Y-%m-%d_%H_%i.log`;
- **never let logs land in the web root** (`runtime/` must not be directly accessible, see [Chapter 1-7](deployment.md));
- exception logs include stack traces by default (`default_exception_do_log = true`) — they may contain SQL, paths, and arguments, all sensitive information;
- don't write passwords/tokens into logs: [`Logger`](../reference/Core-Logger.md) only records what you give it ([Chapter 1-6](debugging.md)).

## Pre-launch checklist

### Security

| ✔ | Check item | How to fix | Evidence |
|---|---|---|---|
| ☐ | `is_debug` is `false`, and **no child app** turns it on | search globally for `'is_debug' => true`; note the root app and child apps combine with "or" | `App::_IsDebug()` |
| ☐ | the setting `duckphp_is_debug` is not turned on | check `DuckPhpSettings.config.php` / `.env` | `_Setting('duckphp_is_debug')` |
| ☐ | `error_404`/`error_500` point to your own error pages | in the views, **wrap debug blocks in `__is_debug()`** | [Chapter 2-12](exception.md) |
| ☐ | `installed` is set to `true` (projects using the install flow) | or make sure the install entry `url_install` is not publicly accessible | `checkInstallToPage()` |
| ☐ | `is_maintain` is `false` (unless actually maintaining) | set it true during maintenance and configure the `error_maintain` view | `App::initComponents()` |
| ☐ | all user data output goes through `__h()` | search views for `<?=` and review one by one | `CoreHelper::H()` |
| ☐ | all SQL uses placeholders | search for string concatenation inside `fetchAll(` / `execute(` | [Chapter 2-7](database.md) |
| ☐ | write operations have CSRF protection | **not provided by the framework**: issue and verify your own token | project-side |
| ☐ | uploads get type/size/path validation | **not provided by the framework**: validate yourself; the storage directory must not be executable | project-side |
| ☐ | authorization checks live in the Business layer | every resource access checks ownership | [Chapter 2-1](layers.md) |
| ☐ | cookies set `Secure`/`HttpOnly`/`SameSite` | pass the flags explicitly via `Helper::setcookie(...)` | [`Controller\ControllerHelper::setcookie()`](../reference/Foundation-Controller-ControllerHelper.md) |

| ☐ | the Session prefix doesn't collide with other apps | set `session_prefix` | `Foundation/SessionTrait.php` |
| ☐ | forced HTTPS redirect + HSTS | implement with a pre route hook | [Chapter 2-4](route-hooks.md) |
| ☐ | sensitive configuration is not in the repo | use `.env` (`use_env_file`) or a settings file, and keep that file out of git | [Chapter 1-5](configuration.md) |
| ☐ | `runtime/`, `config/` are not directly web-accessible | point the document root at `public/` | [Chapter 1-7](deployment.md) |
| ☐ | dependencies audited / versions locked | `composer audit` + commit `composer.lock` | project-side |

### Performance

| ✔ | Check item | How to fix | Evidence |
|---|---|---|---|
| ☐ | OPcache is on (`opcache.enable=1`, sensible `memory_consumption`) | php.ini / FPM pool config | server-side |
| ☐ | no unused extensions in `ext` | remove the extra declarations | [`DuckPhp::initComponentsOfInner()`](../reference/DuckPhp.md) |
| ☐ | with rewrite in place, `path_info_compact_enable` is off | set `false` | [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) |
| ☐ | if you don't persist ext options, `data_file_enable` is off | set `false` | `ExtOptionsLoader` |
| ☐ | route-map rules ordered by hit frequency; prefer exact matches over regex | adjust the `route_map_important` order | `RouteHookRouteMap::matchRoute()` |
| ☐ | database: frequent queries have indexes; read-only queries use the read connection | modeling + `Helper::DbForRead()` | [Chapter 2-7](database.md) |
| ☐ | hot data is cached with a TTL | [`Helper::Cache()->set($k, $v, $ttl)`](../reference/Component-Cache.md) | [Chapter 2-14](cache.md) |
| ☐ | logs have rotation and a sensible level | tune the `Logger` options and rotation policy | `src/Core/Logger.php` |
| ☐ | the `use_output_buffer` trade-off is decided | when in doubt, leave it off | [`Runtime`](../reference/Core-Runtime.md) option |
| ☐ | ran a round of load testing / slow-query logging before launch | enable `database_log_sql_query`, observe, then turn it off | [`DbManager`](../reference/Component-DbManager.md) option |

## Common patterns

**① Forcing HTTPS (a pre route hook — the framework provides no middleware)**

```php
// in onInited() of src/System/App.php
Helper::addRouteHook(function (string $path_info) {
    if (App::_()->isCli()) { return false; }
    if (!empty($_SERVER['HTTPS'])) { return false; }
    Helper::Show302('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    return true;                      // intercepted: the controller never runs (Chapter 2-4)
}, 'prepend-outter');
```

**② Hardening the session cookie**

```php
Helper::session_start([
    'cookie_secure'   => true,        // sent over HTTPS only
    'cookie_httponly' => true,        // not readable from JS
    'cookie_samesite' => 'Lax',       // mitigates CSRF
]);
```

(`Helper::session_start()` goes through `SystemWrapper`, so tests can replace it; the Session prefix uses the `session_prefix` option.)

**③ Explicitly separating debug/production in the error page**

```php
<!-- view/_sys/error_500.php -->
<h1>服务器开小差了</h1>
<?php if (__is_debug()): ?>
    <pre><?= __h($class . ': ' . $message) ?></pre>
    <pre><?= __h($trace) ?></pre>
<?php endif; ?>
```

**④ Caching hot data (with a TTL — avoid "cache that never expires")**

```php
public function hotProducts(): array
{
    $key  = 'hot_products';
    $data = Helper::Cache()->get($key);
    if ($data === null) {
        $data = ProductModel::_()->getHot(20);
        Helper::Cache()->set($key, $data, 300);     // 5 minutes
    }
    return $data;
}
```

**⑤ Turn on SQL logging once before launch to confirm there are no full-table scans**

```php
// for temporary troubleshooting; remember to turn it off after observing
$options = ['database_log_sql_query' => true, 'database_log_sql_level' => 'debug'];
```

## Common errors

| Symptom                         | Cause                                                                                                                                                                                                 | Fix                                  |
| -------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------- |
| Stack traces and paths visible in production                | `is_debug` is true, or some **child app** has it on                                                                                                                                                                        | search globally and turn it off; wrap debug blocks in `__is_debug()` in error views |
| The error page is the `Internal Error` placeholder text | `error_500` is not configured (likewise 404 is `404 File Not Found`)                                                                                                                                                      | point `error_500`/`error_404` at your own views   |
| Front/admin suddenly 302 to the install page           | `installed` is `false` (the constructors of [`UserControllerBase`](../reference/Foundation-Controller-UserControllerBase.md)/[`AdminControllerBase`](../reference/Foundation-Controller-AdminControllerBase.md) check it) | set it `true` ([Chapter 3-6](installer.md))   |
| Pages show the "Maintaining." placeholder       | `is_maintain` or the setting `duckphp_is_maintain` is true and `error_maintain` is not configured                                                                                                                                    | turn the switch off, or configure the `error_maintain` view         |
| User input is echoed into HTML verbatim            | views use `<?= $x ?>` directly                                                                                                                                                                                  | always `__h($x)`                        |
| Slower after going live                      | debug switches still on, OPcache off, `path_info_compact` still enabled                                                                                                                                                          | go through the "Performance" checklist above                          |
| The log directory fills the disk                  | exception logging is on by default with no rotation                                                                                                                                                                                       | configure rotation; lower the log level if needed                       |
| Believing "using a framework means CSRF is handled"          | the framework provides no CSRF mechanism                                                                                                                                                                                      | issue and verify your own token (the checklist item above)                      |
|                            |                                                                                                                                                                                                    |                                     |
|                            |                                                                                                                                                                                                    |                                     |

## Next steps

- [Chapter 1-7 The Minimal Go-Live Checklist](deployment.md): deployment, document root, directory permissions — the prerequisite for this chapter.
- [Chapter 2-12 Exceptions and Error Handling](exception.md): the full mechanism of error pages and exception reporting.
- [Chapter 4-9 Performance Tuning and Troubleshooting](troubleshooting.md): symptom → troubleshooting path.
- The reference manual: [DuckPhp\Core\App](../reference/Core-App.md), [options cheat sheet](../reference/options.md), [DuckPhp\Core\Logger](../reference/Core-Logger.md).
