# DuckPhp\Core\KernelTrait

应用核心初始化流程 Trait。

## 简介

`KernelTrait` 定义了 DuckPHP 应用从初始化到运行结束的完整生命周期流程。它负责：

- 选项合并与外部配置加载
- Phase 容器管理
- 生命周期事件触发
- 核心组件（`Console`、`Route`、`Runtime`）初始化
- 扩展（`ext`）和子应用（`app`）的初始化和运行
- 404、异常、开发错误处理

`App` 类通过 `use KernelTrait` 获得这些能力。开发者通常不需要直接使用 `KernelTrait`，而是通过继承 `App` 或 `DuckPhp` 来定制应用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `null` | 项目根路径。未设置时自动根据 `SCRIPT_FILENAME` 推断。 |
| `override_class` | `null` | 指定用于覆盖当前应用的类名。 |
| `override_class_from` | `null` | 覆盖类的来源类。 |
| `cli_enable` | `true` | 是否启用 CLI 命令处理。 |
| `is_debug` | `false` | 是否开启调试模式。 |
| `ext` | `[]` | 主扩展列表，初始化顺序在 `onInit` 之后。 |
| `app` | `[]` | 子应用列表，初始化顺序在 `onBeforeChildrenInit` 之后。 |
| `skip_404` | `false` | 是否跳过默认 404 处理。 |
| `skip_exception_check` | `false` | 是否跳过异常捕获检查。 |
| `on_init` | `null` | 初始化完成后的回调函数。 |
| `namespace` | `null` | 项目默认命名空间。 |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | Setting 文件路径。 |
| `setting_file_ignore_exists` | `true` | Setting 文件不存在时是否忽略。 |
| `setting_file_enable` | `true` | 是否加载 Setting 文件。 |
| `use_env_file` | `false` | 是否加载 `.env` 文件。 |
| `options_file` | `'config/DuckPhpOptions.config.php'` | 额外选项文件路径。 |
| `options_file_enable` | `false` | 是否加载额外选项文件。 |
| `path_installed_options` | `'config'` | 已安装选项文件所在目录。 |
| `installed_options_file` | `'DuckPhpInstalled.config.php'` | 已安装选项文件名。 |
| `installed_options_enable` | `false` | 是否加载已安装选项文件。 |
| `cli_command_classes` | `[]` | 注册的 CLI 命令类。 |
| `cli_command_prefix` | `null` | CLI 命令命名空间前缀。 |
| `cli_command_method_prefix` | `'command_'` | CLI 命令方法前缀。 |

## 使用方式

### 通过 App 类使用

```php
use DuckPhp\Core\App;

class MyApp extends App
{
    public $options = [
        'is_debug' => true,
    ];
}

MyApp::RunQuickly();
```

### 运行入口

```php
$app = MyApp::_()->init([
    'path' => __DIR__ . '/../',
    'is_debug' => true,
]);
$app->run();
```

### 生命周期事件

```php
class MyApp extends App
{
    protected function onInit()
    {
        // 在核心组件初始化完成后、子应用初始化前触发
        EventManager::FireEvent([static::class, __FUNCTION__]);
    }

    protected function onBeforeRun(): void
    {
        // 在 run() 开始处理请求前触发
    }

    protected function onAfterRun(): void
    {
        // 在 run() 结束后触发
    }
}
```

### 获取当前应用/根应用

```php
$current = MyApp::Current(); // 当前 Phase 下的应用实例
$root    = MyApp::Root();    // 根应用实例
$phase   = MyApp::Phase();   // 获取/切换当前 Phase
```

## 配置示例

### 基础应用配置

```php
class MyApp extends App
{
    public $options = [
        'path' => __DIR__ . '/../',
        'is_debug' => true,
        'namespace' => 'MyApp',
    ];
}
```

### 加载外部选项文件

```php
class MyApp extends App
{
    public $options = [
        'options_file_enable' => true,
        'options_file' => 'config/DuckPhpOptions.config.php',
    ];
}
```

### 加载 .env 文件

```php
class MyApp extends App
{
    public $options = [
        'use_env_file' => true,
    ];
}
```

### 注册子应用

```php
class MyApp extends App
{
    public $options = [
        'app' => [
            \ApiApp\System\ApiApp::class => [
                'path' => __DIR__ . '/../api',
            ],
        ],
    ];
}
```

## 注意事项

1. **初始化顺序**：`init()` 内部按以下顺序执行：
   - 合并选项并加载 `options_file`
   - 处理 `override_class`
   - 初始化容器（`initContainer`）和 Phase 管理
   - 初始化异常管理（`initException`）
   - 加载 `installed_options`
   - 触发 `onPrepare`
   - 初始化核心组件（`prepareComponents` → `initComponents`）
   - 初始化 `ext` 扩展（`initExtentions($options['ext'], true)`）
   - 触发 `onInit` 和 `on_init` 回调
   - 触发 `onBeforeChildrenInit`
   - 初始化子应用 `app`（`initExtentions($options['app'], false)`）
   - 标记完成并触发 `onInited`

2. **Phase 概念**：`Phase` 是 DuckPHP 的多应用切换机制。通过 `PhaseContainer` 切换当前活跃的应用实例，使 `Current()` 和 `::_()` 返回正确的应用对象。

3. **ext 与 app 的区别**：
   - `ext` 是主扩展，共享主应用的选项，初始化后 Phase 不切换。
   - `app` 是子应用，拥有独立的命名空间和路径，初始化后 Phase 会切回主应用。

4. **CLI 与 Web 路由**：`run()` 内部根据 `PHP_SAPI` 决定调用 `Console::_()->run()` 还是 `Route::_()->run()`。

5. **404 处理**：当路由匹配失败且子应用也未能处理时，会触发 `On404` 事件，并根据 `skip_404` 决定是否调用 `_On404()`。

6. **异常处理**：`run()` 中捕获的所有异常都会交给 `ExceptionManager` 处理；未处理的异常会触发 `OnDefaultException`。





## 全部选项

```php
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

    public static function RunQuickly(array $options = [], callable $after_init = null): bool
快速运行入口：初始化应用，可选执行回调，然后调用 `run()`

    public static function Current()
返回当前 Phase 下的应用实例

    public static function Root()
返回根应用实例

    public static function Phase($new = null)
获取或切换当前 Phase 容器

    public static function Setting($key = null, $default = null)
通过 `_Setting()` 获取根应用的 Setting 值

    public static function IsRoot()
判断当前实例是否为根应用

    public function init(array $options, object $context = null)
应用完整初始化流程

    public function run(): bool
应用运行入口，处理 CLI 或 Web 请求

    public static function On404(): void
触发 404 处理

    public static function OnDefaultException($ex): void
触发默认异常处理

    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
触发开发错误处理

    public function _On404(): void
默认 404 处理，输出 `"no found"`

    public function _OnDefaultException($ex): void
默认异常处理，输出 `"_OnDefaultException"`

    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
默认开发错误处理，输出 `"_OnDevErrorHandler"`

### 受保护方法

    protected function initOptions(array $options): void
合并选项并加载 `options_file`

    protected function getDefaultProjectNameSpace(?string $class): string
根据类名推断默认项目命名空间

    protected function getDefaultProjectPath(): string
根据 `SCRIPT_FILENAME` 推断默认项目路径

    public function _Phase($new = null)
Phase 容器操作内部实现

    public function _IsRoot()
返回当前实例是否为根应用

    public function getOverridingClass()
返回当前应用的覆盖类

    protected function initContainer(object $context = null): bool
初始化 Phase 容器，注册公共类，管理根/子应用状态

    protected function addPublicClassesInRoot(array $classes): void
在根应用下注册公共类

    protected function createLocalObject(string $class, ?object $object = null): object
创建局部对象实例

    protected function initException(array $options): void
初始化 `ExceptionManager`

    protected function initInstalledOptions()
加载并合并 `installed_options_file`

    protected function prepareComponents(): void
准备核心组件，子类可覆盖

    protected function initComponents(array $options, object $context = null): void
初始化 `Console`、`Route`、`Runtime` 等核心组件

    protected function doInitComponents(): void
子类覆盖以初始化额外核心组件

    protected function loadSetting(): void
加载 `.env` 和 Setting 文件

    protected function dealWithEnvFile(): void
解析 `.env` 文件

    protected function dealWithSettingFile(): void
加载 Setting 文件

    public function _Setting($key = null, $default = null)
获取 Setting 值

    protected function initExtentions(array $exts, bool $use_main_options): void
初始化扩展或子应用

    protected function runException(\Throwable $ex): void
处理运行期异常

    protected function runExtentions(): bool
依次运行子应用，直到有一个返回 `true`

    protected function onBeforeCreatePhases(): void
生命周期事件：创建 Phase 容器前

    protected function onAfterCreatePhases(): void
生命周期事件：创建 Phase 容器后

    protected function onPrepare(): void
生命周期事件：选项和容器准备完成后

    protected function onBeforeChildrenInit(): void
生命周期事件：子应用初始化前

    protected function onInit()
生命周期事件：核心组件和扩展初始化完成后

    protected function onInited()
生命周期事件：全部初始化完成后

    protected function onBeforeRun(): void
生命周期事件：运行前

    protected function onAfterRun(): void
生命周期事件：运行后

## 相关链接

- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md)
- [DuckPhp\Core\Route](Core-Route.md)
- [DuckPhp\Core\Runtime](Core-Runtime.md)
- [DuckPhp\Core\Console](Core-Console.md)

- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
