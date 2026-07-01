# DuckPhp\Component\Command

框架内置 CLI 命令集合。

## 简介

`Command` 组件提供 DuckPHP 框架的内置 CLI 命令，包括 `version`、`help`、`run`、`fetch`、`call` 和 `debug`。这些命令通过 `DuckPhp\Core\Console` 组件注册到 CLI 命令组中，默认属于 `cli_command_with_common` 命令组。

`DuckPhp\DuckPhp` 默认在 `prepareComponents()` 阶段将 `Command` 类加入 `cli_command_classes`，因此这些命令无需额外配置即可使用。

## 选项

`Command` 组件本身没有公共选项。其行为受 `Console` 组件的以下选项影响：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `cli_command_default` | `'help'` | 默认 CLI 命令 |
| `cli_command_group` | `[]` | CLI 命令分组配置 |
| `cli_readlines_logfile` | `''` | CLI 读取日志文件 |

这些选项属于 `DuckPhp\Core\Console`，在 `DuckPhp\Core\App` 的选项中配置。

## 内置命令

### version

显示当前应用版本。

```bash
php cli.php version
```

输出示例：

```
(MyApp\System\App)1.3.4
```

### help

显示帮助信息，包括所有可用命令列表。

```bash
php cli.php help
```

### run

启动内置开发服务器。

```bash
php cli.php run
php cli.php run --port=8080
php cli.php run --http_server=MyApp\HttpServer\MyHttpServer
```

该命令通过 `HttpServer` 组件运行，默认使用 `DuckPhp\HttpServer\HttpServer`。

### fetch

模拟一次 HTTP 请求。

```bash
php cli.php fetch /user/profile
php cli.php fetch /user/login --post=username=foo
```

该命令会设置 `$_SERVER['REQUEST_URI']`、`$_SERVER['PATH_INFO']` 和 `$_SERVER['HTTP_METHOD']`，然后调用 `App::Current()->run()`。

### call

调用指定类的某个方法。

```bash
php cli.php call MyApp/Business/UserBusiness@getUser --id=1
```

类名中的 `/` 会自动转换为 `\`，参数通过 `Console::callObject()` 解析并传入。

### debug

切换调试模式。

```bash
php cli.php debug          # 开启调试模式
php cli.php debug --off    # 关闭调试模式
```

该命令会修改 `config/DuckPhpApps.config.php` 中的 `is_debug` 选项。

## 使用方式

### 通过框架 CLI 入口

```bash
php vendor/bin/duckphp help
php vendor/bin/duckphp run
php vendor/bin/duckphp version
```

### 通过项目 cli.php

```bash
php cli.php help
php cli.php run
php cli.php fetch /user/profile
```

## 自定义命令

除了 `Command` 组件提供的内置命令，还可以在 `App` 类或其他类中定义 `command_*` 方法。

```php
class App extends DuckPhp
{
    public function command_hello($name = 'World')
    {
        echo "Hello, $name!\n";
    }
}
```

执行：

```bash
php cli.php hello --name=Duck
```

更多自定义命令的内容参见 [Core-Console](Core-Console.md)。

## 注意事项

1. 命令方法的文档注释第一行会作为命令描述显示在 `help` 中。
2. `command_call` 的类名分隔符支持 `/` 和 `\`，会自动转换。
3. `command_fetch` 目前主要用于模拟 GET/POST 请求，内部直接修改 `$_SERVER`。
4. `command_debug` 会持久化修改到 `config/DuckPhpApps.config.php`。

## 方法列表

### 公共方法

    public function command_version()
显示当前应用版本。

    public function command_help()
显示帮助信息和命令列表。

    public function command_run()
启动内置开发服务器。支持 `--port` 和 `--http_server` 参数。

    public function command_fetch($uri = '', $post = false)
模拟 HTTP 请求。支持 `--post` 参数指定 POST 数据。

    public function command_call()
调用指定类的方法。参数格式为 `namespace/class@method`。

    public function command_debug($off = false)
切换调试模式。`--off` 关闭调试。

    public function getCommandsOfThis($method_prefix, $phase)
获取当前类注册的命令列表，用于 `help` 展示。当 `$phase` 不是当前相位时，会隐藏 `new`、`run`、`help` 命令。

### 受保护方法

    protected function getCommandListInfo()
汇总所有命令组的命令列表，生成 help 输出文本。

    protected function getCommandsByClasses($classes, $method_prefix, $phase)
从多个类中提取命令列表。

    protected function getCommandsByClass($class, $method_prefix, $phase)
从单个类中提取命令列表。如果类有 `getCommandsOfThis` 方法，则调用它。

    protected function getCommandsByClassReflection($ref, $method_prefix)
通过反射提取类中符合 `command_` 前缀的方法及其文档注释第一行。

## 相关链接

- [DuckPhp\Core\Console](Core-Console.md)
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md)
- [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md)
