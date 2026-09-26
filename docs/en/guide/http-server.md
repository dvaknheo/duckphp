# 4-5 Long-running processes and the embedded HTTP server

> What this solves: how to start and stop the framework's built-in HTTP server (`DuckPhp\HttpServer\HttpServer`), why it can run RPC round-trips, and which state survives when "one process handles several requests".
> Prerequisites: [Chapter 2-2 The request lifecycle](lifecycle.md), [Chapter 4-6 Multiple entry points, domains and SAPIs](multi-entry.md). About 20 minutes.
> Examples: `tests/data_for_tests/ZAllDemoTest.config.php` (a smoke test that really starts the server and curls every entry point), `demo/public/rpc.php` (a round-trip example that needs several workers).

```bash
# ① start it through the project CLI (which calls HttpServer internally)
php cli.php run
# ② call HttpServer directly (equivalent, with more obvious arguments)
php -r 'require "autoload.php"; \DuckPhp\HttpServer\HttpServer::RunQuickly(["path"=>__DIR__."/demo/","port"=>8080,"path_document"=>"public"]);'
```

## Minimal example

A usage that really exists in the repository (`tests/data_for_tests/ZAllDemoTest.config.php`; the test starts the server from it):

```php
'server_options' => [
    'path'          => realpath(__DIR__ . '/../../demo/') . '/',
    'path_document' => 'public',      // document root (relative to path)
    'port'          => 9802,
    'background'    => true,          // start in the background so the test can curl it
    'workers'       => 4,             // PHP built-in server multi-process (7.4+)
],
```

```php
HttpServer::RunQuickly($server_options);
sleep(1);                              // ugly but practical: wait for it to come up
$data = file_get_contents('http://127.0.0.1:9802/demo.php');
HttpServer::_()->close();              // done; do not leave orphan processes
```

## How it works

### 1. It is really `php -S`

The command `HttpServer::runHttpServer()` assembles (you can print it without running it with `--dry`):

```
PHP_CLI_SERVER_WORKERS=<workers> php -S <host>:<port> -t <docroot>
```

- `PHP_CLI_SERVER_WORKERS` is only added when `workers` has a value — that is the multi-process mode of PHP 7.4+'s built-in server, and **an RPC round-trip (the server requesting itself) requires it** (`demo/public/rpc.php` is exactly that case);
- when `background` is true the command gets `> /dev/null 2>&1 & echo $!;` appended, recording the PID for `getPid()`;
- **Windows takes a different branch** (`proc_open` with an argument array, bypassing the shell), because `& echo $!` prints a fake PID under cmd.exe.

### 2. Options and CLI short arguments

| Option | Default | Description |
|---|---|---|
| `host` | `127.0.0.1` | Listen address (change it explicitly to expose it, and mind the security) |
| `port` | `8080` | Port |
| `path` | `''` | Project root |
| `path_document` | `public` | Document root (relative to `path`) |
| `workers` | `null` | Number of processes; `null`/`0` means single-process |
| `background` | `false` | A **hidden option**: run in the background (also triggered by `-b/--background`) |

Command-line arguments are supported too: `-H/--host`, `-P/--port`, `-t/--docroot`, `-b/--background`, `--dry` (print the command only), `-h/--help`. Command-line arguments take precedence over options.

```bash
php cli.php run -H 0.0.0.0 -P 9000 -t public --dry     # see what it would execute first
```

### 3. Starting, stopping and the lifecycle

| Method | What it does |
|---|---|
| `HttpServer::RunQuickly($options)` | `init($options)->run()` |
| `HttpServer::_()->getPid()` | The child process PID in background mode |
| `HttpServer::_()->close()` | Finish: on POSIX it uses `posix_kill($pid, 9)`; on Windows `proc_terminate`/`taskkill /T` |
| `HttpServer::_()->isInited()` | Initialisation state |

A `close()` returning `false` means **there is no server to close in the current process** (say `background` was not enabled, or the PID was already cleared elsewhere).

### 4. Its relationship with the CLI: `php cli.php run`

The built-in `run` command ([`Command::command_run()`](../reference/Component-Command.md)) does this:

1. take the CLI arguments as server options (and also put the current app class into `http_app_class`);
2. if the arguments name another `http_server` class, replace the `HttpServer` singleton with it;
3. temporarily set `cli_enable` to `false` (so the child process runs in web mode), start the server, then set it back.

So "long-running" in this framework means: **a built-in server process plus a complete `RunQuickly` flow per request**. It is not a php-fpm-style worker pool, and there is no object reuse across requests.

### 5. What survives when one process handles several requests

In multi-worker mode the built-in server still has an independent PHP process per request (that is the `php -S` model), so **framework singletons do not survive across requests**. The two cases where leftovers really matter are:

- **a long-running script of your own** (say a `while` loop calling `RunQuickly()` repeatedly, or the framework embedded in another daemon): singletons, [`Runtime`](../reference/Core-Runtime.md) state and database connections all stay. Clean them up explicitly: [`Route::_()->clear()`](../reference/Core-Route.md), `Runtime::_()->clear()`, [`PhaseContainer::_()->RestAllContainerForTesting()`](../reference/Core-PhaseContainer.md) ([Chapter 4-1](container-phases.md));
- **output buffering enabled** (`use_output_buffer = true`): `Runtime` calls `ob_start()`, and the buffer is only flushed when the request reaches `Runtime::_()->clear()`; do not forget that step in a long-running script, or responses "pile up without being sent".

A few related runtime queries:

```php
Helper::isRunning();        // is Runtime "handling a request" right now
Helper::isInException();    // are we inside exception handling
Runtime::_()->isOutputed(); // has anything been output already
```

### 6. Do not use it in production

The built-in server is a **single-process (or few-worker) toy server**: no process management, no timeouts or rate limiting, no HTTPS, limited concurrency. Production uses php-fpm + nginx ([Chapter 1-7](deployment.md), [Chapter 2-18](security-performance.md)). If you need a high-performance long-running solution (RoadRunner/Swoole/FrankenPHP), the framework **does not ship one**; you wire `RunQuickly` into their lifecycle yourself, paying particular attention to the "leftover state" point above.

## Common patterns

**① Starting a server in development**

```bash
php cli.php run -H 127.0.0.1 -P 8080
```

**② Start in the background, assert, close (in tests)**

```php
HttpServer::RunQuickly(['path' => $app_path, 'port' => 9802, 'background' => true, 'workers' => 4]);
try {
    $body = file_get_contents('http://127.0.0.1:9802/demo.php');
    $this->assertStringContainsString('Hello', $body);
} finally {
    HttpServer::_()->close();
}
```

**③ See the command before starting it**

```bash
php cli.php run --dry
```

**④ Enable workers when you need "the server requests itself" (RPC/internal APIs)**

```php
['workers' => 4]     // without several workers a round-trip request waits forever
```

**⑤ Make one entry use a different [HttpServer](../reference/HttpServer-HttpServer.md) implementation**

```bash
php cli.php run --http_server=MyProj/Http/MyServer
```

(`command_run` resolves the `http_server` argument into a class name and replaces the singleton.)

The class you bring in must implement [`HttpServerInterface`](../reference/HttpServer-HttpServerInterface.md): the four methods `RunQuickly($options)` / `run()` / `getPid()` / `close()`.

## Common errors

| Symptom                         | Cause                                             | Fix                                                            |
| -------------------------- | ---------------------------------------------- | ------------------------------------------------------------- |
| The port is in use / it will not start                | A previous background server was never stopped                                    | `HttpServer::_()->close()`; or use another port; find leftovers with `ps aux \| grep "php -S"` |
| A test fails intermittently with connection refused               | `background` started it but nothing waited for it to be ready                      | `sleep(1)` after starting (what the repository does today), or retry probing the port |
| The RPC example hangs/times out                | `workers` was not enabled                                   | Set `workers` (≥2)                                               |
| Background mode produces no output at all                 | Output is redirected to `/dev/null`                            | Use foreground mode or `--dry` while debugging                                             |
| `close()` returns `false`       | There is no PID to close in the current process                                  | Make sure you close in the **process that started the server**; never close across processes |
| On Windows starting the server reports "the system cannot find the path specified" | It used to start through a shell, and cmd.exe cannot parse `& echo $!`           | Current versions take the `proc_open` branch on Windows; upgrade to the current code |
| A long-running script's response "piles up without being sent"              | `use_output_buffer` is on but `Runtime::clear()` is never reached | Make sure the request wind-down logic (`Route::clear()`/`Runtime::clear()`) runs              |
| Putting the built-in server into production                 | It is not a production-grade server                                      | Move to php-fpm/nginx ([Chapter 1-7](deployment.md))                       |

## Next steps

- [Chapter 4-6 Multiple entry points, domains and SAPIs](multi-entry.md): many entries into one code base.
- [Chapter 4-7 Test infrastructure and the coverage pipeline](coverage.md): the flow that starts a server for end-to-end smoke tests.
- [Chapter 4-9 Performance tuning and troubleshooting](troubleshooting.md): how to chase port/process problems.
- Reference manual: [DuckPhp\HttpServer\HttpServer](../reference/HttpServer-HttpServer.md), [DuckPhp\HttpServer\HttpServerInterface](../reference/HttpServer-HttpServerInterface.md), [DuckPhp\Component\Command](../reference/Component-Command.md), [DuckPhp\Core\Runtime](../reference/Core-Runtime.md).
