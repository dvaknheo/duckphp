# DuckPhp\Core\Logger

日志组件。

## 简介

`Logger` 提供基础的日志记录能力，支持 PSR-3 风格的日志级别（`emergency`、`alert`、`critical`、`error`、`warning`、`notice`、`info`、`debug`）。日志文件按模板生成，支持上下文参数替换，并自动附加当前请求路径信息。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径。相对路径的日志目录会基于该路径计算。 |
| `path_log` | `'runtime'` | 日志文件所在目录。 |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | 日志文件名模板，支持 `date()` 格式字符。 |
| `log_prefix` | `'DuckPhpLog'` | 日志前缀，输出在每条日志中。 |

## 使用方式

### 全局函数

```php
__logger()->info('用户 {id} 登录', ['id' => 42]);
__logger()->error('数据库连接失败');

// 仅在调试模式下输出
__debug_log('调试信息 {name}', ['name' => 'test']);
```

### 通过 Logger 组件

```php
use DuckPhp\Core\Logger;

Logger::_()->info('订单创建成功', ['order_id' => 12345]);
Logger::_()->error('处理异常: {message}', ['message' => $ex->getMessage()]);
```

### 日志级别

```php
use DuckPhp\Core\Logger;

Logger::_()->emergency('系统不可用');
Logger::_()->alert('必须立即处理');
Logger::_()->critical('严重错误');
Logger::_()->error('运行时错误');
Logger::_()->warning('警告信息');
Logger::_()->notice('普通通知');
Logger::_()->info('普通信息');
Logger::_()->debug('调试信息');
```

## 配置示例

### 基础配置

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'path_log' => 'runtime/logs',
        'log_file_template' => 'log_%Y-%m-%d.log',
        'log_prefix' => 'MyApp',
    ];
}
```

### 按小时分日志文件

```php
class App extends \DuckPhp\DuckPhp
{
    public $options = [
        'log_file_template' => 'log_%Y-%m-%d_%H.log',
    ];
}
```

## 日志文件格式

日志文件默认格式如下：

```
[info][DuckPhpLog][2024-01-01 12:00:00]: /user/login : 用户 42 登录
```

## 注意事项

1. 日志文件路径为 `path_log` 与 `log_file_template` 组合后的完整路径。`path_log` 为空时，`error_log` 会按系统默认方式输出。
2. `log_file_template` 中的 `%X` 会被替换为 `date('X')`，例如 `%Y-%m-%d` 会生成 `2024-01-01`。
3. 上下文参数替换使用 `{key}` 格式，值会被 `var_export()` 后替换。
4. 日志消息会自动追加 `PATH_INFO` 信息，便于追踪请求上下文。

## 全部选项

```php
    'path' => '',
    'path_log' => 'runtime',
    'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
    'log_prefix' => 'DuckPhpLog',
```

## 方法列表

### 公共方法

    public function log($level, $message, array $context = array())
写入一条日志。`$level` 为日志级别，`$message` 支持 `{key}` 上下文替换

    public function emergency($message, array $context = array())
记录 `emergency` 级别日志

    public function alert($message, array $context = array())
记录 `alert` 级别日志

    public function critical($message, array $context = array())
记录 `critical` 级别日志

    public function error($message, array $context = array())
记录 `error` 级别日志

    public function warning($message, array $context = array())
记录 `warning` 级别日志

    public function notice($message, array $context = array())
记录 `notice` 级别日志

    public function info($message, array $context = array())
记录 `info` 级别日志

    public function debug($message, array $context = array())
记录 `debug` 级别日志

## 相关链接

- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md)
- [DuckPhp\Core\Functions](Core-Functions.md)
