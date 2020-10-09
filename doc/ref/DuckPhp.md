# DuckPhp\DuckPhp
[toc]

## 简介
总入口类。
## 依赖关系
`DuckPhp\DuckPhp` 
    1. 继承 [DuckPhp\Core\App](Core-App.md)
    2. 使用 [DuckPhp\Ext\DBManager](Ext-DBManager.md)
    3. 使用 [DuckPhp\Ext\Pager](Ext-Pager.md)
    4. 使用 [DuckPhp\Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)
    5. 使用 [DuckPhp\Ext\RouteHookPathInfoCompat](Ext-RouteHookPathInfoCompat.md)
    

## 选项

继承 [Core\Kernel](Core-Kernel.md) 的默认选项。详细查看 [Core\Kernel](Core-Kernel.md) 的文档。

'ext' => \[\]
    

'default_exception_do_log' => true,
'default_exception_self_display' => true,

'ext' => [
    DbManager::class => true,
    RouteHookPathInfoCompat::class => true,
    RouteHookRouteMap::class => true,
],
## 说明
DuckPhp 类只是弥补了 DuckPhp 缺失的方法。
具体的方法在 Core\App 里
主要流程在 Core\Kernel 里
## 公开方法



## 重载保护方法

* 下划线开始的公开方法被视为内部方法 *



public function _Pager($object = null)

    重写了 `Core\App` 的 _Pager 方法，填充默认 DuckPhp\Ext
    Pager 对象。
## 详解

App 类，继承了 Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容。


