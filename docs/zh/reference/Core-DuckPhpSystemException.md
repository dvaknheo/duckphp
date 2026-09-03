# DuckPhp\Core\DuckPhpSystemException

## 简介

系统抛出的、携带 ThrowOn 能力的通用异常基类。

`DuckPhpSystemException extends Exception`（PHP 标准异常），并用 `ThrowOnTrait` 提供静态遍历“满足则抛”的入口。框架内部在各处用它的子类直接抛异常来描述问题（如“Phase 重名”“不可 init 基类”等），同时业务可将自己的异常类继承它，或用其 Trait 获得 `ThrowOn` 一次性守卫式抛法。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class DuckPhpSystemException extends Exception`
- 使用 Trait：`DuckPhp\Core\ThrowOnTrait`

## 使用方式

```php
use DuckPhp\Core\DuckPhpSystemException;

// 守卫式：不满足即抛
DuckPhpSystemException::ThrowOn($user == null, 'not logged in');
```

自定义更专业异常时直接子类化：

```php
class MyBizException extends DuckPhpSystemException
{
}
```

### 作为“exit 换成异常”的基类
`DuckPhp\Core\ExitException extends DuckPhpSystemException`，供 `__EXIT_EXCEPTION` 语义（通过抛 ExitException 实现中断而不是真正的 exit）。可见 `use_exit_exception` 描述于 `Core-KernelTrait`。

## 方法列表

本类**自身没有显式声明任何方法**，可用能力来自以下来源：

- 静态抛出工具 `ThrowOn(...)`：来自 `use DuckPhp\Core\ThrowOnTrait` —— 首位参数为真时 `throw new static($message,$code)`；详见 [Core-ThrowOnTrait](Core-ThrowOnTrait.md)。
- 标准异常能力：来自 PHP 内置 `Exception`（`getMessage()`、`getCode()`、`getPrevious()`、`getLine()`、`getFile()`、`getTrace()`、`__toString()` 等），全部可用。

## 相关链接

- [DuckPhp\Core\ThrowOnTrait](Core-ThrowOnTrait.md) — 静态抛异常来源 Trait
- [DuckPhp\Core\ExitException](Core-ExitException.md) — 它的独特子类（exit 语义）
