# 1-6 Debugging, Logging, and a First Taste of the CLI

> What this solves: the fastest way to see the truth when something breaks; where logs go and how to write them; and two command-line entry points you can use right away.
> Prerequisites: [Chapter 1-5](configuration.md). About 15 minutes.

## 1. Turn on the debug switch first

```php
// turn it on temporarily in the local entry file (never in production)
\MyProj\System\App::RunQuickly(['is_debug' => true]);
```

Debug mode is decided by "**`duckphp_is_debug` in the settings OR the root app's `is_debug` option**" ([`App::IsDebug()`](../reference/Core-App.md)), so turning it on via a local settings file works too. Once on:

| Change | Description |
|---|---|
| Exception pages show the stack | Served by the `error_debug` view (if not set, the framework renders its own: exception class, message, file and line, full call stack) |
| Development warnings escalate | `_OnDevErrorHandler` turns PHP notices/warnings into visible errors (Chapter 2-12) |
| 404 pages carry routing info | Prints the route error (hints like "can't Reflection class (…)"), far easier to pin down than a bare 404 |

The four error-page options (all are **view names**, relative to `path_view`):

```php
'error_404'      => '_sys/error_404',       // 404
'error_500'      => '_sys/error_500',       // production error page
'error_debug'    => '_sys/error-debug',     // debug page (used mutually exclusively with the two above)
'error_maintain' => null,                   // maintenance page (when is_maintain or duckphp_is_maintain is true)
```

The scaffold already ships `error_404.php` and `error_500.php` in `skeleton/view/_sys/`; copy them and adjust the wording.

## 2. Logging

The logging component is [`Logger`](../reference/Core-Logger.md) (eight PSR-3-style levels):

```php
use DuckPhp\Core\Logger;

Logger::_()->info('user {id} login', ['id' => 7]);
Logger::_()->warning('something odd');
Logger::_()->error('db failed: ' . $e->getMessage());
// equivalent entry: \DuckPhp\Core\CoreHelper::Logger()->info(...)
```

The eight levels: `emergency` / `alert` / `critical` / `error` / `warning` / `notice` / `info` / `debug`; you can also specify directly with `log($level, $message, $context)`.

Three options decide where logs land:

| Option | Default | Description |
|---|---|---|
| `path_log` | `'runtime'` | Log directory (relative to the **project root** `path`, or an absolute path) |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | File name template; `%X` is expanded via `date(X)` |
| `log_prefix` | `'DuckPhpLog'` | Per-line prefix |

So by default it writes to `<project root>/runtime/log_2026-09-19_10_30.log`.

Two behaviors worth knowing:

- **The framework logs uncaught exceptions by default** (option `default_exception_do_log`, default `true`): `_OnDefaultException()` writes the exception into the log — when production gives a 500, look in `runtime/` for the log first.
- **A failed write doesn't blow up**: `Logger::log()` try/catches internally and returns `false` on failure. So when "the log directory isn't writable" the page is fine — the log is just gone (the go-live checklist in Chapter 1-7 checks that `runtime/` is writable).

## 3. First taste of the CLI: two entry points

The framework's bundled command entry `bin/cli.php` (shares the codebase with the Web entry):

```bash
php bin/cli.php help        # lists all commands
php bin/cli.php version     # version
php bin/cli.php run         # runs the app on the built-in HTTP server (default 127.0.0.1:8080)
php bin/cli.php debug       # toggles the debug flag (needs the data_file capability, see below)
```

The installer entry `vendor/bin/duckphp` (only three commands):

```bash
php vendor/bin/duckphp new      # generates a new project from skeleton/
php vendor/bin/duckphp show     # shows the current installation
php vendor/bin/duckphp help
```

Custom commands are `command_xxx()` methods written in the controller layer / command classes (Chapter 2-16); a child app's commands take a phase prefix (`php bin/cli.php shop-help`).

> The `debug` command rewrites the "extra options file" ([`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md), file `DuckPhpApps.config.php`), so it needs `data_file_enable` on and `is_debug` added to `data_file_bump_keys`; otherwise it only tells you to configure that.

## 4. Four moves when something goes wrong

```php
// 1) which singletons exist right now, in which phase (the demo's /files page, demo/view/files.php, displays exactly this)
\DuckPhp\Core\PhaseContainer::Dump();          // or PhaseContainer::_()->dumpAllObject()

// 2) which routes exist right now (first stop for a 404)
print_r(\DuckPhp\Ext\RouteLister::_()->listAll());

// 3) which controller/method/path the current request landed on
\DuckPhp\Core\Route::_()->getRouteCallingClass();
\DuckPhp\Core\Route::_()->getRouteCallingMethod();
\DuckPhp\Core\Route::_()->getRouteCallingPath();

// 4) the options in effect and the actual settings
var_dump(\DuckPhp\Core\App::_()->options);
var_dump(\DuckPhp\Core\App::_Setting());
```

Together with the built-in `php bin/cli.php debug` and the logs, the vast majority of "page 404 / 500 / behaves wrong" cases can be pinned down in minutes (to list the route table, first register the `routes` command from `Ext\RouteLister` — see [Chapter 4-13](ext-classes.md) §5).

## Common errors

| Symptom                        | Cause                             | Fix                                                     |
| ------------------------- | ------------------------------ | ------------------------------------------------------ |
| No logs in `runtime/`          | The directory isn't writable; or `path_log` was changed          | `chmod` for write permission; confirm `App::_()->options['path_log']`        |
| Page errors but no stack visible                | `is_debug` is off, so it goes through `error_500`  | Temporarily set `is_debug=true`; or edit the `error_500` view                   |
| Custom error pages don't take effect                 | The option holds a **file path** instead of a view name           | Write the view name relative to `view/`, e.g. `'_sys/error_404'`                  |
| `php bin/cli.php run` won't open | Port taken / wrong document root                   | Change port with `--port=9000`; the document root is decided by `path_document` (default `public`) |
| Command not found in CLI                | The command belongs to a child app                      | Add the phase prefix (`shop-help`), see [Chapter 2-16](cli.md) and [Chapter 3-1](advanced-phase.md)                          |
| The debug page shows too much; want a clean version for users       | Production should turn off `is_debug` and configure `error_500` | See the go-live checklist in Chapter 1-7                                           |

## Next steps

- [Chapter 1-7 Minimal Go-Live Checklist](deployment.md): turn the debug switch back off and check what should be checked.
- [Chapter 2-12 Exceptions and Error Handling](exception.md): exception layering and custom handling.
- [Chapter 2-16 CLI and Scheduled Tasks](cli.md): command classes, argument parsing, crontab.
