# DuckPhp\Component\EventManager
[toc]

## 简介
事件管理器组件， 这个类是一对多绑定。
## 选项

无选项

## 方法

    public static function OnEvent($event, $callback)
    public function on($event, $callback)
绑定事件

    public static function FireEvent($event, ...$args)
    public function fire($event, ...$args)
触发事件

    public static function AllEvents()
    public function all()

获取所有事件

    public static function RemoveEvent($event, $callback = null)
    public function remove($event, $callback = null)

移除事件， 如果指定 $callback 则移除 $event 中等于callback的事件处理

## 例子

```php
<?php

EventManager::OnEvent('MyEvent',function(...$args){ var_dump($args);});
EventManager::FireEvent('MyEvent','A','B','C');
EventManager::FireEvent('NoExist','A','B','C');

```

## 详细解答

App::OnEvent 绑定事件， 支持一个事件绑定多个回调，先绑定的事件会先处理。

App::FireEvent 触发事件

用 App::RemoveEvent 移除事件

DuckPhp 的事件系统是 一对多的


