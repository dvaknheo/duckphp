# DuckPhp\Foundation\ExceptionTrait

## 简介

`ExceptionTrait` 是 Foundation 层对 `Core\ThrowOnTrait` 的**薄封装**：让工程内的自定义异常类通过 `use ExceptionTrait` 获得静态 `ThrowOn($flag, $message, ...)` 快捷抛异常能力，而不必直接依赖 `Core` 命名空间。

实现为 `use DuckPhp\Core\ThrowOnTrait;`。

## 类信息

- 命名空间：`DuckPhp\Foundation`
- 声明：`trait ExceptionTrait`
- 使用的 Trait：`DuckPhp\Core\ThrowOnTrait`

## 使用方式

```php
namespace MyProject\System;

use DuckPhp\Foundation\ExceptionTrait;

class MyException extends \Exception
{
    use ExceptionTrait;
}

// 用法：条件为真即抛（异常类为 static::class，即 MyException）
MyException::ThrowOn($flag, '出错了', 1001);
```

## 注意事项

- 框架自带的系统异常（`DuckPhpSystemException`）已组合 `ThrowOnTrait`；本 Trait 是为“工程自定义异常”准备的同等能力。

## 方法列表

本 Trait 未自行声明方法：静态 `ThrowOn(...)` 由 `Core\ThrowOnTrait` 提供（组合后即可 `MyException::ThrowOn($flag, $message, $code)`）。

## 相关链接

- [DuckPhp\Core\ThrowOnTrait](Core-ThrowOnTrait.md) — 实际实现
- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 已使用该能力的系统异常
