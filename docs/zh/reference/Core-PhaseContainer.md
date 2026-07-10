# DuckPhp\Core\PhaseContainer

相位/多应用容器管理。

## 简介

`PhaseContainer` 是 DuckPHP 的可变单例容器。它负责管理所有使用 `SingletonTrait` 的类的实例，支持“默认容器”与“当前容器”两层隔离，并可为某些类设置“公共”作用域，使其在默认容器中被共享。

## 选项

无。本类不直接定义配置选项。

## 使用方式

### 获取容器实例

```php
use DuckPhp\Core\PhaseContainer;

$container = PhaseContainer::GetContainer();
```

### 设置默认容器

```php
PhaseContainer::GetContainer()->setDefaultContainer(\MyApp\System\MyApp::class);
```

### 切换当前容器

```php
PhaseContainer::GetContainer()->setCurrentContainer(\ApiApp\System\ApiApp::class);
```

### 设置公共类

公共类会存储在默认容器中，被所有容器共享。

```php
PhaseContainer::GetContainer()->addPublicClasses([
    \DuckPhp\Component\Logger::class,
    \DuckPhp\Component\Configer::class,
]);
```

### 管理当前容器内的局部对象

```php
$container = PhaseContainer::GetContainer();

// 在当前容器中创建/覆盖实例
$container->createLocalObject(\MyApp\Service\UserService::class, $service);

// 移除当前容器中的实例
$container->removeLocalObject(\MyApp\Service\UserService::class);
```

### 重置容器

```php
PhaseContainer::ResetContainer();
```

### 调试输出

```php
PhaseContainer::GetContainer()->dumpAllObject();
```

## 配置示例

无。

## 注意事项

1. 同一类在不同“当前容器”下可以拥有独立实例；公共类则始终使用默认容器中的实例。
2. `SingletonTrait::_()` 内部会调用 `PhaseContainer::GetObject()`，因此不要直接 new 实例覆盖全局状态，除非明确需要。
3. `ResetContainer()` 会清空整个容器状态，通常用于测试或应用重启场景。
4. `GetContainer()` 返回的是单例容器，所有操作对该容器全局生效。

## 方法列表

### 公共方法

    public static function ResetContainer()
重置容器实例为全新状态

    public static function ReplaceSingletonImplement()
保留的单例替换入口（当前实现直接返回 true）

    public static function GetObject($class, $object = null)
获取或设置指定类的实例，供 `SingletonTrait::_()` 调用

    public static function GetContainerInstanceEx($object = null)
获取或替换容器单例

    public static function GetContainer()
获取当前容器实例

    public function _GetObject(string $class, $object = null)
在当前容器或默认容器中获取/设置实例

    public function setDefaultContainer($class)
设置默认容器标识

    public function addPublicClasses($classes)
批量添加公共类

    public function removePublicClasses($classes)
批量移除公共类

    public function setCurrentContainer($container)
设置当前容器标识

    public function getCurrentContainer()
获取当前容器标识

    public function createLocalObject($class, $object = null)
在当前容器中创建或覆盖实例

    public function removeLocalObject($class)
移除当前容器中的指定实例

    public function dumpAllObject()
输出当前容器状态（调试用）

### 受保护方法

    protected function createObject(string $class): object
实例化指定类

## 相关链接

- [DuckPhp\Core\SingletonTrait](Core-SingletonTrait.md)
