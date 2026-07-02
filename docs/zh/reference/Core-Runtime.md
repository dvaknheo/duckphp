# DuckPhp\Core\Runtime

运行时组件。

## 简介

`Runtime` 负责管理应用的运行状态，包括运行标记、输出缓冲控制以及异常状态标记。它为 `DuckPhp` 主流程提供运行期上下文。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `use_output_buffer` | `false` | 是否启用输出缓冲。 |
| `path_runtime` | `'runtime'` | 运行时目录路径。 |

## 使用方式

### 检查运行状态

```php
use DuckPhp\Core\Runtime;

if (Runtime::_()->isRunning()) {
    // 当前应用正在运行中
}

if (Runtime::_()->isInException()) {
    // 当前处于异常处理流程
}

if (Runtime::_()->isOutputed()) {
    // 输出已经清理/完成
}
```

### 启动运行时

```php
use DuckPhp\Core\Runtime;

Runtime::_()->run();   // 若启用 use_output_buffer 则开启输出缓冲
```

### 清理运行时

```php
use DuckPhp\Core\Runtime;

Runtime::_()->clear(); // 关闭输出缓冲，重置运行状态
```

### 标记异常状态

```php
use DuckPhp\Core\Runtime;

Runtime::_()->onException(true);  // 清理输出缓冲并标记异常状态
Runtime::_()->onException(false); // 仅标记异常状态
```

## 配置示例

### 启用输出缓冲

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'use_output_buffer' => true,
        'path_runtime' => 'runtime',
    ];
}
```

## 注意事项

1. `run()` 和 `clear()` 应成对使用，`clear()` 会关闭 `run()` 开启的输出缓冲层。
2. 当 `use_output_buffer` 为 `true` 时，`run()` 会记录当前 `ob_get_level()` 并在 `clear()` 时恢复到该层级。
3. `onException()` 通常由框架异常处理流程调用，一般不需要手动调用。

## 全部选项

```php
    'use_output_buffer' => false,
    'path_runtime' => 'runtime',
```

## 方法列表

### 公共方法

    public function isRunning()
返回当前是否处于运行状态

    public function isInException()
返回当前是否处于异常处理流程

    public function isOutputed()
返回输出是否已经清理/完成

    public function run()
启动运行时，若启用输出缓冲则开启缓冲

    public function clear()
清理运行时状态并关闭输出缓冲层

    public function onException($skip_exception_check)
标记异常状态。传入 `true` 时会先调用 `clear()`

## 相关链接

- [DuckPhp\Core\App](Core-App.md)
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
