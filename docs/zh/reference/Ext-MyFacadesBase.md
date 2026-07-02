# DuckPhp\Ext\MyFacadesBase

> ⚠️ 警告：该扩展是实验性的或已废弃，不建议在新项目中使用。

## 简介

`MyFacadesBase` 是所有自动加载 facade 的基类。它通过 `__callStatic` 将静态方法调用转发到 `MyFacadesAutoLoader` 解析的真实对象方法上。

该扩展通常与 `MyFacadesAutoLoader` 配合使用，属于实验性实现，不建议在新项目中使用。

## 选项

无。

## 使用方式

### 作为基类自动生成

`MyFacadesBase` 通常不需要手动继承。`MyFacadesAutoLoader` 在自动加载 facade 类时会动态生成继承自 `MyFacadesBase` 的类。

```php
use MyFacades\UserService;

UserService::getUserById(123);
```

### 手动继承（不推荐）

```php
namespace MyFacades;

class UserService extends \DuckPhp\Ext\MyFacadesBase
{
}

UserService::getUserById(123);
```

## 配置示例

无。

## 注意事项

1. 静态方法调用会通过 `MyFacadesAutoLoader::_()->getFacadesCallback()` 解析真实对象和方法。
2. 如果解析失败，会抛出 `ErrorException` 异常，提示 `BadCall`。
3. 真实目标类需要实现 `_()` 单例方法。
4. 该扩展是实验性的，动态 facade 机制可能影响代码可维护性。

## 方法列表

### 公共方法

    public function __construct()
构造函数，当前为空实现。

    public static function __callStatic($name, $arguments)
将静态方法调用转发到真实对象。如果找不到对应回调，抛出 `ErrorException`。

## 相关链接

- [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
