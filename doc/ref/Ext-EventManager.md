# DuckPhp\Ext\EventManager

## 简介
事件管理器， 这个类是一对多绑定。
## 选项
无选项
## 方法

public static function OnEvent($event, $callback)

    绑定事件
public static function FireEvent($event, ...$args)

    触发事件
    
public function on($event, $callback)

    实际执行函数
public function fire($event, ...$args)

    实际执行函数
public static function AllEvents()

    获取所有事件
public static function RemoveEvent($event, $callback = null)

    移除事件
## 例子

```php
EventManager::OnEvent('MyEvent',function(...$args){ var_dump($args);});
EventManager::FireEvent('MyEvent','A','B','C');
EventManager::FireEvent('NoExist','A','B','C');

```

## 详细解答
App::OnEvent 绑定事件
App::FireEvent 触发时间
用 App::RemoveEvent 移除事件
DuckPhp 的事件系统是 一对多的