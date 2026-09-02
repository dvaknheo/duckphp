# DuckPhp\Component\GlobalEvent

`DuckPhp\Component\GlobalEvent` 全局事件组件。

## 简介

`GlobalEvent` 是 `DuckPhp\Component` 命名空间下的 类，由 DuckPhp 框架提供。

## 选项

无。

## 使用方式

### 基本用法

```php
use DuckPhp\Component\GlobalEvent;

$obj = GlobalEvent::_();
```

## 注意事项

1. 本类为框架内部或扩展组件，通常由框架自动加载。
2. 如需自定义行为，可继承本类并覆盖相应方法。

## 方法列表

### 公共方法

    function on($event, $callback)

    function globalOn($event, ?string $phase, callable $callback)

    function fire($event, ...$args)

    function all()

    function remove($event, ?string $phase = null, $callback = null)

## 相关链接

- [中文参考手册目录](index.md)
