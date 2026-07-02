# DuckPhp\Core\ThrowOnTrait

条件抛出异常 Trait。

## 简介

`ThrowOnTrait` 提供一个简单的辅助方法 `ThrowOn()`，当传入的条件为真时抛出当前类的异常。该 Trait 通常被异常类自身使用，例如 `DuckPhpSystemException` 及其子类。

## 选项

无。本 Trait 不直接定义配置选项。

## 使用方式

### 在自定义异常类中使用

```php
namespace MyApp\Exception;

use DuckPhp\Core\ThrowOnTrait;

class BusinessException extends \Exception
{
    use ThrowOnTrait;
}
```

### 条件抛出异常

```php
BusinessException::ThrowOn($user === null, '用户不存在', 404);
```

等价于：

```php
if ($user === null) {
    throw new BusinessException('用户不存在', 404);
}
```

## 配置示例

无。

## 注意事项

1. `ThrowOn()` 在条件为真时抛出 `new static(...)`，因此 Trait 应被类（尤其是异常类）使用，而不是普通类。
2. 抛出异常的代码行应包含清晰的消息与可选的业务错误码。
3. 该 Trait 不会记录日志或做任何额外处理，仅做条件抛出的语法糖。

## 方法列表

### 公共方法

    public static function ThrowOn($flag, $message, $code = 0)
当 `$flag` 为真时抛出 `new static($message, $code)` 异常

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md)
