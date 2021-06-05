# DuckPhp\DuckPhp
[toc]

## 简介
总入口类。
## 依赖关系
`DuckPhp\DuckPhp` 继承 微框架入口类 [DuckPhp\Core\App](Core-App.md)，并且引用这些组件类用于增强功能
- 使用 [DuckPhp\Component\Cache](Component-Cache.md)
- 使用 [DuckPhp\Component\Console](Component-Console.md)
- 使用 [DuckPhp\Component\DbManager](Component-DbManager.md)
- 使用 [DuckPhp\Component\DuckPhpCommand](Component-DuckPhpCommand.md)
- 使用 [DuckPhp\Component\EventManager](Component-EventManager.md)
- 使用 [DuckPhp\Component\Pager](Component-Pager.md)
- 使用 [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
- 使用 [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)

## 选项

继承 [DuckPhp\Core\KernelTrait](Core-Trait.md) 的默认选项。详细查看 [DuckPhp\Core\KernelTrait](Core-Trait.md)的文档。

并且做了以下更改

```php
[
    'ext' => [
        DbManager::class => true,
        RouteHookRouteMap::class => true,
    ],
    'route_map_auto_extend_method' => false,
    'database_auto_extend_method' => false,
];
```
Console

Console::G()->options['cli_default_command_class'] = DuckPhpCommand::class;



DuckPhpCommand

RouteHookPathInfoCompat


## 说明

也许你想从这个入口类了解 DuckPhp 的所有配置，但这个类只是扩展自 DuckPhp 类只是弥补了 [DuckPhp\Core\App](Core-App.md) 缺失的方法。
具体的方法在 DuckPhp\Core\App 里。主要流程在 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)  里

## 公开方法
    public function __construct()
    
    protected function initAfterOverride(array $options, object $context = null)



## 详解

App 类，继承了 DuckPhp\Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容。


+ 如果你要看有什么选项，查看  Kernel 和 App  文档
+ 如果你要看核心流程，查看  Kernel  文档
+ 如果你要看有什么方法，查看 App 文档。

## 备忘
    //@override
    public function _Cache($object = null)
    {
        return Cache::G($object);
    }
    //@override
    public function _Pager($object = null)
    {
        return Pager::G($object);
    }
    //@override
    public function _Db($tag)
    {
        return DbManager::G()->_Db($tag);
    }
    //@override
    public function _DbCloseAll()
    {
        return DbManager::G()->_CloseAll();
    }
    //@override
    public function _DbForRead()
    {
        return DbManager::G()->_DbForRead();
    }
    //@override
    public function _DbForWrite()
    {
        return DbManager::G()->_DbForWrite();
    }
    //@override
    public function _Event()
    {
        return EventManager::G();
    }
    //@override
    public function _FireEvent($event, ...$args)
    {
        return EventManager::G()->fire($event, ...$args);
    }
    //@override
    public function _OnEvent($event, $callback)
    {
        return EventManager::G()->on($event, $callback);
    }



