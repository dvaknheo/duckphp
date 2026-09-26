# DuckPhp\HttpServer\HttpServer

## Introduction

`HttpServer` is DuckPHP's built-in launcher for "running a project on PHP's built-in server": it assembles a `php -S host:port -t docroot` command from the arguments and executes it, for local development/demos (CLIs such as `./vendor/bin/duckphp` often drive it behind the scenes). It supports the command-line options `--help`/`--host`/`--port`/`--docroot`/`--dry`/`--background` and so on, and also the multi-process built-in server of PHP 7.4+ (`PHP_CLI_SERVER_WORKERS`).

The class is a standalone implementation (it does not extend `ComponentBase`) and carries its own "embedded singleton" `_()` (which supports the `__SINGLETONEX_REPALACER` replacement hook).

## Class info

- Namespace: `DuckPhp\HttpServer`
- Declaration: `class HttpServer`

## Options

The `public $options` property (deeply merged with what is passed in when `init()` runs):

| Option | Default | Meaning |
|---|---|---|
| `host` | `'127.0.0.1'` | The listen address; it can also be overridden by the CLI option `--host`/`-H`. |
| `port` | `'8080'` | The listen port; it can also be overridden by the CLI option `--port`/`-P`. |
| `path` | `''` | The project root path, combined with `path_document` into the docroot. |
| `path_document` | `'public'` | The document directory name; the actual docroot = `path/path_document`. |
| `workers` | `null` | When not empty, starts a multi-process built-in server with `PHP_CLI_SERVER_WORKERS=N`. |

The CLI option metadata lives in `$cli_options` (`help/host/port/docroot/dry/background`, each with `short/desc/required/optional`) and is used by `parseCaptures()` for parsing and by the `--help` output.

## Usage

```php
use DuckPhp\HttpServer\HttpServer;

HttpServer::RunQuickly([
    'host' => '127.0.0.1',
    'port' => '9628',
    'path' => __DIR__,
    // 'workers' => 4, // PHP 7.4+ multi-process
]);
```

The command-line form (the framework CLI uses it too):

```text
php -r 'require "vendor/autoload.php"; \DuckPhp\HttpServer\HttpServer::RunQuickly([]);' -- --host 0.0.0.0 --port 8080 --docroot public
```

## Caveats

- `init()` does the following: merges the options → records host/port → parses the CLI arguments with `parseCaptures($cli_options)` (short options are mapped onto long ones, and the remaining positional arguments are merged in) → computes the docroot → overrides host/port/docroot with the CLI arguments.
- `run()`: prints the welcome message first; with `--help` it prints the help; otherwise it goes into `runHttpServer()`.
- `runHttpServer()`: assembles and executes the command; `--dry` only prints the command without executing it; with `--background`/`-b` it runs in the background and stores the PID in `$pid` (afterwards `getPid()`/`close()` can be used).
- `close()` uses `posix_kill($pid, 9)` (Unix-like only; adjust it as needed on Windows).
- `isInited()` reads `$is_inited`, which `init()` sets to true (see line 120 of `HttpServer.php` in the source), so "init has run" and `isInited() === true` agree.
- The CLI override key for `docroot` is `docroot` (corresponding to the `docroot` in `$cli_options`), whereas the option table's matching default directory uses the combination `path`+`path_document`.

## Methods

### Public methods

    public static function _($object = null)
The embedded singleton entry point: with a `__SINGLETONEX_REPALACER` it is handed over to that; passing an object registers it as the instance; otherwise one is created and cached.

    public function __construct()
An empty constructor.

    public static function RunQuickly($options)
Quick start: `static::_()->init($options)->run()`.

    public function init(array $options, ?object $context = null)
Merges the options, parses the CLI arguments and computes the docroot, sets `is_inited = true` and returns itself (`$context` is not used).

    public function isInited(): bool
Returns whether `init()` has run.

    public function run()
The start entry point: prints the welcome message; with `--help` it outputs the help; otherwise it runs the built-in server.

    public function getPid(): int
Returns the process PID when running in the background.

    public function close()
Ends the background process: when a Windows `proc_open` handle (`$process`) exists it does `proc_terminate` + `proc_close` and zeroes the PID; otherwise, with no PID, it returns `false`; on Windows it kills the process tree with `taskkill /F /T /PID`; on Unix-like systems it uses `posix_kill($this->pid, 9)`.

### Protected methods

    protected function getopt(string $options, array $longopts, &$optind)
Wraps the native `getopt()` (making it easy to replace in tests).

    protected function parseCaptures(array $cli_options): array
Parses the CLI by the `$cli_options` metadata: it builds the short/long option string, calls `getopt()`, maps short options onto long ones, and merges the remaining positional arguments into the returned array.

    protected function showWelcome(): void
Outputs the welcome message.

    protected function showHelp()
Outputs the `--help` text and the current arguments according to `$cli_options`.

    protected function runHttpServer()
Assembles and executes the `php -S host:port -t docroot` command; `workers` sets `PHP_CLI_SERVER_WORKERS`; `--dry` only prints; in background mode it records `$pid` after executing and returns. On the Windows platform it goes straight to `runHttpServerOnWindows()` (there is no POSIX shell).

    protected static function isWindows(): bool
Tells whether the current platform is Windows (`PHP_OS_FAMILY === 'Windows'`).

    protected function runHttpServerOnWindows()
The Windows-specific start: it launches the built-in server with `proc_open()` in array form (without going through cmd.exe); in background mode it redirects to NUL, records the real PID and reclaims the child process with `register_shutdown_function`. `--dry` is supported.

    protected function serverEnvironment(): array
Prepares the environment variables for the server child process: it turns `workers` into the `PHP_CLI_SERVER_WORKERS` environment variable (on Windows it cannot be written before the command as it is under POSIX).

## Related links

- [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) — the launcher interface
