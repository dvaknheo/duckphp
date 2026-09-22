# 4-4 无 Composer·单文件·内嵌

> 解决什么问题：手上**没有 Composer**、或只想在别的项目里**塞一两个页面**时，怎么让 DuckPHP 跑起来。
> 前置：[第 1-2 章 安装与最小示例](install.md)、[第 1-7 章 上线最小清单](deployment.md)。预计 15 分钟。
> 示例全部来自 `demo/public/`（`helloworld.php`、`just-route.php`、`traditional.php`）与 `src/DuckPhpAllInOne.php`，可用 `php -S 127.0.0.1:8080 -t demo/public` 起服务后逐个访问。

## 最小示例

`demo/public/helloworld.php`（33 行）演示「框架当库用」的最小姿态：一个控制器类 + 一次 `RunQuickly()`：

```php
class MainController
{
    public function index()
    {
        echo "hello world";
    }
}
$options = [
    'is_debug' => true,
    'namespace_controller' => "\\",   // 控制器在根命名空间，而不是默认的 Controller\
];
\DuckPhp\DuckPhp::RunQuickly($options);
```

访问 `http://127.0.0.1:8080/helloworld.php` 即输出 `hello world`。整个文件没有 `namespace`、没有 `src/` 目录、没有 Composer —— 框架被当成一个**普通的 require 库**来用。

## 机制说明

### 没有 Composer 时，框架怎么找到类

DuckPHP 自带一个 PSR-4 风格的简化自动加载器 [DuckPhp\Core\AutoLoader](../reference/Core-AutoLoader.md)，**不依赖 Composer**。它有两条独立的启动路径：

**路径 A：只加载框架自身**（`DuckPhp\` 前缀 → `src/`）。仓库根的 `autoload.php` 只有两行：

```php
require __DIR__.'/src/Core/AutoLoader.php';
spl_autoload_register([DuckPhp\Core\AutoLoader::class ,'DuckPhpSystemAutoLoader']);
```

`bin/duckphp`（框架自带的脚手架命令）用的是同一种写法。注册后，`DuckPhp\` 开头的类会被 `DuckPhpSystemAutoLoader()` 按 `src/<子命名空间>/<类名>.php` 的路径 `include_once`（`src/Core/AutoLoader.php` 第 222–234 行）。**这一步只解决「框架自己能被找到」**，你的工程类还不行。

**路径 B：让工程类也能被找到**。`AutoLoader::RunQuickly(['path'=>…])` 初始化项目根目录并注册 `spl_autoload_register([AutoLoader::class,'AutoLoad'])`；`AutoLoader::addPsr4('命名空间\\', '目录')` 再补一条「命名空间 → 目录」映射。`demo/cli.php` 与 `demo/public/index.php` 里都有真实写法：

```php
// demo/cli.php（demo/public/index.php 同款）
if (!class_exists(\ProjectNameTemplate\System\App::class)) {
    \DuckPhp\Core\AutoLoader::RunQuickly([
        'path' => __DIR__.'/',
    ]);
    \DuckPhp\Core\AutoLoader::addPsr4("ProjectNameTemplate\\", 'src');
}
\ProjectNameTemplate\System\App::RunQuickly($options);
```

先 `class_exists()` 探测：Composer 在就用 Composer 的 autoload（路径 A 不需要），不在才回落到框架自带的 [AutoLoader](../reference/Core-AutoLoader.md)。这是「无 Composer 内嵌」的**标准探测姿势**。

> 参考手册：[DuckPhp\Core\AutoLoader](../reference/Core-AutoLoader.md) 列出了全部选项（`path` / `namespace` / `path_namespace` / `psr-4` / `autoload_path_namespace_map`）与 `assignPathNamespace()` / `clear()` 等方法。

### `DuckPhpAllInOne`：一个类就是整个应用

[src/DuckPhpAllInOne.php](../../src/DuckPhpAllInOne.php) 里的 [`DuckPhp\DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) 把**应用入口、控制器、视图回调、四组 Helper** 全部塞进一个类：

- 用 `__callStatic` 把**四层 Helper 的并集**嵌进本类：调用本类上不存在的静态方法时，按 System → Controller → Business → Model 找第一个声明它的层 Helper 并转发（源码第 128–139 行）。所以在它的 `action_*` 方法里能直接 [`$this->Db()`](../reference/Db-Db.md)、`$this->Setting()`、`$this->Show()`——注意这些方法在反射层面并不存在（IDE 靠源码里 96 条 `@method` 注释），细节见[第 2-7 章](helper.md)。


- 构造函数里调 `embedMe()`（第 145 行起）注入一组默认选项：

| 选项 | 注入值 | 作用 |
|---|---|---|
| `namespace_controller` | `\` + 本类所在命名空间 | 控制器就是本命名空间下的类 |
| `name` | `'@'` | 相位名取类名 basename |
| `controller_welcome_class` | `static::class` | 欢迎页就是**这个类自己** |
| `controller_class_postfix` | `''` | 类名不再追加 `Controller` 后缀 |
| `controller_method_prefix` | `'action_'` | 只有 `action_*` 方法才是动作 |
| `cli_enable` | `true` | 同时是 CLI 入口 |
| `path_info_compact_enable` | `true` | 无 PATH_INFO 也能跑（[第 2-2 章](routing.md)） |
| `duckphp_all_in_one_wrap_header_foot` | `true` | `_Show()` 时自动包 `view_head` / `view_foot` |

- 视图不走视图文件，而是**类方法**：`viewToCallback()`（第 86–93 行）把视图名里的 `/` 换成 `_`，找 `view_<名字>` 方法；找到就当作可调用视图，找不到才回落到父类的文件视图。`_Show()`（第 94–109 行）按「head → 正文 → foot」顺序调用。所以子类只要写 `view_hello($data)` 就等于定义了 `hello` 视图。
- `onPrepare()`（第 63–71 行）把 `static::class` 登记进 `options['cmd']`，等价于 `cli_command_with_app=true` 的效果：CLI 下 `php <脚本> help` 能看到这个类的命令。

参考手册：[DuckPhp\DuckPhpAllInOne](../reference/DuckPhpAllInOne.md)；测试见 `tests/DuckPhpAllInOneTest.php`（断言 `cmd` 含自身类、`Show([], 'index')` 输出含 `<html>` 与 `main page work at`）。

## 常见写法

**1. 老项目里只加一两个页面** —— `demo/public/helloworld.php` 的姿态：在原有项目的 `public/` 里丢一个 php 文件，`namespace_controller` 指到根命名空间，控制器类就写在同一个文件里。原项目继续用原项目的加载方式，互不影响。

**2. 只要路由，不要别的** —— `demo/public/just-route.php` 的姿态：连 [`DuckPhp`](../reference/DuckPhp.md) 应用类都不用，直接 [`Route::RunQuickly($options)`](../reference/Core-Route.md)：

```php
use DuckPhp\Core\Route;

class MainController
{
    public function index() { echo "Just route test done"; }
    public function i()      { phpinfo(); }
}
$options = ['namespace_controller' => '\\'];
$flag = Route::RunQuickly($options);
if (!$flag) {
    header(404, 'no');
    echo "404!";
}
```

只用到 [DuckPhp\Core\Route](../reference/Core-Route.md) 一个类：路由解析、控制器调用、404 返回 `false` 都归它。适合「只想借用路由」的场景。

**3. 全函数模式 + 视图数据外置** —— `demo/public/traditional.php` 的姿态：动作是 `action_*` 函数而不是类方法，配合 [`Ext\RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md) 扩展；视图也不走 [View](../reference/Core-View.md) 组件，而是用 [`Ext\EmptyView`](../reference/Ext-EmptyView.md) 把数据存下来，由文件末尾的**原生 PHP 模板**自己 `extract()` 渲染：

```php
$options['namespace'] = '\\';
$options['path_info_compact_enable'] = true;
$options['ext'][\DuckPhp\Ext\EmptyView::class] = true;            // _Show 只存数据
$options['ext'][\DuckPhp\Ext\RouteHookFunctionRoute::class] = true; // action_* 函数即动作
$flag = \DuckPhp\DuckPhp::RunQuickly($options);
$xxx = \DuckPhp\Core\View::_()->getViewData();   // 取出数据自己渲染
extract($xxx);
```

这是「塞进一个传统 PHP 页面」的极端形态：框架只负责路由与输入，渲染完全交回给页面自己的 HTML。

**4. 一个类写完整个微型应用** —— 继承 `DuckPhpAllInOne`：

```php
class Tiny extends \DuckPhp\DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__,
        'is_debug' => false,
    ];
    public function action_hello() { $this->_Show(['m' => 'Hi'], 'hello'); }
    public function view_hello($data) { echo 'hello ' . __h($data['m']); }
}
Tiny::RunQuickly([]);
```

访问 `/…/tiny.php/hello` 即调用 `action_hello`，`_Show` 自动包上内置的 `view_head` / `view_foot`（除非把 `duckphp_all_in_one_wrap_header_foot` 关掉）。「登录后视图」（[第 2-9 章](external-auth.md)）靠视图数据 `__logined_enable_view` 打开，在子类里 `Helper::assignViewData('__logined_enable_view', true)` 即可。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `Class 'DuckPhp\...' not found` | 没注册任何 autoload | 有 Composer 就 `require vendor/autoload.php`；没有就 `require <框架>/autoload.php`（或照 `bin/duckphp` 手写两行） |
| 框架能找到，工程类找不到 | 只注册了 `DuckPhpSystemAutoLoader`，它只处理 `DuckPhp\` 前缀 | 再 `AutoLoader::RunQuickly(['path'=>…])` + `addPsr4('工程命名空间\\', 'src')`（见 `demo/cli.php`） |
| `RunQuickly()` 后页面空白、无路由命中 | 控制器命名空间没指对 | 控制器写在根命名空间时给 `'namespace_controller' => "\\"`（`helloworld.php` 那行注释就是干这个的） |
| `DuckPhpAllInOne` 子类的 `action_xxx` 不生效 | 方法名没带 `action_` 前缀 | `embedMe()` 注入了 `'controller_method_prefix' => 'action_'`，方法必须以此开头 |
| `_Show()` 没包头尾 | 关了 `duckphp_all_in_one_wrap_header_foot`，或没定义 `view_head`/`view_foot` | 该选项为真且子类定义了对应 `view_*` 方法才会包；不需要头尾就保持关闭 |
| 在老项目里内嵌后，原项目的类被框架 autoload 抢先加载 | 两边都注册了 autoload，顺序不可控 | 用 `class_exists()` 探测（`demo/cli.php` 的写法），或在内嵌文件里只 `require` 框架 `autoload.php`、不动工程侧加载 |

## 下一步

- [第 4-5 章 常驻进程与内嵌 HTTP](http-server.md)：把这里的单文件再用内置服务器包一层。
- [第 4-6 章 多入口·多域名·多 SAPI](multi-entry.md)：单文件姿态之上，同一套 `src/` 怎么被多个入口复用。
- 参考手册：[DuckPhp\Core\AutoLoader](../reference/Core-AutoLoader.md)、[DuckPhp\DuckPhpAllInOne](../reference/DuckPhpAllInOne.md)、[DuckPhp\Core\Route](../reference/Core-Route.md)
