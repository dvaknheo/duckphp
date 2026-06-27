# 命令行工具

## CLI 入口

### 标准入口

```bash
php cli.php {command} [参数]
```

`cli.php` 文件位于项目根目录（参见 `template/cli.php`）。

### 使用框架 CLI 入口

```bash
php vendor/bin/duckphp run
php vendor/bin/duckphp new
php vendor/bin/duckphp help
```

## 内置命令

### `run` — 启动开发服务器

```bash
php vendor/bin/duckphp run
# 默认端口 8080，可通过选项修改
```

### `new` — 创建新项目

```bash
php vendor/bin/duckphp new
# 交互式引导创建项目
```

### `help` — 查看帮助

```bash
php vendor/bin/duckphp help
# 显示所有可用命令
```

## 注册自定义命令

### 在 App 中定义命令方法

```php
namespace MyProject\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    // 方法前缀为 command_
    public function command_hello($name = 'World')
    {
        echo "Hello, $name!\n";
    }
    
    public function command_cache_clear()
    {
        // 清除缓存逻辑
        echo "Cache cleared.\n";
    }
}
```

### 在其他类中注册命令

```php
namespace MyProject\Controller;

class Commands
{
    public function command_import($file)
    {
        echo "Importing $file...\n";
    }
    
    public function command_export($format = 'json')
    {
        echo "Exporting as $format...\n";
    }
}
```

然后在 `App` 中注册：

```php
class App extends DuckPhp
{
    public $options = [
        'cli_command_classes' => [
            \MyProject\Controller\Commands::class,
        ],
    ];
}
```

## CLI 命令参数解析

```bash
# 基本命令
php cli.php hello

# 带参数
php cli.php hello --name=Duck

# 带位置参数
php cli.php import file.csv

# 切换命名空间
php cli.php mynamespace:command --arg=value
```

CLI 参数自动解析为方法的命名参数：

```php
public function command_import($file, $format = 'csv')
{
    // php cli.php import data.csv --format=xml
    // $file = 'data.csv', $format = 'xml'
}
```

## 快速安装器

框架提供 `FastInstaller` 用于快速搭建数据库和 Redis：

```bash
php cli.php install
# 交互式安装向导
```
