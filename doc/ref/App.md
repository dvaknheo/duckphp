# App

## 简介
总入口类，父类 Core\App
## 依赖关系
`DuckPhp\App` 
    1. 继承 [Core\App]()
        + 继承 Core\Kernel()
    2. 使用 [Ext\Misc](ref/Ext-Misc.md)
    3. 使用 [Ext\SimpleLogger](ref/Ext-SimpleLogger.md)
    4. 使用 [Ext\DBManager](ref/Ext-DBManager.md)
    5. 使用 [Ext\RouteHookRewrite](ref/Ext-RouteHookRewrite.md)
    6. 使用 [Ext\RouteHookRouteMap](ref/Ext-RouteHookRouteMap.md)
    
    7. 参考 [Ext\StrictCheck]()
    8. 参考 [Ext\RouteHookOneFileMode]()
    9. 参考 [Ext\RouteHookDirectoryMode]()
    10. 参考 [Ext\RouteHookOneFileMode]()
## 选项

'db_before_query_handler' => null,

    内部选项，将会填充为 [static::class, 'OnQuery'] 回调。
'log_sql' => false,

    记录 sql ，配合 db_before_query_handler
'use_short_functions' => false,

    打开这个选项，使用 `\e` 函数。
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
    处理 use_short_functions,
public function _Pager($object = null)

    重写了 `Core\App` 的 _Pager 方法，填充默认 DuckPhp\ExtPager 对象。
public static function OnQuery($sql, ...$args)

    DB查询钱的回调
public function _OnQuery($sql, ...$args)

    // OnQuery 的实现。
## 详解

App 类，继承了 Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容

