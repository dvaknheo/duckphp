# DuckPhp\Core\ExceptionManager

异常管理组件。

## 简介

`ExceptionManager` 负责接管 PHP 的异常和错误处理流程。它通过注册全局的异常处理函数和错误处理函数，将未捕获的异常、致命错误以及开发期提示性错误统一交给框架处理。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `handle_all_dev_error` | `true` | 是否接管 PHP 开发期错误（notice、deprecated、strict 等）。 |
| `handle_all_exception` | `true` | 是否接管未捕获的异常。 |
| `system_exception_handler` | `null` | 自定义系统级异常处理器。为空时使用默认的 `set_exception_handler`。 |
| `handle_exception_on_init` | `true` | 初始化完成后是否立即启用异常处理。 |
| `default_exception_handler` | `null` | 默认异常处理回调，当没有任何具体处理器匹配时调用。 |
| `dev_error_handler` | `null` | 开发期错误的处理回调，仅在 `handle_all_dev_error` 为 `true` 时生效。 |
| `exception_reporter` | `null` | 自定义异常报告器类名。设置后，将 `exception_for_project` 指定的异常类交给该报告器处理。 |
| `exception_for_project` | `null` | 异常报告器捕获的异常类。 |

## 使用方式

### 基本使用

默认情况下，`DuckPhp` 初始化后会自动启用异常处理，无需手动调用。

```php
use DuckPhp\DuckPhp;

$app = DuckPhp::_();
$app->init([
    'path' => __DIR__,
]);
```

### 手动注册异常处理器

```php
use DuckPhp\Core\ExceptionManager;

ExceptionManager::_()->assignExceptionHandler(\MyApp\Exception\BusinessException::class, function ($ex) {
    echo '业务异常: ' . $ex->getMessage();
});

ExceptionManager::_()->setDefaultExceptionHandler(function ($ex) {
    echo '默认异常处理: ' . $ex->getMessage();
});
```

### 批量注册异常处理器

```php
use DuckPhp\Core\ExceptionManager;

ExceptionManager::_()->assignExceptionHandler([
    \MyApp\Exception\NotFoundException::class => function ($ex) {
        // 处理 404 相关异常
    },
    \MyApp\Exception\AuthException::class => function ($ex) {
        // 处理权限相关异常
    },
]);

ExceptionManager::_()->setMultiExceptionHandler([
    \MyApp\Exception\A::class,
    \MyApp\Exception\B::class,
], function ($ex) {
    // 统一处理 A 和 B 异常
});
```

### 运行与清理

```php
use DuckPhp\Core\ExceptionManager;

$manager = ExceptionManager::_();
$manager->run();     // 注册全局错误/异常处理
$manager->clear();   // 恢复默认错误/异常处理
```

## 配置示例

### 基础配置

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'handle_all_exception' => true,
        'handle_all_dev_error' => true,
        'handle_exception_on_init' => true,
    ];
}
```

### 自定义异常处理器

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'handle_all_exception' => true,
        'default_exception_handler' => function ($ex) {
            header('Content-Type: text/plain');
            echo 'Exception: ' . $ex->getMessage();
        },
        'dev_error_handler' => function ($errno, $errstr, $errfile, $errline) {
            error_log("DevError [$errno]: $errstr in $errfile:$errline");
        },
    ];
}
```

### 使用异常报告器

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'exception_reporter' => \MyApp\Controller\ExceptionReporter::class,
        'exception_for_project' => \MyApp\System\ProjectException::class,
    ];
}
```

`ExceptionReporter` 类需要实现 `OnException($ex)` 方法。

## 注意事项

1. 异常处理器按后进先出的顺序匹配，最后注册的处理器会优先检查。
2. 错误处理时，notice、strict、deprecated 等开发期错误会交给 `dev_error_handler`，其他错误会转为 `ErrorException` 抛出。
3. 调用 `clear()` 后，会恢复 PHP 默认的错误和异常处理机制。
4. `ExitException` 会被跳过处理，以避免退出流程被重复处理。

## 全部选项

```php
    'handle_all_dev_error' => true,
    'handle_all_exception' => true,
    'system_exception_handler' => null,
    'handle_exception_on_init' => true,

    'default_exception_handler' => null,
    'dev_error_handler' => null,
    'exception_reporter' => null,
    'exception_for_project' => null,
```

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
初始化异常管理器，并根据 `handle_exception_on_init` 决定是否自动启用处理

    public static function CallException($ex)
静态入口，调用当前实例的 `_CallException` 处理异常

    public function setDefaultExceptionHandler($default_exception_handler)
设置默认异常处理回调

    public function assignExceptionHandler($class, $callback = null)
注册一个或多个异常处理器。`$class` 可以是类名字符串，也可以是 `[类名 => 回调]` 的数组

    public function setMultiExceptionHandler(array $classes, $callback)
为多个异常类注册同一个回调

    public function on_error_handler($errno, $errstr, $errfile, $errline)
PHP 错误处理回调，将非 notice 类错误转为 `ErrorException`

    public function isInited():bool
返回组件是否已初始化

    public function run()
启用全局错误/异常处理

    public function reset()
重置处理器状态（当前实现不清理已注册的处理器）

    public function clear()
清理并恢复默认错误/异常处理机制

### 受保护方法

    protected function initOptions(array $options): void
从选项中初始化默认异常处理器和系统异常处理器

    public function _CallException($ex)
根据注册顺序查找匹配处理器并执行；没有匹配则调用默认处理器

## 相关链接

- [DuckPhp\Core\Logger](Core-Logger.md)
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md)
- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md)
