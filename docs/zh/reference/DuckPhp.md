# DuckPhp\DuckPhp

DuckPHP 标准应用入口类。

## 简介

`DuckPhp` 是 `DuckPhp\Core\App` 的直接子类，提供了面向实际项目的标准应用入口。它在 `App` 的基础上预置了一组常用扩展（`ext`），并默认初始化了数据库、Redis、管理员、用户等组件。

标准应用通常直接继承 `DuckPhp\DuckPhp`，而不是继承 `DuckPhp\Core\App`。

## 选项

### 通用选项（common_options）

| 选项 | 默认值 | 说明 |
|---|---|---|
| `ext_options_file_enable` | `true` | 是否加载扩展选项文件。 |
| `ext_options_file` | `'config/DuckPhpApps.config.php'` | 扩展选项文件路径。 |
| `session_prefix` | `null` | Session 前缀。 |
| `table_prefix` | `null` | 数据库表前缀。 |
| `path_info_compact_enable` | `false` | 是否启用 PATH_INFO 兼容模式。 |
| `class_admin` | `''` | 管理员服务类。 |
| `class_user` | `''` | 用户服务类。 |
| `database_driver` | `''` | 数据库驱动。 |
| `cli_command_with_app` | `true` | 是否将当前应用类注册为 CLI 命令类。 |
| `cli_command_with_common` | `true` | 是否注册公共命令类。 |
| `cli_command_with_fast_installer` | `true` | 是否注册快速安装器命令类。 |
| `allow_require_ext_app` | `true` | 是否允许 require 扩展应用。 |
| `lang_default` | `null` | 默认语言。 |
| `lang_final` | `null` | 最终语言。 |

### 默认扩展（ext）

| 扩展类 | 默认启用 | 说明 |
|---|---|---|
| `DuckPhp\Component\Lang` | `true` | 多语言组件。 |
| `DuckPhp\Component\RouteHookCheckStatus` | `true` | 路由状态检查钩子。 |
| `DuckPhp\Component\RouteHookRewrite` | `true` | URL 重写路由钩子。 |
| `DuckPhp\Component\RouteHookRouteMap` | `true` | 路由映射钩子。 |
| `DuckPhp\Component\RouteHookResource` | `true` | 静态资源路由钩子。 |
| `DuckPhp\Component\RouteHookPathInfoCompat` | `false` | PATH_INFO 兼容路由钩子。 |

### 继承自 App 的选项

`DuckPhp` 同时继承了 `App` 的 `core_options` 和 `KernelTrait` 的 `kernel_options`，例如 `path`、`is_debug`、`namespace`、`error_404`、`error_500` 等。

## 使用方式

### 标准应用入口

```php
use DuckPhp\DuckPhp;

class MyApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'is_debug' => true,
        'namespace' => 'MyApp',
    ];
}

MyApp::RunQuickly();
```

### 使用多语言

```php
class MyApp extends DuckPhp
{
    public $options = [
        'lang_default' => 'zh_CN',
    ];
}

// 在控制器或业务层中使用
$text = MyApp::Lang('hello');
```

### 配置数据库

```php
class MyApp extends DuckPhp
{
    public $options = [
        'database_driver' => 'mysql',
        'database' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4',
            'username' => 'root',
            'password' => 'password',
        ],
    ];
}
```

### 配置子应用

```php
class MyApp extends DuckPhp
{
    public $options = [
        'app' => [
            \ApiApp\System\ApiApp::class => [
                'path' => __DIR__ . '/../api',
                'local_database' => true,
            ],
        ],
    ];
}
```

## 配置示例

### 完整基础配置

```php
class MyApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'is_debug' => true,
        'namespace' => 'MyApp',
        'error_404' => 'error-404',
        'error_500' => 'error-500',
        'lang_default' => 'zh_CN',
        'database_driver' => 'mysql',
        'database' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=mydb;charset=utf8mb4',
            'username' => 'root',
            'password' => 'secret',
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
    ];
}
```

### 关闭默认扩展

```php
class MyApp extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Component\RouteHookResource::class => false,
        ],
    ];
}
```

### 启用 PATH_INFO 兼容模式

```php
class MyApp extends DuckPhp
{
    public $options = [
        'path_info_compact_enable' => true,
    ];
}
```

## 注意事项

1. `DuckPhp` 的 `prepareComponents()` 会在父类准备之后加载 `ext_options_file`，并注册 CLI 命令类。
2. `initComponents()` 会在父类初始化之后，额外初始化 `DbManager`、`RedisManager`、`GlobalAdmin`、`GlobalUser`。
3. 只有根应用会直接初始化 `DbManager` 和 `RedisManager`；子应用如果配置了不同的数据库驱动或 `local_database` / `local_redis`，会创建局部实例。
4. `class_admin` 和 `class_user` 用于指定 `GlobalAdmin` 和 `GlobalUser` 绑定的服务类。
5. `lang()` 方法被重写为直接委托给 `Lang` 组件，因此支持多语言文件和自动语言检测。
6. 默认扩展中 `RouteHookPathInfoCompat` 被注释为未启用，需要显式设置 `path_info_compact_enable => true` 才会加载。

## 全部选项

```php
protected $common_options = [
    'ext_options_file_enable' => true,
    'ext_options_file' => 'config/DuckPhpApps.config.php',
    'ext' => [
        \DuckPhp\Component\Lang::class => true,
        \DuckPhp\Component\RouteHookCheckStatus::class => true,
        \DuckPhp\Component\RouteHookRewrite::class => true,
        \DuckPhp\Component\RouteHookRouteMap::class => true,
        \DuckPhp\Component\RouteHookResource::class => true,
    ],
    'session_prefix' => null,
    'table_prefix' => null,
    'path_info_compact_enable' => false,
    'class_admin' => '',
    'class_user' => '',
    'database_driver' => '',
    'cli_command_with_app' => true,
    'cli_command_with_common' => true,
    'cli_command_with_fast_installer' => true,
    'allow_require_ext_app' => true,
    'lang_default' => null,
    'lang_final' => null,
];

protected $core_options = [
    'path_runtime' => 'runtime',
    'alias' => null,
    'default_exception_do_log' => true,
    'close_resource_at_output' => false,
    'html_handler' => null,
    'lang_handler' => null,
    'error_404' => null,
    'error_500' => null,
];

protected $kernel_options = [
    'path' => null,
    'override_class' => null,
    'override_class_from' => null,
    'cli_enable' => true,
    'is_debug' => false,
    'ext' => [],
    'app' => [],
    'skip_404' => false,
    'skip_exception_check' => false,
    'on_init' => null,
    'namespace' => null,
    'setting_file' => 'config/DuckPhpSettings.config.php',
    'setting_file_ignore_exists' => true,
    'setting_file_enable' => true,
    'use_env_file' => false,
    'exception_reporter' => null,
    'exception_for_project' => null,
    'options_file' => 'config/DuckPhpOptions.config.php',
    'options_file_enable' => false,
    'path_installed_options' => 'config',
    'installed_options_file' => 'DuckPhpInstalled.config.php',
    'installed_options_enable' => false,
    'cli_command_classes' => [],
    'cli_command_prefix' => null,
    'cli_command_method_prefix' => 'command_',
];
```

## 方法列表

### 公共方法

    public function lang($str, $args = [])
委托给 `Lang` 组件进行翻译和参数替换

### 受保护方法

    protected function prepareComponents()
在父类准备之后加载扩展选项文件并注册 CLI 命令类

    protected function initComponents(array $options, object $context = null)
在父类初始化之后初始化 `DbManager`、`RedisManager`、`GlobalAdmin`、`GlobalUser` 等组件

    protected function isLocalDatabase()
判断子应用是否需要独立的 `DbManager` 实例

    protected function isLocalRedis()
判断子应用是否需要独立的 `RedisManager` 实例

## 相关链接

- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Component\Lang](Component-Lang.md)
- [DuckPhp\Component\DbManager](Component-DbManager.md)
- [DuckPhp\Component\RedisManager](Component-RedisManager.md)
- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)
- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)
- [DuckPhp\Component\RouteHookCheckStatus](Component-RouteHookCheckStatus.md)
- [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md)
- [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
- [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md)
