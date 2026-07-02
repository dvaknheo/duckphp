# DuckPhp\Core\ExitException

框架退出异常。

## 简介

`ExitException` 继承自 `DuckPhpSystemException`，用于在 DuckPHP 中统一表示“退出”语义。它提供 `Init()` 方法注册 `__EXIT_EXCEPTION` 常量，框架其他部分可据此识别并处理退出流程。

## 选项

无。本类不直接定义配置选项。

## 使用方式

### 初始化退出异常常量

```php
use DuckPhp\Core\ExitException;

ExitException::Init();
```

### 条件抛出退出异常

```php
ExitException::ThrowOn($shouldExit, '程序退出', 0);
```

### 捕获退出异常

```php
try {
    // 某些框架代码
} catch (DuckPhp\Core\ExitException $e) {
    // 处理退出逻辑，例如输出响应
}
```

## 配置示例

无。

## 注意事项

1. `Init()` 只会定义一次 `__EXIT_EXCEPTION` 常量，重复调用不会覆盖。
2. 该异常通常由框架内部使用，普通业务代码不建议直接抛出。
3. 通过 `DuckPhpSystemException` 继承的 `ThrowOn()` 方法，可以在需要退出时统一抛出异常。

## 方法列表

### 公共方法

    public static function Init()
定义 `__EXIT_EXCEPTION` 常量（仅首次调用生效）

    public static function ThrowOn($flag, $message, $code = 0)
继承自 `DuckPhpSystemException` 使用的 `ThrowOnTrait`，条件为真时抛出异常

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md)
