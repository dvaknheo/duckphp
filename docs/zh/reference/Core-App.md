# DuckPhp\Core\App

DuckPHP 核心应用类。

## 简介

`App` 继承自 `ComponentBase` 并使用了 `KernelTrait`，是 DuckPHP 框架的核心应用类。它在 `KernelTrait` 提供的生命周期之上，进一步初始化了 `Logger`、`SuperGlobal`、`SystemWrapper`、`View` 等核心组件，并提供了默认的 404、异常和开发错误处理行为。

`DuckPhp\DuckPhp` 是 `App` 的直接子类，标准应用通常继承 `DuckPhp\DuckPhp` 而不是直接使用 `App`。

## 选项

### 核心选项（core_options）

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_runtime` | `'runtime'` | 运行时目录。 |
| `alias` | `null` | 子应用别名，用于覆盖文件路径解析。 |
| `default_exception_do_log` | `true` | 是否记录默认异常日志。 |
| `close_resource_at_output` | `false` | 是否在输出后关闭资源。 |
| `html_handler` | `null` | 自定义 HTML 处理函数。 |
| `lang_handler` | `null` | 自定义语言处理函数。 |
| `error_404` | `null` | 404 视图文件或回调。 |
| `error_500` | `null` | 500 错误视图文件或回调。 |

### 继承自 KernelTrait 的选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `null` | 项目根路径。 |
| `override_class` | `null` | 覆盖类。 |
| `cli_enable` | `true` | 是否启用 CLI。 |
| `is_debug` | `false` | 调试模式。 |
| `ext` | `[]` | 主扩展列表。 |
| `app` | `[]` | 子应用列表。 |
| `skip_404` | `false` | 是否跳过 404 处理。 |
| `skip_exception_check` | `false` | 是否跳过异常检查。 |
| `on_init` | `null` | 初始化回调。 |
| `namespace` | `null` | 项目命名空间。 |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | Setting 文件路径。 |
| `setting_file_enable` | `true` | 是否启用 Setting 文件。 |
| `use_env_file` | `false` | 是否加载 `.env` 文件。 |
| `exception_reporter` | `null` | 异常报告器。 |
| `options_file` | `'config/DuckPhpOptions.config.php'` | 选项文件路径。 |
| `options_file_enable` | `false` | 是否启用选项文件。 |
| `path_installed_options` | `'config'` | 已安装选项目录。 |
| `installed_options_file` | `'DuckPhpInstalled.config.php'` | 已安装选项文件。 |
| `installed_options_enable` | `false` | 是否启用已安装选项。 |
| `cli_command_classes` | `[]` | CLI 命令类。 |
| `cli_command_prefix` | `null` | CLI 命令前缀。 |
| `cli_command_method_prefix` | `'command_'` | CLI 方法前缀。 |

## 使用方式

### 定义应用

```php
use DuckPhp\Core\App;

class MyApp extends App
{
    public $options = [
        'path' => __DIR__ . '/../',
        'is_debug' => true,
        'namespace' => 'MyApp',
    ];
}

MyApp::RunQuickly();
```

### 获取版本

```php
echo MyApp::_()->version();
```

### 自定义 404 页面

```php
class MyApp extends App
{
    public $options = [
        'error_404' => 'error-404', // 对应 view/error-404.php
    ];
}
```

### 自定义 500 页面

```php
class MyApp extends App
{
    public $options = [
        'error_500' => 'error-500', // 对应 view/error-500.php
        'error_debug' => 'error-debug', // 对应 view/error-debug.php
    ];
}
```

### 检查调试模式

```php
if (MyApp::IsDebug()) {
    // 调试模式
}
```

## 配置示例

### 基础 Web 应用

```php
class MyApp extends App
{
    public $options = [
        'path' => __DIR__ . '/../',
        'is_debug' => true,
        'namespace' => 'MyApp',
        'error_404' => 'error-404',
        'error_500' => 'error-500',
    ];
}
```

### 禁用 Setting 文件

```php
class MyApp extends App
{
    public $options = [
        'setting_file_enable' => false,
    ];
}
```

### 自定义错误回调

```php
class MyApp extends App
{
    public $options = [
        'error_404' => function () {
            echo 'Custom 404 Page';
        },
        'error_500' => function ($ex) {
            echo 'Custom 500: ' . $ex->getMessage();
        },
    ];
}
```

## 注意事项

1. `App` 构造时会将 `kernel_options`、`core_options`、`common_options` 和 `$options` 合并为一个统一的 `$options` 数组，之后不再保留 `kernel_options` 等属性。
2. 核心组件 `Logger`、`SuperGlobal`、`SystemWrapper`、`View` 在 `doInitComponents()` 中初始化。
3. `_On404()` 默认行为：
   - 发送 `HTTP/1.1 404 Not Found` 头。
   - 如果 `error_404` 是回调则执行回调。
   - 否则输出默认 404 信息，调试模式下显示路由错误详情。
4. `_OnDefaultException()` 默认行为：
   - 切换到根应用 Phase。
   - 如果 `default_exception_do_log` 为 `true`，记录异常日志。
   - 发送 `HTTP/1.1 500 Server Error` 头。
   - 如果 `error_500` 是回调则执行回调；否则输出默认 500 页面，调试模式下显示异常详情。
5. `_OnDevErrorHandler()` 默认行为：
   - 仅在调试模式下生效。
   - 输出包含错误类型、文件、行号和错误信息的调试 HTML 块。
6. `lang()` 方法在 `App` 中提供基础参数替换；`DuckPhp\DuckPhp` 会将其委托给 `Lang` 组件。

## 全部选项

```php
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

    public function __construct()
构造应用实例，合并所有选项并设置 `overriding_class`

    public static function _($object = null)
获取或设置应用实例，通过 `PhaseContainer` 管理

    public function version()
返回应用版本字符串，包含类名和版本号

    public function _On404(): void
404 默认处理：设置 404 响应头并渲染 `error_404` 视图或回调

    public function _OnDefaultException($ex): void
默认异常处理：记录日志、设置 500 响应头并渲染 `error_500` 视图或回调

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
开发错误处理：仅在调试模式下输出调试信息

    public function getOverrideableFile($path_sub, $file, $use_override = true)
解析可覆盖文件路径，支持子应用别名和根应用路径回退

    public function skip404Handler()
设置 `skip_404` 为 `true`

    public function onBeforeOutput()
输出前触发 `EventManager` 事件

    public function adjustViewFile($view)
视图文件名为空时，返回当前路由调用路径

    public static function Platform()
返回当前平台标识

    public function _Platform()
从 Setting 中读取 `duckphp_platform`

    public static function IsDebug()
判断是否处于调试模式

    public function _IsDebug()
综合 Setting 和选项判断调试模式

    public static function IsRealDebug()
判断是否为真实调试模式

    public function _IsRealDebug()
内部实现，等价于 `_IsDebug()`

    public function isInstalled()
返回 `installed` 选项值

    public function lang($str, $args = [])
基础语言/参数替换处理

### 受保护方法

    protected function doInitComponents(): void
初始化 `Logger`、`SuperGlobal`、`SystemWrapper`、`View` 核心组件

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)
- [DuckPhp\DuckPhp](DuckPhp.md)
- [DuckPhp\Core\Logger](Core-Logger.md)
- [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
- [DuckPhp\Core\View](Core-View.md)
