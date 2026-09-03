# DuckPhp\Ext\EventManager

## 简介

`EventManager` 是简单的事件管理器扩展：以 `事件名 => [回调…]` 存放监听，支持注册（`on`）、触发（`fire`）、查询（`all`）、移除（`remove`），并提供一套静态便捷入口（`OnEvent/FireEvent/AllEvents/RemoveEvent`）。事件名可以是字符串或数组（数组会以 `::` 拼接为字符串键）。

与 `Component\GlobalEvent` 的定位差异：`GlobalEvent` 面向“全局事件”（供 App 选项开关配置）；`EventManager` 是组件化的独立实现，可自行引入使用。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class EventManager extends DuckPhp\Core\ComponentBase`

## 使用方式

```php
use DuckPhp\Ext\EventManager;

EventManager::OnEvent('order.created', function ($orderId) { /* … */ });
EventManager::OnEvent('order.created', $listener2);

EventManager::FireEvent('order.created', 42);   // 按注册顺序逐个调用回调

$all = EventManager::AllEvents();               // ['order.created' => [callable…]]
EventManager::RemoveEvent('order.created');     // 移除该事件的全部监听
EventManager::RemoveEvent('order.created', $listener2); // 只移除某个回调
```

## 注意事项

- `on()`：同一事件下重复注册同一回调会被跳过（`in_array` 判重）。
- `fire()`：事件无监听时静默返回；监听回调逐个 `(…)($args)` 调用，返回值不聚合。
- `remove()`：不传 `$callback` 时整个事件清空；传了则按回调过滤（宽松比较 `!=`）。
- `eventName()`：数组事件（如 `[Class::class,'method']`）拼成 `Class::method` 形式字符串。

## 方法列表

### 公共方法

    public static function OnEvent($event, $callback)
静态注册监听（等价 `on`）。

    public static function FireEvent($event, ...$args)
静态触发事件（等价 `fire`）。

    public static function AllEvents()
静态取全部事件表。

    public static function RemoveEvent($event, $callback = null)
静态移除监听（等价 `remove`）。

    public function on($event, $callback)
注册事件监听（去重后追加）。

    public function fire($event, ...$args)
触发事件：依次调用该事件的全部回调。

    public function all()
返回全部事件表（事件名 => 回调数组）。

    public function remove($event, $callback = null)
移除事件：无回调清空整事件，有回调则过滤掉相等的回调。

### 受保护方法

    protected function eventName($event): string
规范化事件名：数组用 `::` 拼成字符串。

## 相关链接

- [DuckPhp\Component\GlobalEvent](Component-GlobalEvent.md) — 全局事件组件
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类
