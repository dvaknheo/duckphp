# DuckPhp\Foundation\ExceptionReporterTrait

## 简介

`ExceptionReporterTrait` 是「项目异常报告器」的推荐实现：按“异常类是否属于本应用命名空间 + 类短名是否对应 `onXxx()` 方法”做分派，把项目异常交给对应处理方法，非项目异常/无对应方法则落到默认异常处理。

工作流程（`OnException($ex)`）：
1. 异常类命名空间**不是** `App::options['namespace']` 前缀 → 走 `defaultException($ex)`（视为系统/外部异常）；
2. 是项目异常 → 取异常类短名 `Xxx`，若当前实例存在可调用的 `onXxx($ex)` 方法则调用之；
3. 否则 → `defaultException($ex)` → `defaultSystemException($ex)` → `App::_()->_OnDefaultException($ex)`。

工程中把某类组合本 Trait 并实现若干 `onXxx()` 方法（如 `onBusinessException`），再配置为 `exception_reporter` 即可接管项目异常报告。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`trait ExceptionReporterTrait`
- 使用的 Trait：`DuckPhp\Core\SingletonExTrait`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporter
{
    use ExceptionReporterTrait;

    public function onBusinessException(\MyProject\System\BusinessException $ex)
    {
        // 处理项目业务异常
    }
}
// App options: 'exception_reporter' => ExceptionReporter::class,
```

## 注意事项

- 命名空间匹配用的是 `App::_()->options['namespace']`（项目命名空间），因此只有“项目自己的异常类”才会尝试 `onXxx` 分派。
- `onXxx` 的方法名 = 异常短类名（如 `BusinessException` → `onBusinessException`）；不可调用时回退默认。
- `defaultSystemException()` 委托 `App::_OnDefaultException()`；子类可覆盖 `defaultException()` 做兜底报告。

## 方法列表

### 公共方法

    public static function OnException($ex)
异常报告入口：按命名空间与 `onXxx` 方法分派到对应处理方法或默认处理。

    public function defaultException(\Throwable $ex): void
默认异常处理（默认转 `defaultSystemException`，可覆盖）。

### 受保护方法

    protected function defaultSystemException(\Throwable $ex): void
兜底：委托 `App::_()->_OnDefaultException($ex)`。

## 相关链接

- [DuckPhp\Core\App](Core-App.md) — `_OnDefaultException` 与 namespace 选项来源
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) — exception_reporter 的消费方
