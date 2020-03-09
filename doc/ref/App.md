# App

## 简介
总入口类，父类 Core\App

## 选项
'rewrite_map' => [],
'route_map_important' => [],
'route_map' => [],
'db_before_query_handler' => null,
'log_sql' => false,
'use_short_functions' => false,

'ext' => [
    //'DuckPhp\Ext\PluginForSwooleHttpd' => true,
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
]
## 公开方法


## 详解
App 类，继承了 Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容

