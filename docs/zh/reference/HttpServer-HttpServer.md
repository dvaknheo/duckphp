# DuckPhp\HttpServer\HttpServer

内置 PHP 开发用 HTTP 服务器。

## 简介

`HttpServer` 基于 PHP 内置的 `php -S` 命令，提供简单的 HTTP 服务器封装。它支持配置主机、端口、文档根目录，并可通过命令行参数覆盖配置，适合在开发环境中快速启动本地服务。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `host` | `'127.0.0.1'` | 服务器监听主机。 |
| `port` | `'8080'` | 服务器监听端口。 |
| `path` | `''` | 文档根目录的前置路径。 |
| `path_document` | `'public'` | 文档根目录目录名，与 `path` 拼接为完整根目录。 |
| `background` | `false` | 是否在后台运行服务器。 |

> 命令行参数：`host`、`port`、`docroot`、`background`、`dry`、`help` 等可进一步覆盖或扩展行为。

## 使用方式

### 快速启动

```php
use DuckPhp\HttpServer\HttpServer;

HttpServer::RunQuickly([
    'host' => '127.0.0.1',
    'port' => '8080',
    'path' => '',
    'path_document' => 'public',
]);
```

### 获取实例运行

```php
use DuckPhp\HttpServer\HttpServer;

$server = HttpServer::_();
$server->init([
    'host' => '0.0.0.0',
    'port' => '8888',
    'path' => '/var/www',
    'path_document' => 'public',
])->run();
```

### 后台运行与关闭

```php
use DuckPhp\HttpServer\HttpServer;

$server = HttpServer::_();
$server->init([
    'port' => '8090',
    'background' => true,
])->run();

$pid = $server->getPid();
// ...
$server->close();
```

### 命令行使用

在项目目录下通过 CLI 启动服务器：

```bash
php -r "require 'vendor/autoload.php'; DuckPhp\HttpServer\HttpServer::RunQuickly([]);"
```

常用参数：

```bash
-H 127.0.0.1  # 主机
-P 8080       # 端口
-t public     # 文档根目录
-b            # 后台运行
--dry         # 仅显示命令，不执行
-h            # 显示帮助
```

## 配置示例

### 基础配置

```php
class App extends DuckPhp
{
    public $options = [
        'http_server' => [
            'host' => '127.0.0.1',
            'port' => '8080',
            'path' => '',
            'path_document' => 'public',
        ],
    ];
}
```

### 开发环境后台运行

```php
HttpServer::RunQuickly([
    'host' => '0.0.0.0',
    'port' => '8088',
    'path' => dirname(__DIR__),
    'path_document' => 'public',
    'background' => true,
]);
```

## 注意事项

1. 生产环境不建议使用此内置服务器，仅适用于开发调试。
2. 文档根目录最终路径为 `path/path_document`。例如 `path` 为空，`path_document` 为 `public` 时，文档根目录为 `public/`。
3. 命令行参数 `host`、`port`、`docroot` 优先级高于配置选项。
4. 后台运行依赖 `posix_kill`，在 Windows 环境下可能无法正常工作。
5. `dry` 模式仅输出要执行的 `php -S` 命令，不真正启动服务。

## 全部选项

```php
public $options = [
    'host' => '127.0.0.1',
    'port' => '8080',
    'path' => '',
    'path_document' => 'public',
    // 'background' => false,
];
```

## 方法列表

### 公共方法

    public static function _($object = null)
获取或设置单例实例。

    public static function RunQuickly($options)
快速初始化并运行服务器。

    public function init(array $options, object $context = null)
初始化服务器，合并配置并解析命令行参数。

    public function isInited(): bool
返回组件是否已初始化（当前始终返回 `false`）。

    public function run()
运行服务器，展示欢迎信息，处理 `--help` 或启动 HTTP 服务。

    public function getPid(): int
获取当前服务器进程 ID。

    public function close()
关闭后台运行的服务器，发送 `SIGKILL` 信号。

### 受保护方法

    protected function getopt(string $options, array $longopts, &$optind)
封装 `getopt()` 调用，便于测试覆盖。

    protected function parseCaptures(array $cli_options): array
解析命令行选项，返回合并后的参数数组。

    protected function showWelcome(): void
输出欢迎信息。

    protected function showHelp()
输出命令行帮助信息。

    protected function runHttpServer()
构建并执行 `php -S` 命令，启动 PHP 内置 HTTP 服务器。

## 相关链接

- [DuckPhp\HttpServer\HttpServerInterface](HttpServer-HttpServerInterface.md)
