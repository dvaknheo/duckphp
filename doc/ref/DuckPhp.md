# DuckPhp

## 简介
总入口类
## 依赖关系
`DuckPhp\DuckPhp` 
    1. 继承 [Core\App](Core-App.md)
    2. 使用 [Ext\DBManager](Ext-DBManager.md)
    3. 使用 [Ext\Pager](Ext-Pager.md)
    4. 使用 [Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)
    5. 使用 [Ext\RouteHookPathInfoByGet](Ext-RouteHookPathInfoByGet.md)
    

## 选项

继承 [Core\Kernel](Core-Kernel.md) 的默认选项。详细查看 [Core\Kernel](Core-Kernel.md) 的文档。

'ext' => \[\]

    默认启用的扩展
    DBManager::class => true,
    RouteHookRouteMap::class => true,

## 公开方法

public function __construct()

    构造函数被重载，以在前面加上选项

## 重载保护方法

* 下划线开始的公开方法被视为内部方法 *

protected function initOptions()

    重写这个方法
    处理 `log_sql`，如果 log_sql 不为 false 则联立 OnQuery,

public function _Pager($object = null)

    重写了 `Core\App` 的 _Pager 方法，填充默认 DuckPhp\Ext
    Pager 对象。
## 详解

App 类，继承了 Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容。


