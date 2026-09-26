# DuckPhp\Core\Logger

A minimal file-writing logger offering PSR-3-style level convenience methods (emergency…debug) that appends formatted lines to a log file.

## Introduction

`Logger` (PSR-3 by comment, not `implements`, best-effort interface alignment) fits the "logs land in a file with no external dependencies" need:

- Each line is assembled as `PLACE/PREFIX//date/time: PATH_INFO? :message\n` and appended via `error_log($msg, 3, $file)`;
- Keys in `context` are substituted into the format string (`{key}` is var_export'ed first);
- The log-file template uses `strftime`-ish `%Y`… — at runtime `log()` expands it with `preg_replace_callback('/%(.)/', =>date($1), template)`;
- The level is written as a filename prefix marker (`log_prefix`), default `DuckPhpLog`.

The level constants EMERGENCY/ALERT/CRITICAL/ERROR/WARNING/NOTICE/INFO/DEBUG are defined in the class as `const … = '…'`.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class Logger extends ComponentBase`
- Constants: `const EMERGENCY=>'emergency'; ALERT=>'alert'; CRITICAL=>'critical'; ERROR=>'error'; WARNING=>'warning'; NOTICE=>'notice'; INFO=>'info'; DEBUG=>'debug';`
- Related: `CoreHelper::Logger()` returns this handle.

## Options

`Logger::$options`:

| Option | Default | Description |
|---|---|---|
| `path_log` | `'runtime'` | Log directory (absolute, or relative to the root). |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | Log file name template; `%X` is expanded by `date(X)`. |
| `log_prefix` | `'DuckPhpLog'` | Prefix marker written to the line. |

> Relative paths are based on the **project root path**, i.e. `App::_()->getProjectPath()` (internally the source reads `App::Root()->options['path']`); this class itself has **no** `path` option (an early version had one; it was removed).

## Usage

```php
use DuckPhp\Core\Logger;

Logger::_()->init([ 'path_log' => __DIR__, ]);
Logger::_()->info('user {id} login', ['id' => 7]);
Logger::_()->error('db failed: {err}', ['err' => $e]);
```

Advanced (context expansion) values are var_export'ed into PHP expressions for troubleshooting. Also directly usable via `CoreHelper::Logger()`/App.

(If you want only a shell in the "business layer" — DuckPhp provides `Logger::_()`; as a matter of habit, write logs in the Helper compatibility layer rather than in the Model/Controller core.)

## Configuration example

```php
// project options
'path_log' => 'runtime',
'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',   // same as the default (you can also use a monthly/hourly template)
'log_prefix'         => 'Shop',
```

Produces a log like `runtime/log_2025-06-11_15_10.log`.

These three lines can go straight into the app options: at root `init()`, `App` initializes `Logger` with **this app's own options** (`initComponentsOfRoot()` configures `Logger::class => EXT_DEFAULT` → "follow this app"). The keys do not need to be written into `App::$core_options` (the three commented lines there are only hints); `Logger` picks from its own option table via `array_intersect_key`. **Only the first init takes effect** (`init_once=true`); passing them again later has no effect.

## Caveats

- No async/rotation; synchronous append.
- `log()` computes the template/path/prefix etc.; the level name must be one of the constants above.
- `init_once=true`; repeat init after the first is idempotent.
- If a write fails (e.g. the directory does not exist), it returns false / is recorded inside a catch and does not blow up upwards (try/catch in the source).

## All options

```php
    public $options = [
        'path_log' => 'runtime',
        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        'log_prefix' => 'DuckPhpLog',
    ];
```

## Methods

### Public methods

    public function log($level, $message, array $context = array())
Master entry: expands the template file path, replaces {key}, adds PATH_INFO and the date, then `error_log(…,3,file)`.

    public function emergency($message, array $context = array())
log(EMERGENCY,…).

    public function alert($message, array $context = array())
log(ALERT,…).

    public function critical($message, array $context = array())
log(CRITICAL,…).

    public function error($message, array $context = array())
log(ERROR,…).

    public function warning($message, array $context = array())
log(WARNING,…).

    public function notice($message, array $context = array())
log(NOTICE,…).

    public function info($message, array $context = array())
log(INFO,…).

    public function debug($message, array $context = array())
log(DEBUG,…).

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) `Logger()` handle
