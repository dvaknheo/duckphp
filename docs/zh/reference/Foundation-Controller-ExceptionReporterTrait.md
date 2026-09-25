# DuckPhp\Foundation\Controller\ExceptionReporterTrait

异常报告器骨架：按异常**短类名**分派到 `on{短类名}($ex)`，命中就调用，没命中落回应用的默认异常处理。

## 简介

`ExceptionReporterTrait` 是「项目异常报告器」的推荐实现。它只做一件事：把异常**类型**翻译成你的**方法**：

1. `OnException($ex)`（静态入口）→ `static::_()->_OnException($ex)`；
2. `_OnException()` 取异常的短类名 `Xxx`，拼出方法名 `onXxx()`；
3. 该方法可调用（实例方法、静态方法都行）→ 调用它，并把它的返回值原样返回；
4. 不可调用 → `App::_()->_OnDefaultException($ex)`。

报告器自己不判断“异常是不是项目的”、不做过滤：谁进得来由 [`ExceptionManager`](Core-ExceptionManager.md) 的「异常类 → 处理器」映射决定，而这份映射是入口类读 `exception_reporter` / `exception_for_project` 两个选项注册的。所以一个报告器能同时收到业务异常、控制器异常等若干种类型，分派完全按短类名走。

## 类信息

- 命名空间：`DuckPhp\Foundation\Controller`
- 声明：`trait ExceptionReporterTrait`
- 使用的 Trait：**无**（本 Trait 不 use 任何 Trait；组合它的类必须自己提供 `_()`，见「注意事项」第 1 条）
- 常量：无

## 使用方式

```php
namespace MyProject\Controller;

use DuckPhp\Foundation\Controller\ExceptionReporterTrait;
use DuckPhp\Foundation\SingletonTrait;

class ExceptionAction
{
    use SingletonTrait;                 // 提供 _()，OnException() 靠它取实例
    use ExceptionReporterTrait;

    public function onBusinessException($ex)   // MyProject\System\BusinessException
    {
        Helper::ShowJson(['error' => $ex->getMessage()]);
    }
    public function onControllerException($ex) // MyProject\System\ControllerException
    {
        Helper::Show302('login');
    }
}
```

在入口类里启用（值必须是**可调用**的；裸类名不是 callable，会启动即抛）：

```php
// MyProject\System\App
public $options = [
    'exception_reporter'    => [ExceptionAction::class, 'OnException'],
    'exception_for_project' => ProjectException::class,   // 报告器接管这一类及其子类
];
```

## 注意事项

1. **组合类必须自己带 `_()`**。`OnException()` 的实现是 `static::_()->_OnException($ex)`，而本 Trait 不 use 单例 Trait，所以报告器类要自己 `use DuckPhp\Foundation\SingletonTrait`（或继承已经组合了它的 `Controller\Base`）。“只 use 本 Trait”的类在**异常真的抛出来那一刻**报 `Call to undefined method …::_()`——启动时看不出来，那一步只校验 `is_callable()`。
2. 方法名 = `on` + 异常**短类名**（`MyProject\System\BusinessException` → `onBusinessException()`），与异常的命名空间无关，也与 `exception_for_*` 选项无关。
3. 静态方法与实例方法都能命中：判断用的是 `is_callable([$this, $method])`，而静态方法对实例同样可调用。
4. **方法名撞车会走兜底**：PHP 方法名大小写不敏感，异常类叫 `Exception` 时拼出的 `onException` 与 `OnException` 只差大小写 → 命中递归保护，直接 `App::_()->_OnDefaultException($ex)`（`_OnException` 同理）。想在“没有任何 `onXxx()` 命中”时统一收口，别指望加 `onException()`，去覆盖 `App::_OnDefaultException()`。
5. **旧接口已删**：早期版本按 `App::options['namespace']` 判断“是不是项目异常”，并提供可覆盖的 `defaultException()` / `defaultSystemException()`。这两个方法现在都不存在，命名空间也不再参与判断，兜底固定为 `App::_()->_OnDefaultException()`（照旧文档写会直接 `Call to undefined method`）。
6. 注册发生在入口类 init 时（`DuckPhp::initComponentsOfInner()`）：`exception_reporter` 过不了 `is_callable()` 就抛 `DuckPhpSystemException("'exception_reporter' config error!:…")`；`exception_for_project`（缺省 `\Exception::class`）是 assign 的目标基类，子类一并命中（所以 `BusinessException extends ProjectException` 也会进报告器）。

## 方法列表

### 公共方法

    public static function OnException(\Throwable $ex)
静态入口：转发 `static::_()->_OnException($ex)`，返回报告器方法的返回值。

    public function _OnException($ex)
实例侧入口：按异常短类名拼 `on{短类名}()`，可调用就执行并返回其返回值，否则 `App::_()->_OnDefaultException($ex)`。

## 相关链接

- [DuckPhp\Foundation\SingletonTrait](Foundation-SingletonTrait.md) — 提供 `_()`，组合报告器时通常需要
- [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) — `exception_reporter` 的消费方（异常类 → 处理器映射）
- [DuckPhp\Core\App](Core-App.md) — `_OnDefaultException()` 与 `exception_reporter` / `exception_for_project` 选项来源
- [DuckPhp\DuckPhp](DuckPhp.md) — init 时读选项并 assign 的装配点
- guide: [exception](../guide/exception.md)、[lifecycle](../guide/lifecycle.md)
