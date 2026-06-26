# 安装与快速开始

## 环境要求

- PHP >= 7.4.0
- Composer（推荐）
- PDO 扩展（如需数据库）

## 通过 Composer 安装

```bash
composer require dvaknheo/duckphp
```

## 最小运行示例（Composer 安装后）

如果你已通过 Composer 安装，以下是一个最简的入口文件：

```php
<?php
// public/index.php
require_once __DIR__.'/../vendor/autoload.php';

class MyApp extends \DuckPhp\DuckPhp
{
    // 使用默认配置
}

// 一行启动
MyApp::RunQuickly([
    'is_debug' => true,
]);
```

创建第一个控制器：

```php
<?php
// src/Controller/MainController.php
namespace MyApp\Controller;

class MainController
{
    public function action_index()
    {
        echo "Hello DuckPHP!";
    }
}
```

启动内置服务器：

```bash
php -S localhost:8080 -t public
php vendor/bin/duckphp run    # 或使用框架命令行
```

访问 `http://localhost:8080/`，看到 "Hello DuckPHP!"。

## 下一步

- 查看 [项目结构与编码规则](project-structure.md) 了解标准项目结构和编码规范
- 查看 [快速入门](quickstart.md) 了解完整示例

