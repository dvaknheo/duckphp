# DuckPhp\Ext\EventManager

事件管理组件。

## 简介

`EventManager` 组件提供了一个简单的事件监听和触发机制。它允许在应用的不同阶段注册事件回调，并在事件触发时按注册顺序依次执行。

该组件默认通过 `DuckPhp\DuckPhp` 的 `ext` 选项自动加载。

## 使用方式

### 静态方法

```php
use DuckPhp\Ext\EventManager;

// 监听事件
EventManager::OnEvent('user.login', function ($userId) {
    echo "User {$userId} logged in\n";
});

// 触发事件
EventManager::FireEvent('user.login', 123);

// 获取所有事件
$events = EventManager::AllEvents();

// 移除事件
EventManager::RemoveEvent('user.login');
```

### 使用类名作为事件名

事件名也支持数组形式，通常用于 `类名::事件名`：

```php
use DuckPhp\GlobalUser\GlobalUser;

EventManager::OnEvent([GlobalUser::class, GlobalUser::EVENT_LOGINED], function ($user) {
    // ...
});

EventManager::FireEvent([GlobalUser::class, GlobalUser::EVENT_LOGINED], $user);
```

## 事件回调

事件回调可以是任意可调用对象。触发事件时，会依次调用所有监听该事件的回调：

```php
EventManager::OnEvent('app.init', function () {
    // 第一个回调
});

EventManager::OnEvent('app.init', function () {
    // 第二个回调
});

EventManager::FireEvent('app.init');  // 两个回调都会执行
```

## 移除事件

移除单个回调：

```php
$callback = function () { /* ... */ };
EventManager::OnEvent('my.event', $callback);
EventManager::RemoveEvent('my.event', $callback);
```

移除整个事件的所有回调：

```php
EventManager::RemoveEvent('my.event');
```

## 注意事项

1. 同一个回调不会被重复注册到同一事件。
2. 事件触发时按注册顺序执行回调。
3. 事件名内部统一转为字符串处理，数组形式会拼接为 `Class::EVENT`。
4. 事件管理器没有返回值，回调结果不传递给后续回调。

## 方法列表

### 公共方法

    public static function OnEvent($event, $callback)
注册事件监听器

    public static function FireEvent($event, ...$args)
触发事件，依次调用所有监听器

    public static function AllEvents()
获取所有已注册事件

    public static function RemoveEvent($event, $callback = null)
移除事件监听器。`$callback` 为 `null` 时移除整个事件

    public function on($event, $callback)
实例方法版本，注册事件监听器

    public function fire($event, ...$args)
实例方法版本，触发事件

    public function all()
实例方法版本，获取所有事件

    public function remove($event, $callback = null)
实例方法版本，移除事件监听器

### 受保护方法

    protected function eventName($event): string
将事件名统一转换为字符串

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\App](Core-App.md)
