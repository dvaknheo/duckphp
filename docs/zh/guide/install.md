# 2 安装与最小示例

> 目标：装好、跑起一个页面、知道每个文件为什么存在。预计 12 分钟。
> 本章示例取自仓库里的真实文件：脚手架 `skeleton/`、示例应用 `tests/data_for_tests/ZAllDemo`、单文件示例 `demo/public/helloworld.php`。

## 环境要求

| 项 | 要求 |
|---|---|
| PHP | **>= 7.4**（框架自身兼容到 8.4） |
| 扩展 | `pdo`（用数据库时）、`redis`（用 Redis 时）、其它按需 |
| Composer | 推荐；框架不强制（见下面的「不用 Composer」） |
| Web 根 | 只能暴露 `public/` 目录（第 7 章） |

## 路线 A：用脚手架建项目（推荐）

```bash
composer require dvaknheo/duckphp
php vendor/bin/duckphp new        # 交互式：问命名空间，然后把 skeleton/ 拷到当前目录
php vendor/bin/duckphp show       # 看生成结果与当前配置
```

`duckphp` 这个命令只有三个子命令：`new` / `show` / `help`（它由 `DuckPhpInstaller` 提供）。`new` 做的事情很朴素：**把 `skeleton/` 目录整个拷成你的工程**，所以生成的结构与 `skeleton/` 一致。

## 路线 B：手写最小工程（3 个文件）

不想用脚手架就自己建这三个文件：

**① `public/index.php` —— Web 入口（只有几行）**

```php
<?php declare(strict_types=1);
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}
\MyProj\System\App::RunQuickly([
    // 想临时覆盖选项就写在这里，例如 'is_debug' => true
]);
```

> 这就是 `tests/data_for_tests/ZAllDemo/public/index.php` 的写法（有测试兜底）。「找两处 vendor」是为了兼容「框架装在项目里」和「项目在框架仓库里」两种布局。

**② `src/System/App.php` —— 应用类（整个应用的配置中枢）**

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',      // 项目根：view/ config/ runtime/ 都相对它
        'error_404' => '_sys/error_404',    // 404 视图（相对 view/）
        'error_500' => '_sys/error_500',
    ];
}
```

**③ `src/Controller/MainController.php` —— 第一个控制器**

```php
<?php declare(strict_types=1);
namespace MyProj\Controller;

class MainController
{
    public function index()
    {
        echo 'Hello DuckPHP';
    }
}
```

> ⚠️ **方法名就是 URL 段**。框架的 `controller_method_prefix` 默认是**空**，所以 `/` 与 `/index` 都会调用 `index()`；如果你写成 `action_index()`，那只有 `/action_index` 能访问到它（老文档里到处都是 `action_`，那是早期默认值）。

**④ `view/main/index.php`（可选）** —— 想用视图就用 `Helper::Show()`：

```php
<?php // view/main/index.php ?>
<h1><?= __h($title) ?></h1>
```

```php
// 控制器里
Helper::Show(['title' => 'Hello DuckPHP'], 'main/index');
```

## 跑起来

```bash
# 开发用内置服务器（最简单）
php -S 127.0.0.1:8080 -t public

# 或者用框架自带的常驻 HTTP 服务（第 37 章），同样是内置服务器
php bin/cli.php run                 # 默认 127.0.0.1:8080，文档根 public/
php bin/cli.php run --port=9000     # 换端口
```

> 这两种启动方式都只把「不像文件」的路径交给 `index.php`：`/`、`/Note/index` 能跑通；**带后缀的 URL**（如框架代发的 `/res/main.css`）需要额外一个 router 脚本 —— 见[第 7 章 §二](deployment.md)。

浏览器打开 `http://127.0.0.1:8080/`，看到 `Hello DuckPHP` 就成了。

命令行入口 `bin/cli.php` 与 Web 入口共用同一套代码：

```php
<?php declare(strict_types=1);
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../vendor/autoload.php'] as $file) {
    if (file_exists($file)) { require $file; break; }
}
\MyProj\System\App::RunQuickly(['cli_enable' => true]);
```

```bash
php bin/cli.php help        # 列出所有可用命令（第 23 章）
php bin/cli.php version
```

## 不用 Composer 也能跑

框架自带极简自动加载器，仓库入口 `bin/duckphp` 就是这么做的：

```php
require __DIR__ . '/src/Core/AutoLoader.php';
spl_autoload_register([\DuckPhp\Core\AutoLoader::class, 'DuckPhpSystemAutoLoader']);
```

自己的项目要这么用时，再给工程命名空间加一条映射即可（第 36 章）。

## 目录一览（细节见第 3 章）

```
project/
├── public/index.php      ← 唯一对外暴露的入口
├── bin/cli.php           ← 命令行入口
├── config/               ← 设置文件（敏感信息放这里）
├── src/{System,Controller,Business,Model}/
├── view/                 ← 视图（含 _sys/ 错误页）
├── runtime/              ← 日志等可写目录
└── vendor/
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 访问 `/` 是 404 | 控制器方法写成了 `action_index()` 而前缀是空 | 方法名去掉 `action_`；或显式设 `'controller_method_prefix' => 'action_'` |
| 全站 500，提示类找不到 | 命名空间与目录不一致 | `MyProj\Controller\MainController` 必须在 `src/Controller/MainController.php` 且 `path` 指向项目根 |
| `Failed opening required vendor/autoload.php` | 入口的相对路径写错 | 照抄上面入口里的「两处 vendor」写法 |
| `php -S` 下除了首页都 404 | 忘了 `-t public`，或访问了 `/index.php/foo` 之外的路径 | 用 `-t public`；短路径由框架接管（第 7 章讲服务器配置） |
| CLI 里跑到 Web 分支 | 入口没传 `cli_enable`，而 `cli_enable` 被关掉了 | CLI 入口传 `['cli_enable' => true]` |

## 下一步

- [第 3 章 目录结构与编码规则](project-structure.md)：把项目摆成「框架期望的样子」。
- [第 4 章 第一个页面](quickstart.md)：路由 → 控制器 → 业务 → 模型 → 视图 走通一遍。
- 参考手册：[DuckPhp\DuckPhp](../reference/DuckPhp.md)、[DuckPhp\Ext\DuckPhpInstaller](../reference/Ext-DuckPhpInstaller.md)
