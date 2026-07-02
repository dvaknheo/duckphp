# DuckPhp\DuckPhpAllInOne

DuckPHP 快速演示/单文件应用入口类。

## 简介

`DuckPhpAllInOne` 继承自 `DuckPhp\DuckPhp`，是一个用于快速演示、原型验证或单文件应用的特殊入口类。它集成了 `ModelHelperTrait`、`BusinessHelperTrait`、`ControllerHelperTrait` 和 `AppHelperTrait`，将 Model、Business、Controller、App 四层 helper 方法合并到一个类中，方便在最小项目里直接编写控制器和视图。

该类内置了默认页面（`action_index`）和默认视图（`view_head`、`view_index`、`view_foot`），不需要额外配置即可运行并显示一个可工作的主页。

## 选项

`DuckPhpAllInOne` 继承自 `DuckPhp\DuckPhp`，因此可以使用 `DuckPhp` 的全部选项。除此之外，`embedMe()` 方法会动态注入以下选项：

| 选项 | 注入值 | 说明 |
|---|---|---|
| `namespace_controller` | `\"{当前命名空间}\"` | 将当前类作为控制器命名空间。 |
| `controller_welcome_class` | `当前短类名` | 默认欢迎控制器为当前类本身。 |
| `controller_class_postfix` | `''` | 控制器类名无后缀。 |
| `path_info_compact_enable` | `true` | 启用 PATH_INFO 兼容模式。 |
| `ext[CallableView]` | `true` | 启用 CallableView 扩展。 |
| `callable_view_class` | `static::class` | 将当前类作为 CallableView 的视图类。 |
| `callable_view_prefix` | `'view_'` | 视图方法前缀为 `view_`。 |

## 使用方式

### 单文件入口

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;

class MyApp extends DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__ . '/',
        'is_debug' => true,
    ];
}

MyApp::RunQuickly();
```

访问对应的 URL，即可看到 `MyApp main page work at ...` 的输出。

### 自定义首页

```php
class MyApp extends DuckPhpAllInOne
{
    public function view_index($data)
    {
        echo 'Hello, DuckPHP AllInOne!';
    }
}
```

### 自定义页面布局

```php
class MyApp extends DuckPhpAllInOne
{
    public function view_head($data)
    {
        echo '<!DOCTYPE html><html><head><title>My App</title></head><body>';
    }

    public function view_foot($data)
    {
        echo '</body></html>';
    }

    public function view_index($data)
    {
        echo '<h1>Welcome</h1>';
    }
}
```

### 使用 helper 方法

```php
class MyApp extends DuckPhpAllInOne
{
    public function action_hello()
    {
        static::Show(get_defined_vars(), 'hello');
    }

    public function view_hello($data)
    {
        echo 'Hello Page';
    }
}
```

## 配置示例

### 最小可用配置

```php
class MyApp extends DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__ . '/',
        'is_debug' => true,
    ];
}

MyApp::RunQuickly();
```

### 关闭调试模式

```php
class MyApp extends DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__ . '/',
        'is_debug' => false,
    ];
}
```

### 添加数据库支持

```php
class MyApp extends DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__ . '/',
        'database_driver' => 'mysql',
        'database' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
            'username' => 'root',
            'password' => 'password',
        ],
    ];

    public function action_dbtest()
    {
        $ret = static::Db('SELECT 1');
        static::Show(get_defined_vars(), 'dbtest');
    }
}
```

## 注意事项

1. `DuckPhpAllInOne` 主要用于快速演示和最小化项目，不适合大型项目。大型项目建议分别使用 Model、Business、Controller 层的 Helper 类。
2. 构造时 `embedMe()` 会先注入默认选项，然后调用父类构造函数。因此可以在子类中覆盖部分注入的选项。
3. 多个 trait 之间存在同名方法，源码中通过 `insteadof` 进行了解决冲突：
   - `BusinessHelperTrait` 的 `Setting`、`Config`、`XpCall`、`FireEvent`、`OnEvent`、`PathOfProject`、`PathOfRuntime` 优先于 `ControllerHelperTrait` 和 `AppHelperTrait`。
   - `ControllerHelperTrait` 的 `header`、`setcookie`、`exit` 优先于 `AppHelperTrait`。
   - `ControllerHelperTrait` 的 `AdminService`、`UserService` 优先于 `BusinessHelperTrait`。
4. `onInited()` 会自动设置视图头尾为 `head` 和 `foot`，即 `setViewHeadFoot('head', 'foot')`。
5. 默认 `action_index()` 调用 `Show(get_defined_vars(), 'index')`，从而渲染 `view_head` + `view_index` + `view_foot`。
6. 视图方法名以 `view_` 开头，例如 `view_head`、`view_index`、`view_foot`，与 `CallableView` 的 `callable_view_prefix` 配置一致。

## 全部选项

```php
// DuckPhpAllInOne 本身未定义新的选项，继承自 DuckPhp 的 common_options。
// 以下列出 DuckPhp 注入并默认使用的 common_options 及动态注入项：

protected $common_options = [
    'ext_options_file_enable' => true,
    'ext_options_file' => 'config/DuckPhpApps.config.php',
    'ext' => [
        \DuckPhp\Component\Lang::class => true,
        \DuckPhp\Component\RouteHookCheckStatus::class => true,
        \DuckPhp\Component\RouteHookRewrite::class => true,
        \DuckPhp\Component\RouteHookRouteMap::class => true,
        \DuckPhp\Component\RouteHookResource::class => true,
        \DuckPhp\Ext\CallableView::class => true, // 由 DuckPhpAllInOne 注入
    ],
    'session_prefix' => null,
    'table_prefix' => null,
    'path_info_compact_enable' => true, // 由 DuckPhpAllInOne 注入
    'class_admin' => '',
    'class_user' => '',
    'database_driver' => '',
    'cli_command_with_app' => true,
    'cli_command_with_common' => true,
    'cli_command_with_fast_installer' => true,
    'allow_require_ext_app' => true,
    'lang_default' => null,
    'lang_final' => null,
    'namespace_controller' => '{当前命名空间}', // 由 DuckPhpAllInOne 注入
    'controller_welcome_class' => '{当前短类名}', // 由 DuckPhpAllInOne 注入
    'controller_class_postfix' => '', // 由 DuckPhpAllInOne 注入
    'callable_view_class' => '{static::class}', // 由 DuckPhpAllInOne 注入
    'callable_view_prefix' => 'view_', // 由 DuckPhpAllInOne 注入
];
```

## 方法列表

### 公共方法

    public function __construct()
构造实例，先调用 `embedMe()` 注入默认配置，再调用父类构造函数

    public function onInited()
初始化完成后设置视图头尾为 `head` 和 `foot`

    public function action_index()
默认首页动作，渲染 `index` 视图

    public function view_head($data)
默认 HTML 头部视图

    public function view_index($data)
默认首页内容视图，输出当前类名和时间

    public function view_foot($data)
默认 HTML 底部视图

### 受保护方法

    protected function embedMe()
将当前类嵌入为控制器和 CallableView 视图类，并启用 PATH_INFO 兼容模式

## 相关链接

- [DuckPhp\DuckPhp](DuckPhp.md)
- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Helper\ModelHelperTrait](Helper-ModelHelperTrait.md)
- [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md)
- [DuckPhp\Helper\ControllerHelperTrait](Helper-ControllerHelperTrait.md)
- [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md)
- [DuckPhp\Ext\CallableView](Ext-CallableView.md)
