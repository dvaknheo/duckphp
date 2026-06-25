# 安装与快速开始

## 环境要求

- PHP >= 7.4.0
- Composer（推荐）
- PDO 扩展（如需数据库）

## 通过 Composer 安装

```bash
composer require dvaknheo/duckphp
```

## 最小运行示例

DuckPHP 可以不需要任何第三方 Composer 包即可运行。以下是一个最简的入口文件：

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
php bin/duckphp run    # 或使用框架命令行
```

访问 `http://localhost:8080/`，看到 "Hello DuckPHP!"。

## 两种运行方式

### 1. RunQuickly（推荐）

```php
MyApp::RunQuickly($options);
```

内部完成 `(new MyApp())->init($options)->run()`。

### 2. 分步运行

```php
$app = MyApp::_(new MyApp());
$app->init($options);
$app->run();
```

## 搭建标准项目结构

推荐使用 `template/` 目录下的脚手架模板。项目根目录结构：

```
project/
├── composer.json
├── config/
│   └── DuckPhpSettings.config.php    # 全局设置
├── public/
│   └── index.php                     # Web 入口
├── src/
│   ├── Controller/                   # 控制器层
│   │   ├── Base.php
│   │   ├── Helper.php
│   │   └── MainController.php
│   ├── Business/                     # 业务层
│   │   ├── Base.php
│   │   └── Helper.php
│   ├── Model/                        # 模型层
│   │   ├── Base.php
│   │   └── Helper.php
│   └── System/
│       └── App.php                   # 应用核心配置
├── view/                             # 视图目录
├── runtime/                          # 运行时目录（日志等）
├── cli.php                           # CLI 入口
└── vendor/
```

使用 Composer 脚本快速创建项目（安装后可用）：

```bash
php vendor/bin/duckphp new
```
