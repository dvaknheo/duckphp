# DuckPhp\Core\DuckPhpSystemException

框架系统异常基类。

## 简介

`DuckPhpSystemException` 是 DuckPHP 框架内部异常体系的根类，继承自 PHP 内置 `Exception`，并使用 `ThrowOnTrait` 提供 `ThrowOn()` 辅助方法。所有 DuckPHP 系统级别的异常（如 `ExitException`）都继承自此类。

## 选项

无。本类不直接定义配置选项。

## 使用方式

### 抛出系统异常

```php
use DuckPhp\Core\DuckPhpSystemException;

throw new DuckPhpSystemException('系统错误', 500);
```

### 条件抛出

```php
DuckPhpSystemException::ThrowOn($error, '发生错误', 500);
```

### 自定义子类

```php
namespace MyApp\Exception;

use DuckPhp\Core\DuckPhpSystemException;

class MySystemException extends DuckPhpSystemException
{
    // 可在此添加自定义业务逻辑
}
```

## 配置示例

无。

## 注意事项

1. 这是框架级异常基类，业务代码通常应继承它创建自己的异常体系。
2. `ThrowOn()` 在条件成立时抛出 `new static(...)`，因此自定义子类抛出的实例类型为子类自身。
3. 该异常类不会自动处理 HTTP 响应码或错误页面，需要结合框架的错误处理机制使用。

## 方法列表

### 公共方法

    public static function ThrowOn($flag, $message, $code = 0)
来自 `ThrowOnTrait`，当 `$flag` 为真时抛出 `new static($message, $code)` 异常

## 相关链接

- [DuckPhp\Core\ThrowOnTrait](Core-ThrowOnTrait.md)
- [DuckPhp\Core\ExitException](Core-ExitException.md)
