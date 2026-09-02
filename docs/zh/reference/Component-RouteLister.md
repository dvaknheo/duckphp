# DuckPhp\Component\RouteLister

`DuckPhp\Component\RouteLister` 路由枚举组件。

## 简介

`RouteLister` 是 `DuckPhp\Component` 命名空间下的 类，由 DuckPhp 框架提供。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `classes_to_get_controller_path` | `[]` |  |

## 使用方式

### 基本用法

```php
use DuckPhp\Component\RouteLister;

$obj = RouteLister::_();
```

## 注意事项

1. 本类为框架内部或扩展组件，通常由框架自动加载。
2. 如需自定义行为，可继承本类并覆盖相应方法。

## 方法列表

### 公共方法

    function pathInfoFromClassAndMethod($class, $method, $adjuster = null)

    function listAll(bool $with_children = true, bool $only_controller = false, bool $only_admin = false, bool $only_user = false): array
List all routes as recordset.
 Order: rewrite_map, route_map_important, controller routes, route_map.
 @return array<int, array<string, mixed>>

### 受保护方法

    function doControllerClassAdjust(string $first, string $method): array

    function getAllControllerClasses(): array

    function getControllerMethods(string $full_class, ?callable $adjuster = null): array

    function listControllerRows(bool $only_admin, bool $only_user): array
@return array<int, array<string, mixed>>

    function parseRouteMapCallback(string $callback): array
@return array{0: string, 1: string}

    function isSubclassOf(string $class, string $interface): bool

## 相关链接

- [中文参考手册目录](index.md)
