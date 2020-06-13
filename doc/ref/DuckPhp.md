# App

## 简介
总入口类，父类 Core\DuckPhp
## 依赖关系
`DuckPhp\DuckPhp` 
    1. 继承 [Core\App](Core-App.md)
        + Trait Core\Kernel(Core-Kernel.md)
    2. 使用 [Ext\Misc](Ext-Misc.md)
    3. 使用 [Ext\SimpleLogger](Ext-SimpleLogger.md)
    4. 使用 [Ext\DBManager](Ext-DBManager.md)
    5. 使用 [Ext\RouteHookRouteMap](Ext-RouteHookRouteMap.md)
    

## 选项
继承 [Core\Kernel](Core-Kernel.md) 的默认选项。

    详细查看 [Core\Kernel](Core-Kernel.md) 的文档
'db_before_query_handler' => null,

    内部选项，将会填充为 [static::class, 'OnQuery'] 回调。
'log_sql' => false,

    记录 sql ，配合 db_before_query_handler

'ext' => \[\]



    扩展选项
    默认为：
```
[
    // No Use 'DuckPhp\Ext\PluginForSwooleHttpd' => true,
    'DuckPhp\Ext\Misc' => true,
    'DuckPhp\Ext\SimpleLogger' => true,
    'DuckPhp\Ext\DBManager' => true,
    'DuckPhp\Ext\RouteHookRewrite' => true,
    'DuckPhp\Ext\RouteHookRouteMap' => true,
    
    'DuckPhp\Ext\StrictCheck' => false,
    'DuckPhp\Ext\RouteHookOneFileMode' => false,
    'DuckPhp\Ext\RouteHookDirectoryMode' => false,
    
    'DuckPhp\Ext\RedisManager' => false,
    'DuckPhp\Ext\RedisSimpleCache' => false,
    'DuckPhp\Ext\DBReusePoolProxy' => false,
    'DuckPhp\Ext\FacadesAutoLoader' => false,
    'DuckPhp\Ext\Lazybones' => false,
    'DuckPhp\Ext\Pager' => false,
],
```

## 方法
public function __construct()

    载入默认选项
protected function onInit()

    处理 `log_sql`，如果 log_sql 不为 false 则联立 OnQuery,
public function _Pager($object = null)

    重写了 `Core\App` 的 _Pager 方法，填充默认 DuckPhp\Ext
    Pager 对象。
public static function OnQuery($sql, ...$args)

    DB查询钱的回调
public function _OnQuery($sql, ...$args)

    // OnQuery 的实现。
## 详解

App 类，继承了 Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容