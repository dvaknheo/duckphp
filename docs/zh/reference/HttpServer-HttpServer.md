# DuckPhp\HttpServer\HttpServer

## 简介

`HttpServer` 是 DuckPHP 内置的“用 PHP 内置服务器跑项目”的启动器：它按参数拼出 `php -S host:port -t docroot` 命令并执行，用于本地开发/演示（`./vendor/bin/duckphp` 等 CLI 背后常驱动它）。支持 `--help`/`--host`/`--port`/`--docroot`/`--dry`/`--background` 等命令行选项，也支持 PHP 7.4+ 内置服务器多进程（`PHP_CLI_SERVER_WORKERS`）。

该类为独立实现（不继承 `ComponentBase`），自带一份“嵌入式单例” `_()`（支持 `__SINGLETONEX_REPALACER` 替换钩子）。

## 类信息

- 命名空间：`DuckPhp\HttpServer`
- 声明：`class HttpServer`

## 选项

`public $options` 属性（`init()` 时与传入项深度合并）：

| 键 | 默认值 | 说明 |
|---|---|---|
| `host` | `'127.0.0.1'` | 监听地址；也可被 CLI 参数 `--host`/`-H` 覆盖。 |
| `port` | `'8080'` | 监听端口；也可被 CLI 参数 `--port`/`-P` 覆盖。 |
| `path` | `''` | 项目根路径，与 `path_document` 拼成 docroot。 |
| `path_document` | `'public'` | 文档目录名；实际 docroot = `path/path_document`。 |
| `workers` | `null` | 非空时用 `PHP_CLI_SERVER_WORKERS=N` 启动多进程内置服务器。 |

CLI 选项元数据在 `$cli_options`（`help/host/port/docroot/dry/background`，含 `short/desc/required/optional`），用于 `parseCaptures()` 解析与 `--help` 输出。

## 使用方式

```php
use DuckPhp\HttpServer\HttpServer;

HttpServer::RunQuickly([
    'host' => '127.0.0.1',
    'port' => '9628',
    'path' => __DIR__,
    // 'workers' => 4, // PHP 7.4+ 多进程
]);
```

命令行方式（框架 CLI 也会用到）：

```text
php -r 'require "vendor/autoload.php"; \DuckPhp\HttpServer\HttpServer::RunQuickly([]);' -- --host 0.0.0.0 --port 8080 --docroot public
```

## 注意事项

- `init()` 会：合并选项 → 记录 host/port → `parseCaptures($cli_options)` 解析 CLI 参数（短参映射到长参，剩余位置参数并入）→ 计算 docroot → 用 CLI 参数覆盖 host/port/docroot。
- `run()`：先输出欢迎语；`--help` 时打印帮助；否则进入 `runHttpServer()`。
- `runHttpServer()`：拼出并执行命令；`--dry` 只打印命令不执行；`--background`/`-b` 时后台运行并把 PID 存入 `$pid`（随后可用 `getPid()`/`close()`）。
- `close()` 使用 `posix_kill($pid, 9)`（仅类 Unix；Windows 环境按需调整）。
- 源码中 `isInited()` 读取 `$is_inited`，但 `init()` 并未把该属性置真——实际初始化状态以 `init()` 已执行为准。
- `docroot` 的 CLI 覆盖键是 `docroot`（对应 `$cli_options` 里的 `docroot`），而选项表里对应默认目录用的是 `path`+`path_document` 组合。

## 方法列表

### 公共方法

    public static function _($object = null)
嵌入式单例入口：有 `__SINGLETONEX_REPALACER` 时交给它；传对象则登记为实例；否则创建并缓存。

    public function __construct()
空构造器。

    public static function RunQuickly($options)
快捷启动：`static::_()->init($options)->run()`。

    public function init(array $options, ?object $context = null)
合并选项、解析 CLI 参数并计算 docroot，返回自身（`$context` 不使用）。

    public function isInited(): bool
返回 `$is_inited`（源码中 `init()` 未置真，行为以源码为准）。

    public function run()
启动入口：打印欢迎语；`--help` 时输出帮助；否则运行内置服务器。

    public function getPid(): int
返回后台运行时的进程 PID。

    public function close()
结束后台进程：若存在 Windows 的 `proc_open` 句柄（`$process`）则 `proc_terminate` + `proc_close` 并清零 PID；否则无 PID 返回 `false`；Windows 下用 `taskkill /F /T /PID` 杀进程树；类 Unix 下 `posix_kill($this->pid, 9)`。

### 受保护方法

    protected function getopt(string $options, array $longopts, &$optind)
包装原生 `getopt()`（便于测试替换）。

    protected function parseCaptures(array $cli_options): array
按 `$cli_options` 元数据解析 CLI：生成短/长选项串，调用 `getopt()`，把短参映射为长参，并把剩余位置参数并入返回数组。

    protected function showWelcome(): void
输出欢迎语。

    protected function showHelp()
按 `$cli_options` 输出 `--help` 帮助文本与当前参数。

    protected function runHttpServer()
拼装并执行 `php -S host:port -t docroot` 命令；`workers` 设置 `PHP_CLI_SERVER_WORKERS`；`--dry` 仅打印；后台模式执行后记录 `$pid` 并返回。Windows 平台直接转 `runHttpServerOnWindows()`（无 POSIX shell）。

    protected static function isWindows(): bool
判断当前平台是否 Windows（`PHP_OS_FAMILY === 'Windows'`）。

    protected function runHttpServerOnWindows()
Windows 专用启动：以数组形式 `proc_open()` 启动内置服务器（不经 cmd.exe）；后台模式重定向到 NUL、记录真实 PID、`register_shutdown_function` 回收子进程。支持 `--dry`。

    protected function serverEnvironment(): array
为服务器子进程准备环境变量：把 `workers` 转成 `PHP_CLI_SERVER_WORKERS` 环境变量（Windows 下不能像 POSIX 那样写在命令前）。

## 相关链接

- [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md) — 启动器接口
