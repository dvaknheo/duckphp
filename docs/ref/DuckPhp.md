# DuckPhp\DuckPhp
[toc]

## 简介
总入口类。
## 依赖关系
`DuckPhp\DuckPhp` 继承 微框架入口类 [DuckPhp\Core\App](Core-App.md)，并且引用这些组件类用于增强功能
- 缓存组件 [DuckPhp\Component\Cache](Component-Cache.md);
- 配置器组件 [DuckPhp\Component\Configer](Component-Configer.md);
- 数据库管理组件 [DuckPhp\Component\DbManager](Component-DbManager.md);
- DuckPhp命令 [DuckPhp\Component\DuckPhpCommand](Component-DuckPhpCommand.md);
- 事件管理器组件 [DuckPhp\Component\EventManager](Component-EventManager.md);
- 额外选项加载器组件 [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md);
- 全局管理员组件 [DuckPhp\Component\GlobalAdmin](Component-GlobalAdmin.md);
- 使用 [DuckPhp\Component\GlobalUser](Component-GlobalUser.md);
- 使用 [DuckPhp\Component\Pager](Component-Pager.md);
- 使用 [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md);
- 使用 [DuckPhp\Component\RedisManager](Component-RedisManager.md);
- 使用 [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md);
- 使用 [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md);
- 使用 [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md);
- 使用 [DuckPhp\Core\App](Core-App.md);
- 使用 [DuckPhp\Core\Console](Core-Console.md);
- 使用 [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md);
- 使用 [DuckPhp\Core\Route](Core-Route.md);


## 选项

继承 [DuckPhp\Core\KernelTrait](Core-Trait.md) 的默认选项。详细查看 [DuckPhp\Core\KernelTrait](Core-Trait.md)的文档。

        'ext_options_file_enable' => false,
额外配置文件

        'ext_options_file' => 'config/DuckPhpApps.config.php',
配置文件名字
        
        'path_info_compact_enable' => false,
PATH_INFO 兼容模式
        
        'class_user' => null,
用户类名，设置这个类以实现默认的用户类

        'class_admin' => null,
管理员类名，设置这个类以实现默认的管理员类
        
        'session_prefix' => null,
Session 前缀

        'table_prefix' => null,
数据库表前缀
        

## 隐含扩展选项

        'path_config' => 'config',
配置类路径

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


并且做了以下更改

```php
        'path_info_compact_enable' =>false,

```


## 说明

也许你想从这个入口类了解 DuckPhp 的所有配置，但这个类只是扩展自 DuckPhp 类只是弥补了 [DuckPhp\Core\App](Core-App.md) 缺失的方法。
具体的方法在 DuckPhp\Core\App 里。主要流程在 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)  里

## 公开方法

### 方法
    public static function InitAsContainer($options, $welcome_handle = null)

    protected function initComponents(array $options, object $context = null)

    public function install($options, $parent_options = [])


## 详解

App 类，继承了 DuckPhp\Core\App 的功能，在默认配置里，还加载了其他 Ext 扩展的内容。


+ 如果你要看有什么选项，查看  Kernel 和 App  文档
+ 如果你要看核心流程，查看  Kernel  文档
+ 如果你要看有什么方法，查看 App 文档。

    
## 继承自 DuckPhp\Core\App 的方法


## 完毕
