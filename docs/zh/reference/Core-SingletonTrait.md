# DuckPhp\Core\SingletonTrait

可变单例 Trait。

## 简介

`SingletonTrait` 为类提供统一的单例访问入口 `_()`。它本身不维护实例，而是通过 `PhaseContainer::GetObject()` 实现按“相位/容器”隔离的可变单例，因此同一类在不同应用或不同相位下可以拥有不同实例。

## 选项

无。本 Trait 不直接定义配置选项。

## 使用方式

### 在类中使用 Trait

```php
namespace MyApp\Service;

use DuckPhp\Core\SingletonTrait;

class UserService
{
    use SingletonTrait;

    public function getUser()
    {
        return 'user';
    }
}
```

### 获取单例

```php
$service = UserService::_();

// 手动设置实例
$service = new UserService();
UserService::_($service);
```

### 多应用/相位隔离

`SingletonTrait` 配合 `PhaseContainer` 使用。当 `PhaseContainer` 切换当前容器时，通过 `_()` 获取到的实例会对应到当前容器，从而实现不同应用拥有独立单例。

## 配置示例

无。

## 注意事项

1. 本 Trait 只提供 `_()` 方法，真正的实例管理由 `PhaseContainer` 负责。
2. 在单元测试中可以通过 `PhaseContainer::ResetContainer()` 重置容器状态。
3. 不同相位下可以存在同一类的不同实例，编写跨相位代码时应注意实例作用域。

## 方法列表

### 公共方法

    public static function _($object = null)
访问或设置当前类的单例。不传参数时返回实例；传入对象时将该对象注册为当前类的单例。

## 相关链接

- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md)
