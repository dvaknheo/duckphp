# DuckPhp\Ext\DuckPhpInstaller

DuckPhp 项目安装器扩展组件。

## 简介

`DuckPhpInstaller` 提供命令行工具，用于在当前目录新建项目、显示帮助信息或运行示例 HTTP 服务器。它会把框架自带的 skeleton 目录复制到目标目录，并替换命名空间等占位符。

该组件通常不直接参与 Web 请求处理，而是作为 `bin/duckphp` 或命令行入口使用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 目标路径。新项目将被复制到此目录。 |
| `namespace` | `''` | 项目命名空间。为空时安装器会交互式询问。 |
| `force` | `false` | 是否覆盖已存在的文件。 |
| `autoloader` | `'vendor/autoload.php'` | 自动加载文件路径，用于替换 `@DUCKPHP_HEADFILE` 占位符。 |
| `verbose` | `false` | 是否显示复制进度。 |
| `help` | `false` | 是否显示帮助信息。 |

## 使用方式

### 命令行入口

```php
$installer = new \DuckPhp\Ext\DuckPhpInstaller();
$installer->command_new();  // 创建新项目
$installer->command_help(); // 显示帮助
$installer->command_show();  // 运行示例服务器
```

### 显示帮助

```php
$installer = new \DuckPhp\Ext\DuckPhpInstaller();
$installer->showHelp();
```

输出内容包含可用的命令、参数及说明。

### 创建新项目

```php
$installer = new \DuckPhp\Ext\DuckPhpInstaller();
$installer->newProject();
```

该方法会读取 CLI 参数，如 `--namespace`、`--force`、`--verbose`、`--autoloadfile`、`--path`，然后把 `src/Ext/../../skeleton` 目录复制到目标位置。

### 运行示例服务器

```php
$installer = new \DuckPhp\Ext\DuckPhpInstaller();
$installer->runDemo();
```

默认使用 `template` 目录作为项目路径，端口默认为 `8080`。可以通过 `--port` 指定端口，通过 `--http_server` 指定自定义 HTTP 服务器类。

## 配置示例

安装器通常不需要在 Web 应用配置中加载。命令行用法示例：

```php
#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

$installer = new \DuckPhp\Ext\DuckPhpInstaller();
$installer->command_new();
```

## 注意事项

1. `newProject()` 依赖 `DuckPhp\Core\Console` 获取 CLI 参数，运行环境必须支持命令行。
2. 如果目标文件已存在且 `force` 为 `false`，安装器会提示使用 `--force` 覆盖并终止。
3. 复制文件时会替换三个占位符：`@DUCKPHP_HEADFILE`、`@DUCKPHP_DELETE`、`@DUCKPHP_NAMESPACE`。
4. `runDemo()` 通过 `DuckPhp\HttpServer\HttpServer` 启动服务器，需要单独安装 HTTP 服务器组件。

## 全部选项

```php
    public $options = [
        'path' => '',
        'namespace' => '',
        'force' => false,
        'autoloader' => 'vendor/autoload.php',
        'verbose' => false,
        'help' => false,
    ];
```

## 方法列表

### 公共方法

    public function command_new(): void
创建新项目。初始化组件后调用 `newProject()`。

    public function command_help()
显示帮助信息。

    public function command_show()
运行示例服务器。

    public function showHelp(): void
输出命令行帮助文本。

    public function newProject($options = [])
根据 CLI 参数复制 skeleton 目录并替换命名空间。

    public function runDemo(): void
使用 template 目录运行示例 HTTP 服务器。

### 受保护方法

    protected function dumpDir(string $source, string $dest, bool $force = false): void
递归复制源目录到目标目录，并处理文件过滤。

    protected function checkFilesExist(string $source, string $dest, array $files): bool
检查目标文件是否已存在。如果存在且未开启 `force`，则返回 `false`。

    protected function createDirectories(string $dest, array $files): bool
根据文件列表创建目标目录结构。

    protected function filteText(string $data, bool $is_in_full, string $short_file_name): string
对单个文件内容进行过滤：替换头文件、删除标记、替换命名空间。

    protected function filteMacro(string $data): string
删除包含 `@DUCKPHP_DELETE` 的整行。

    protected function filteNamespace(string $data, string $namespace): string
替换 `@DUCKPHP_NAMESPACE` 和 `YourProjectName\` 为指定命名空间。

    protected function changeHeadFile(string $data, string $short_file_name, string $autoload_file): string
替换 `@DUCKPHP_HEADFILE` 为相对目录的 `require_once` 语句。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\Console](Core-Console.md)
