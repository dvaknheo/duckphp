# DuckPhp\Foundation\SimpleExceptionTrait

简单异常类 Trait。

## 简介

`DuckPhp\Foundation\SimpleExceptionTrait` 是异常类的简化组合 Trait，仅引入了 `DuckPhp\Core\ThrowOnTrait`，使异常类支持快速抛出异常的方法。

## 选项

无。

## 使用方式

### 在自定义异常类中使用

```php
use DuckPhp\Foundation\SimpleExceptionTrait;

class MyException extends \Exception
{
    use SimpleExceptionTrait;
}
```

### 抛出异常

```php
MyException::ThrowOn($condition, 'error message', 500);
```

## 注意事项

1. 该 Trait 本身没有定义方法，所有方法均来自 `ThrowOnTrait`。
2. 自定义异常类通常继承 `\Exception` 或 `\RuntimeException`。

## 方法列表

### 公共方法

来自引入的 Trait，无自有方法。

## 相关链接

- [DuckPhp\Core\ThrowOnTrait](Core-ThrowOnTrait.md)
