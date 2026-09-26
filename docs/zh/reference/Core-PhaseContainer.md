# DuckPhp\Core\PhaseContainer

## 简介

`PhaseContainer` 是 DuckPHP 框架的“Phase 容器”，也是 `SingletonExTrait::_()` 背后真正负责实例归属的容器。

它以“容器名(Phase) + 类名”为维度，在若干并行空间内存放对象实例：

- `containers`：一张 `容器名 => (类名 => 实例)` 的大表；
- `current`：当前生效的 Phase/容器名；
- `default`：共享实例所在的默认容器名（框架里通常是 `#shared`）；
- `shared_classes`：被标记为“可跨 Phase 共享”的类清单（见下）。

当需要取某类的实例（`Class::_()`）时，`_GetObject()` 的查找顺序是：**当前 Phase 里的实例 → 若该类在 `shared_classes` 中，则到 default 容器找共享实例 → 都找不到则自动 `new` 并登记到目标容器**。这构成了“根应用与子应用各自独立、又有少量可共享组件”的多实例格局。

DuckPHP 的根 Phase 名常为 `''`（空串），共享容器默认名在 `KernelTrait` 记为 `#shared`。子应用的 Phase 名形如 `父Phase:命名空间/名称`。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class PhaseContainer`
- 关键属性：`public static $instance`；实例属性 `$containers`、`$current`、`$default`、`$shared_classes`。

## 使用方式

`KernelTrait`/组件内部使用；业务代码一般只接触 `_()` 与 `GetObject()` 的封装。仍可手工驱动：

```php
use DuckPhp\Core\PhaseContainer;

// 手动设“当前容器”
$c = PhaseContainer::_();
$c->setCurrentContainer('myapp');

// 挂/查共享类
$c->addSharedClasses([MyShared::class => true]);

// 取对象（不存在则创建到当前位置；是共享类则在 default 中找或建）
$obj = PhaseContainer::GetObject(MyShared::class);

// 取某 Phase 容器中的对象
$other = $c->getClassOfContainer(MyShared::class, 'phase-name');
```

测试/隔离常这样清空：

```php
PhaseContainer::RestAllContainerForTesting(); // 用全新容器替换静态 $instance
```

## 注意事项

- 默认容器（`#shared`）中的实例是全局共享；要被共享的类必须通过 `addSharedClasses($classes)` 声明，否则 `_GetObject()` 不会回退去 default 容器找。
- 手动 `_(?object $object)` 只会用于替换**静态 `$instance`（容器自身）**；要登记“某类实例”请调用传入对象的 `GetObject($class, $object)` / `createLocalObject`。
- `createLocalObject` / `removeLocalObject` 总是作用在 `current` 上，即它让实例紧跟当前 Phase（局部）。
- `dumpAllObject()` / `Dump()` 仅供调试查看容器布局（打印会把 `*` 标在共享类上）。

## 方法列表

### 公共静态方法

    public static function _ (?object $object = null)
返回容器单一实例；传入对象时把静态 $instance 设为该对象并返回它（便于测试替换整个容器）。

    public static function GetObject(string $class, ?object $object = null)
便捷取实例：委托  `_()->_GetObject($class,$object)`；`$object` 非空时用于登记/替换对应 实例。

    public static function RestAllContainerForTesting()
用全新容器替换静态 $instance——一般仅供测试从干净状态开始。

    public static function Dump()
输出当前容器全部状态（current/default/shared_classes/containers）到 stdout，便于调试。

### 共享实例方法

    public function _GetObject(string $class, ?object $object = null): object
核心查找+创建：先在 current 容器找；该 Class 在 shared_classes 时再去 default 找；没有再（在目标容器）new/登记实例。

    public function setDefaultContainer($class)
设置默认（共享）容器名 —— 即放共享实例的地方。

    public function addSharedClasses($classes)
把若干类标记为共享（键为类名、值为真即可）。
例 `addSharedClasses([Some::class => true])`。

    public function removeSharedClasses($classes)
从共享名单移除这些类（值为数组列表）。

    public function setCurrentContainer($container)
设置当前 Phase/容器名。

    public function getCurrentContainer()
返回当前容器名。

    public function issetContainer($phase)
判断某容器(Phase)是否已存在（是否已建对象）。

    public function createLocalObject($class, $object = null)
在当前容器中创建（或放入给定对象）并登记，返回对象；实例紧跟当前 Phase，不查默认容器。

    public function removeLocalObject($class)
从当前容器移除该类实例。

    public function getClassOfContainer($class, $phase = '')
取某个容器中的某类实例（不查找、不创建；没有返回 null）。

    public function dumpAllObject()
完整 dump（见 Dump 说明）。

### 受保护方法

    protected function createObject(string $class): object
对给定类 `new` 一个实例（子类可替换建实例方式）。

    protected function createObjectToContainer($container_name, $class, $object)
在指定容器登记并返回实例（对象为空时先 createObject）。

    protected function getObjectInContainer($container_name, $class, $object)
在某容器中按“是否有实例”返回；传入 $object 时把其覆盖登记到该容器。

## 相关链接

- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — 依赖本容器实现的 `_()` 单例入口
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — 设置根 Phase(`''`)与共享容器名(`#shared`)，并调用 initContainer/addSharedClasses 装配
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 接触容器的主要类
