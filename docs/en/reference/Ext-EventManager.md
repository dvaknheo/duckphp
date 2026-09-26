# DuckPhp\Ext\EventManager

## Introduction

`EventManager` is a simple event manager extension: it stores listeners as `event name => [callbacks…]` and supports registering (`on`), firing (`fire`), querying (`all`) and removing (`remove`), plus a set of static convenience entries (`OnEvent/FireEvent/AllEvents/RemoveEvent`). An event name can be a string or an array (arrays are joined into a string key with `::`).

Positioning difference from `Component\GlobalEvent`: `GlobalEvent` targets "global events" (switched via App options); `EventManager` is a componentized standalone implementation you can pull in and use on your own.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class EventManager extends DuckPhp\Core\ComponentBase`

## Usage

```php
use DuckPhp\Ext\EventManager;

EventManager::OnEvent('order.created', function ($orderId) { /* … */ });
EventManager::OnEvent('order.created', $listener2);

EventManager::FireEvent('order.created', 42);   // calls the callbacks one by one in registration order

$all = EventManager::AllEvents();               // ['order.created' => [callable…]]
EventManager::RemoveEvent('order.created');     // removes all listeners of this event
EventManager::RemoveEvent('order.created', $listener2); // removes only one callback
```

## Caveats

- `on()`: registering the same callback twice for the same event is skipped (`in_array` dedupe).
- `fire()`: silently returns when the event has no listeners; listener callbacks are called one by one as `(…)($args)`; return values are not aggregated.
- `remove()`: without `$callback` the whole event is cleared; with a callback it filters by callback (loose comparison `!=`).
- `eventName()`: array events (e.g. `[Class::class,'method']`) are joined into a `Class::method` style string.

## Methods

### Public methods

    public static function OnEvent($event, $callback)
Statically register a listener (equivalent to `on`).

    public static function FireEvent($event, ...$args)
Statically fire an event (equivalent to `fire`).

    public static function AllEvents()
Statically get the whole event table.

    public static function RemoveEvent($event, $callback = null)
Statically remove listeners (equivalent to `remove`).

    public function on($event, $callback)
Registers an event listener (appended after dedupe).

    public function fire($event, ...$args)
Fires an event: calls all callbacks of the event in order.

    public function all()
Returns the whole event table (event name => array of callbacks).

    public function remove($event, $callback = null)
Removes an event: clears the whole event without a callback, filters out the equal callback otherwise.

### Protected methods

    protected function eventName($event): string
Normalizes the event name: arrays are joined with `::` into a string.

## Related links

- [DuckPhp\Component\GlobalEvent](Component-GlobalEvent.md) — the global event component
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class
