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


        'ext_options_file_enable' => false,
        'ext_options_file' => 'config/DuckPhpApps.config.php',
        
        'path_info_compact_enable' => null,
        
        'class_user' => null,
        'class_admin' => null,
        
        'session_prefix' => null,
        'table_prefix' => null,
        
        'exception_reporter' => null,
        


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


    public static function setBeforeGetDbHandler($db_before_get_object_handler)

    public static function getRoutes()

    public static function assignRoute($key, $value = null)

    public static function assignImportantRoute($key, $value = null)
//


    protected function initComponents(array $options, object $context = null)


    public static function InitAsContainer($options)

    public function thenRunAsContainer($skip_404 = false, $welcome_handle = null)

    public function isInstalled()

    public function install($options)

    protected function bumpSingletonToRoot($oldClass, $newClass)

    public function _Admin($admin = null)

    public function _User($user = null)

    public function _AdminId()

    public function _UserId()




    public static function InitAsContainer($options)

    public function thenRunAsContainer($skip_404 = false, $welcome_handle = null)

    public function isInstalled()

    public function install($options)

    protected function bumpSingletonToRoot($oldClass, $newClass)

    public function _Admin($admin = null)

    public function _User($user = null)

    public function _AdminId()

    public function _UserId()

    public function _Event()

    public function _Pager($object = null)

        'path_config' => 'config',

        'database' => null,
数据库，单一数据库配置

        'database_list' => null,
数据库，多数据库配置

        'database_list_reload_by_setting' => true,
数据库，从设置里再入数据库配置

        'database_list_try_single' => true,
数据库，尝试使用单一数据库配置

        'database_log_sql_level' => 'debug',
数据库，记录sql 错误等级

        'database_log_sql_query' => false,
数据库，记录sql 查询

        'database_auto_extend_method' => true,
数据库，是否扩充方法至助手类
扩充 setBeforeGetDbHandler 入助手类。

        'database_class' => '',
数据库，默认为 Db::class。
如果你扩展了 DB 类，可以调用这个。更高级的可以调整 getDb 方法

        'redis' => null,

        'redis_list' => null,

        'redis_list_reload_by_setting' => true,

        'redis_list_try_single' => true,

        'controller_url_prefix' => '',

        'route_map_important' => [],

        'route_map' => [],

        'rewrite_map' => [],

        'path_info_compact_action_key' => '_r',

        'path_info_compact_class_key' => '',


    public function install($options, $parent_options = [])

    public function _Admin($new = null)

    public function _AdminData()

    public function _User($new = null)

    public function _UserData()

