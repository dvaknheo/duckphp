# 事件

[toc]

DuckPhp 在主流程中并不使用事件。

DuckPhp 提供了事件系统给第三方使用

## 相关类

[DuckPhp\Component\EventManager](ref/Component-EventManager.md)

## 说明


一般而言是不需要事件的。作为第三方，如果你想让使用者不改你的代码执行，可以使用事件解除耦合

解耦是好事，但是事件满天飞却不是一件很好的事情。



App::OnEvent() 绑定事件， 支持一个事件绑定多个回调，先绑定的事件会先处理。

DuckPhp 的事件名称规范是： 

触发事件的类名加类内常量，用 "::"分割
如：

```
static::class .'::'.static::EVNET_CREATE;
```
这样以方便找到事件在哪里定义的

App::FireEvent() 触发事件

用 App::RemoveEvent() 移除事件

DuckPhp 的事件系统是 一对多的

如果你想调试

可以 `__var_dump(DuckPhp\Component\EventManager::G()->all());`