# DuckPhp\Core\Console

命令行控制台组件。

## 简介

`Console` 组件提供了命令行程序的入口。它解析 `$_SERVER['argv']` 参数，根据 `cli_command_group` 配置分发到对应的命令类和方法执行。DuckPHP 的命令行工具就是基于此组件实现的。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `cli_command_group` | `[]` | 命令分组配置。键为命令命名空间，值为命令类数组等配置。 |
| `cli_command_default` | `'help'` | 默认命令。当没有指定命令时执行。 |
| `cli_readlines_logfile` | `''` | `readLines()` 输入日志文件路径。 |

## 命令分组配置

`cli_command_group` 格式如下：

```php
class App extends DuckPhp
{
    public $options = [
        'cli_command_group' => [
            '' => [
                'phase' => \App\System\App::class,
                'classes' => [\App\Command\DefaultCommand::class],
                'method_prefix' => 'command_',
            ],
            'app' => [
                'phase' => \App\System\App::class,
                'classes' => [\App\Command\AppCommand::class],
                'method_prefix' => 'command_',
            ],
        ],
    ];
}
```

| 键 | 说明 |
|---|---|
| `phase` | 命令执行时切换到的相位应用类。 |
| `classes` | 命令类数组。命令会按数组顺序查找，后面的优先。 |
| `method_prefix` | 命令方法前缀。例如 `command_` 表示命令 `install` 对应方法 `command_install`。 |

## 使用方式

### 命令行入口

```php
// cli.php
require __DIR__ . '/vendor/autoload.php';

use DuckPhp\Core\Console;

Console::DoRun();
```

### 执行命令

```bash
php cli.php install
php cli.php app:install --force
```

### 定义命令类

```php
namespace App\Command;

class DefaultCommand
{
    public function command_install($name = 'default')
    {
        echo "Installing {$name}...\n";
    }
}
```

## 命令参数解析

Console 使用自定义的 CLI 参数解析器：

- 第一个参数是命令名，支持 `namespace:command` 格式
- `--option` 是选项，会自动转为下划线键名
- `--option=value` 设置选项值
- 跟在命令名后面的普通参数是位置参数

示例：

```bash
php cli.php user:create --role=admin john 123
```

解析结果大致为：

```php
[
    '--' => ['user:create', 'john', '123'],
    'role' => 'admin',
]
```

## 交互式输入

`Console` 提供了 `readLines()` 方法用于命令行交互输入：

```php
use DuckPhp\Core\Console;

$options = ['name' => 'admin', 'password' => ''];
$desc = "Input name {name}\nInput password {password}";
$ret = Console::_()->readLines($options, $desc);
```

可以通过 `readLinesFill()` 和 `readLinesCleanFill()` 实现测试模式下的模拟输入。

## 注意事项

1. 命令方法名中的 `-` 会自动替换为 `_`。例如命令 `my-install` 会查找 `command_my_install`。
2. 命令类中可以使用可变单例 `::_()`，也可以作为普通类实例化。
3. 命令执行前会切换到配置的 `phase`，确保命令在正确的应用上下文中运行。
4. 如果命令找不到，会抛出 `ReflectionException`。

## 全部选项

```php
public $options = [
    'cli_command_group' => [],
    'cli_command_default' => 'help',
    'cli_readlines_logfile' => '',
];
```

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化命令行组件

    public function getCliParameters()
获取解析后的 CLI 参数

    public function getArgs()
获取位置参数数组

    public function app()
返回当前上下文应用对象

    public function regCommandClass($command_namespace, $phase, $classes, $method_prefix = 'command_')
注册一个命令分组

    public static function DoRun($path_info = '')
静态入口，执行命令行运行

    public function run()
解析参数并执行命令

    public function readLinesFill($data)
填充模拟输入数据

    public function readLinesCleanFill()
清空模拟输入数据

    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
交互式读取多行输入

    public function getCallback($group, $cmd_method)
根据命令分组和方法名查找可调用的类和方法

    public function callObject($class, $method, $args, $input)
调用命令对象方法，自动映射参数

### 受保护方法

    protected function parseCliArgs(array $argv): array
解析 `$_SERVER['argv']` 参数

    protected function getObject(string $class): object
获取命令对象实例，优先使用 `::_()` 可变单例

## 相关链接

- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
