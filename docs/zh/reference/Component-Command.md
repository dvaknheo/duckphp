# DuckPhp\Component\Command

`DuckPhp\Component\Command` 是 DuckPHP 框架内置的 CLI 命令集合。它提供 `version`、`help`、`run`、`fetch`、`call` 和 `debug` 等常用命令，通过 `DuckPhp\Core\Console` 组件注册到 CLI 命令组中。

---

## 简介

`Command` 组件中的每个公共 `command_*` 方法都对应一个 CLI 命令。`DuckPhp\DuckPhp` 默认在 `prepareComponents()` 阶段将 `Command` 类加入 `cli_command_classes`，因此这些命令无需额外配置即可使用。

该组件本身没有公共选项，其行为受 `DuckPhp\Core\Console` 的选项控制。

---

## 选项

`Command` 组件本身没有独立选项。相关行为由 `DuckPhp\Core\Console` 的以下选项决定：

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `cli_command_default` | `'help'` | `DuckPhp\Core\Console` | 默认 CLI 命令。 |
| `cli_command_group` | `[]` | `DuckPhp\Core\Console` | CLI 命令分组配置。 |
| `cli_command_classes` | `[]` | `DuckPhp\Core\Console` | 注册的 CLI 命令类列表。 |
| `cli_readlines_logfile` | `''` | `DuckPhp\Core\Console` | CLI 读取日志文件。 |

---

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
php cli.php run --host=127.0.0.1
```

> 注意：使用 `run` 命令需要 `DuckPhp\HttpServer\HttpServer` 可用。

### fetch

在命令行中模拟 HTTP 请求，调用当前应用处理一个 URI。

```bash
php cli.php fetch /article/list
php cli.php fetch /api/login --post=1
```

### call

直接调用某个类的对象方法。格式为 `namespace/class@method`，参数跟在后面。

```bash
php cli.php call MyApp/Business/UserBusiness@getUser 1
```

> 被调用的类必须实现 `::_()` 可变单例访问模式。

### debug

切换调试模式。需要启用 `data_file_enable`、`data_file_bump_allowed`，并且 `data_file_bump_keys` 包含 `'is_debug'`。

```bash
php cli.php debug
php cli.php debug --off
```

---

## 添加自定义命令

继承 `Command` 类或创建自己的命令类，方法名以 `command_` 开头即可。

```php
namespace MyApp\Controller;

use DuckPhp\Component\Command;

class MyCommand extends Command
{
    /**
     * my custom command
     */
    public function command_hello($name = 'World'): void
    {
        echo "Hello, $name\n";
    }
}
```

然后在应用选项中注册：

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'cli_command_classes' => [
            \DuckPhp\Component\Command::class,
            \MyApp\Controller\MyCommand::class,
        ],
    ];
}
```

执行：

```bash
php cli.php hello
php cli.php hello DuckPhp
```

---

## 注意事项

1. 命令方法名必须以 `command_` 前缀开头。
2. 命令帮助信息来自方法上方的 PHPDoc 注释第一行。
3. `run` 和 `fetch` 命令会临时切换应用的 `cli_enable` 状态。
4. `debug` 命令依赖 `DuckPhp\Component\ExtOptionsLoader` 保存数据文件。

---

## 方法列表

### 公共命令方法

| 方法 | 说明 |
|---|---|
| `command_version()` | 显示应用版本。 |
| `command_help()` | 显示帮助信息。 |
| `command_run()` | 启动内置开发服务器。 |
| `command_fetch($uri = '', $post = false)` | 模拟 HTTP 请求处理指定 URI。 |
| `command_call()` | 调用指定类的对象方法。 |
| `command_debug(bool $off = false)` | 切换调试模式。 |

### 受保护辅助方法

| 方法 | 说明 |
|---|---|
| `getCommandListInfo()` | 生成命令列表信息。 |
| `getCommandsByClasses(array $classes, string $method_prefix, string $phase)` | 批量获取多个类的命令。 |
| `getCommandsByClass(string $class, string $method_prefix, string $phase)` | 获取单个类的命令。 |
| `getCommandsOfThis($method_prefix, $phase)` | 获取当前类自身的命令。 |
| `getCommandsByClassReflection(\ReflectionClass $ref, string $method_prefix)` | 通过反射提取命令名和描述。 |

---

## 相关链接

- [DuckPhp\Core\Console](Core-Console.md)
- [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md)
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md)
