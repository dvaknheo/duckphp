# DuckPhp\Core\Logger

极简的写入文件日志器，提供 PSR-3 风格的 level 便捷方法（emergency…debug），把格式化行追加到日志文件。

## 简介

`Logger`（PSR-3 注释，非 implements，尽力对齐接口）适合“无外部依赖也能落到文件里”的日志需求：

- 每条日志由 `PLACE/PREFIX//date/time: PATH_INFO? :message\n` 拼装通过 `error_log($msg, 3, $file)` 追加写入；
- `context` 里的键会进入格式串（`{key}` 先 var_export）替换；
- 日志文件模板用 `strftime`-ish 的 `%Y`… — 运行时 `log()` 会用 `preg_replace_callback('/%(.)/', =>date($1), 模板)` 展开；
- level 写为 filename 前缀标识（`log_prefix`），默认 `DuckPhpLog`。

等级常量 EMERGENCY/ALERT/CRITICAL/ERROR/WARNING/NOTICE/INFO/DEBUG 定义在类…代码 `const … = '…'`。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class Logger extends ComponentBase`
- 常量：`const EMERGENCY=>'emergency'; ALERT=>'alert'; CRITICAL=>'critical'; ERROR=>'error'; WARNING=>'warning'; NOTICE=>'notice'; INFO=>'info'; DEBUG=>'debug';`
- 相关：`CoreHelper::Logger()`返回这个句柄。

## 选项

`Logger::$options`：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_log` | `'runtime'` | 日志目录（可绝对，可相对根）。 |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | 日志文件名模板；`%X` 由 `date(X)` 展开。 |
| `log_prefix` | `'DuckPhpLog'` | 写到行的前缀标识。 |

> 相对路径的基准是**项目根路径**，即 `App::_()->getProjectPath()`（源码内部取的是 `App::Root()->options['path']`）；本类自己**没有** `path` 选项（早期版本曾有，已移除）。

## 使用方式

```php
use DuckPhp\Core\Logger;

Logger::_()->init([ 'path_log' => __DIR__, ]);
Logger::_()->info('user {id} login', ['id' => 7]);
Logger::_()->error('db failed: {err}', ['err' => $e]);
```

高级（上下文展开）被 var_export 成 PHP 表达便于排查。经 `CoreHelper::Logger()`/App? 也直接可用。

（若希望在“业务层”只用 Shell — DuckPhp 会提供 `Logger::_()`；习惯层面建议把日志写在 Helper 兼容层而不是 Model/Controller 核心。）

## 配置示例

```php
// 项目 options
'path_log' => 'runtime',
'log_file_template' => 'log_%Y-%m_%d_%H_%i.log',   // 与默认同逻辑的示例
'log_prefix'         => 'Shop',
```

产生类似 `runtime/log_2025-06-11_15_10.log` 的日志。

## 注意事项

- 不做异步/轮转；同步追加。
- `log()` 计算模板/路径/前缀等；抛可解析级别名称应属于上述常量。
- `init_once=true`；首次 init 后重复 init 幂等。
- 若某条写入失败（如目录不存在），返回 false/记在 catch 内，不向上炸（try/catch在源里）。

## 全部选项

```php
    public $options = [
        'path_log' => 'runtime',
        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        'log_prefix' => 'DuckPhpLog',
    ];
```

## 方法列表

### 公共方法

    public function log($level, $message, array $context = array())
总入口：展开模板文件路径、替换 {key}、加 PATH_INFO 与日期再 `error_log(…,3,file)`。

    public function emergency($message, array $context = array())
log(EMERGENCY,…)。

    public function alert($message, array $context = array())
log(ALERT,…)。

    public function critical($message, array $context = array())
log(CRITICAL,…)。

    public function error($message, array $context = array())
log(ERROR,…)。

    public function warning($message, array $context = array())
log(WARNING,…)。

    public function notice($message, array $context = array())
log(NOTICE,…)。

    public function info($message, array $context = array())
log(INFO,…)。

    public function debug($message, array $context = array())
log(DEBUG,…)。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) `Logger()` 句柄
