# DuckPhp\Ext\DuckPhpInstaller

## 简介

`DuckPhpInstaller` 是 `bin/duckphp` 背后的命令行安装器，提供三个 CLI 命令：

- `new`：从框架 `skeleton` 复制一个新工程到指定目录，并把 `ProjectNameTemplate` 命名空间/App 类名替换为你设置的命名空间（`command_new`）；
- `show`：用内置 HttpServer 跑框架的 demo 页面（`command_show` → `runDemo`）；
- `help`：输出帮助文本（`command_help` → `showHelp`）。

工程内直接使用较少，多经 `./vendor/bin/duckphp new/show` 触发。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class DuckPhpInstaller extends DuckPhp\Core\ComponentBase`

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 目标路径（`new` 的落盘目录 / 覆盖）。 |
| `namespace` | `''` | 新工程的命名空间（缺省自动探测/询问）。 |
| `force` | `false` | 目标已存在文件时是否强制覆盖。 |
| `autoloader` | `'vendor/autoload.php'` | 使用的 autoload 路径。 |
| `verbose` | `false` | 是否打印过程信息。 |
| `help` | `false` | 帮助开关。 |

## 使用方式

```text
./vendor/bin/duckphp new --namespace MyProject --path ./myproj --force
./vendor/bin/duckphp new --help
./vendor/bin/duckphp show --port 8080
```

## 注意事项

- `newProject()`：命名空间优先级 = CLI `--namespace` → composer.json `psr-4`（取 `src/` 对应项，经 `getNameSpaceByComposer`）→ 控制台询问（`getNamespaceByConsole`）。
- `dumpDir()`：递归复制 `skeleton`；`src/System/App.php` 会重命名为 `{NamespaceBasename}App.php` 并把 `class App extends` 改为对应类名；文件内容经 `filteText`（含命名空间/宏过滤）处理；`force=false` 且目标有同名文件时中止（`checkFilesExist`）。
- `runDemo()`：以内置模板目录为源，用 `HttpServer::RunQuickly($options)` 起服务；支持 `--port` 与自定义 `--http_server`。

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
父 init 后注册自身命令类前缀（`regCommandClassSingle`）。

    public function command_new(): void
`new` 命令：解析 CLI 参数后 `newProject()`（`--help` 时仅打印帮助）。

    public function command_help()
`help` 命令：打印帮助。

    public function command_show()
`show` 命令：运行 demo 服务器。

    public function showHelp(): void
输出帮助文本。

    public function newProject($options = [])
创建新工程：确定命名空间并复制/改写骨架。

    public function runDemo(): void
以模板为源启动 HttpServer demo。

### 受保护方法

    protected function getNameSpaceByComposer(string $path): string
从 composer.json 的 `psr-4`（`src/`）推断命名空间。

    protected function getNamespaceByConsole(): string
交互式询问命名空间（默认 `Demo`）。

    protected function dumpDir(string $source, string $dest, bool $force = false): void
递归复制目录并逐个改写文件内容。

    protected function getNamespaceBasename()
取命名空间末段（用于 App 类重命名）。

    protected function checkFilesExist()
检查目标是否已有同名文件（非 force 时中止）。

    protected function createDirectories()
在目标创建目录结构。

    protected function filteText()
对单文件内容做宏/命名空间替换过滤。

    protected function filteMacro()
替换模板宏占位。

    protected function filteNamespace()
替换命名空间占位。

    protected function changeHeadFile()
处理头文件模板。

    protected function genProjectName()
生成工程名。

    protected function detectedClass()
探测类信息（用于改写）。

## 相关链接

- [DuckPhp\Core\Console](Core-Console.md) — CLI 参数读取
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) — demo 服务器
