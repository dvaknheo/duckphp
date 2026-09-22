# DuckPhp\Ext\ThrowOnTrait

## 简介

`ThrowOnTrait` 提供静态条件抛出异常方法。用于简写“当某条件满足时抛一个当前类的实例”。

- `ThrowOn($flag, $message, $code)`：当 `$flag` 为真时 `throw new static($message, $code)`；否则什么都不做。

它常被异常类继承使用：把异常类本身当成“构造函数 + 抛出”均可的载体。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`trait ThrowOnTrait`
- 典型宿主：`DuckPhp\Core\DuckPhpSystemException`。任何类都要能 `use` 后获得 `static::ThrowOn(...)`。

## 使用方式

```php
class MyException extends \DuckPhp\Core\DuckPhpSystemException
{
}

// 简单：flag 成立则抛
MyException::ThrowOn($ok === false, 'invalid token');
```

若 `$flag` 为真抛异常；为假则静默返回。

## 注意事项

- 抛出的实例类型为 `static::class`（当前用处的类）。
- 它假设是用该 trait 的类是 `Exception`（或其子类），否则 `new static(...)` 不接 ($message,$code)。
- 直接把条件判断简并一行，适合做“守卫”式校验。

## 方法列表

### 公共方法

    public static function ThrowOn($flag, $message, $code = 0)
若 $flag 为真则 `throw new static($message, $code)`；否则直接返回（无副作用）。

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 宿主异常基类
