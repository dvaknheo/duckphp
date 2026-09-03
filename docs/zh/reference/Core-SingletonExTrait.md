# DuckPhp\Core\SingletonExTrait

## 简介

`SingletonExTrait` 是 DuckPHP 里“单例式访问”的常见来源 Trait。通过 `use SingletonExTrait`，一个类可以直接获得 `类名::_()` 这样的静态入口，由 `PhaseContainer` 统一管理并返回该类的（当前 Phase 内）实例。

它与经典“静态单例属性”不同：**实例的归属和管理完全交给 `PhaseContainer`**，并且支持传入 `$object` 来登记/替换实例。由于 Phase 容器按“Phase + 类名”区隔实例，同一类在不同 Phase 下可以持有不同对象——这也是子应用/多租户各自独立实例得以实现的基础。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`trait SingletonExTrait`
- 使用方：`DuckPhp\Core\ComponentBase`（大多数组件经由它获得 `_()`）；各 `Helper` Trait 也会引入它以便对外暴露 `_()` 便捷。

## 使用方式

### 让类拥有 `_()` 入口

```php
use DuckPhp\Core\SingletonExTrait;

class MyService
{
    use SingletonExTrait;
    // ... 业务
}

$svc = MyService::_();          // 取当前 Phase 里的实例（没有则创建并登记）
```
放在框架中时，实际返回时经由 `PhaseContainer::GetObject(static::class, $object)`；多数字段不必亲自 new。

### 登记一个已构造实例

```php
$existing = new MyService;
MyService::_($existing);        // 返回（并把 $existing 登记为当前 Phase 实例）$existing
```
这样可替换/预置某个组件实例（常用于测试或框架替换组件的实现）。

## 注意事项

- 这是静态入口所在；如需“可变单例/与 container 解耦”，本 Trait 只是壳，真正语义由 `PhaseContainer` 决定（查找顺序：当前 Phase → 公共/父容器 → 自动创建）。
- `$object` 非空时会把传入对象登记后返回同一对象，从而“覆盖”此前实例而不会新建。
- 与 PHP 内置关键字/其它 singleton 无冲突；可被多个类重复 use。

## 方法列表

### 公共方法

    public static function _($object = null)
单例入口：返回（无参）或登记后返回（传对象）`static::class` 的当前 Phase 实例，内部委托 `PhaseContainer::GetObject()`。

## 相关链接

- [DuckPhp\Core\PhaseContainer](Core-PhaseContainer.md) — 真正的实例/单例查找与创建所在
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 引入本 Trait 的组件基类
