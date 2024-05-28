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
- 全局用户组件 [DuckPhp\Component\GlobalUser](Component-GlobalUser.md);
- 分页组件 [DuckPhp\Component\Pager](Component-Pager.md);
- 相位代理 [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md);
- Redis管理器 [DuckPhp\Component\RedisManager](Component-RedisManager.md);
- 兼容路由组件 [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md);
- 路由重写组件 [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md);
- 路由映射组件 [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md);
- Sqldump组件 [DuckPhp\Component\SqlDumper](Component-SqlDumper.md);
- 核心应用 [DuckPhp\Core\App](Core-App.md);
- 命令行组件 [DuckPhp\Core\Console](Core-Console.md);


## 选项

### 

        'ext_options_file_enable' => true,
额外配置文件

        'ext_options_file' => 'config/DuckPhpApps.config.php',
配置文件名字
        
        'path_info_compact_enable' => false,
PATH_INFO 兼容模式
        
        'class_user' => '',
用户类名，设置这个类以实现默认的用户类

        'class_admin' => '',
管理员类名，设置这个类以实现默认的管理员类
        
        'session_prefix' => null,
Session 前缀

        'table_prefix' => null,
数据库表前缀
        
        'sql_dump_enable' => false,

        'install_need_db' => false,

        'install_need_redis' => false,

### 隐含扩展选项

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
        'path_info_compact_enable' => false,

```



        'ext' => [

            RouteHookRouteMap::class => true,

            RouteHookRewrite::class => true,

            RouteHookResource::class => true,

            RouteHookPathInfoCompat::class => false,
            RouteHookCheckStatus::class => true,

        ],

### 继承 [DuckPhp\Core\App](Core-App.md) 的默认选项。
详细查看 [DuckPhp\Core\App](Core-App.md)的文档。

        'path_runtime' => 'runtime',
可写目录

        'alias' => null,
别名，目前只用于视图目录

        'default_exception_do_log' => true,
发生异常时候记录日志


        'close_resource_at_output' => false,
输出时候关闭资源输出（仅供第三方扩展参考

        'html_handler' => null,
HTML编码函数

        'lang_handler' => null,
语言编码回调

        'error_404' => null,          //'_sys/error-404',
404 错误处理 的View或者回调，仅根应用有效

        'error_500' => null,          //'_sys/error-500',
500 错误处理 View或者回调，仅根应用有效


        'path_log' => 'runtime',

        'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',

        'log_prefix' => 'DuckPhpLog',


        'path_view' => 'view',

        'view_skip_notice_error' => true,

        'superglobal_auto_define' => false,

### 继承 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) 的默认选项。
详细查看 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)的文档。

        'path' => null,
基准目录，如果没设置，将设置为`$_SERVER['SCRIPT_FILENAME']`的父级目录。

        'override_class' => null,
如果这个选项的类存在，则且新建 `override_class` 初始化

        'override_class_from' => null,
`override_class`切过去的时候会在此保存旧的`override_class`

        'cli_enable' => true,
启用命令行模式

        'is_debug' => false,
调试模式， 用于 `IsDebug()` 方法。

        'ext' => [],
扩展，保存 类名=>选项对

        'skip_404' => false,
不处理 404 ，用于配合其他框架使用。

        'on_init' => null,
初始化完成后处理回调

        'namespace' => null,
基准命名空间，如果没设置，将设置为当前类的命名空间的上级命名空间，如MyProject\\System\\App => MyProject

        'skip_exception_check' => false,
不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用

        'setting_file' => 'config/DuckPhpSettings.config.php',
设置文件名。仅根应用有效

        'setting_file_enable' => true,
使用设置文件: $path/$path_config/$setting_file.php 仅根应用有效

        'use_env_file' => false,
使用 .env 文件。 仅根应用有效
打开这项，可以读取 path 选项下的 env 文件

        'setting_file_ignore_exists' => true,
如果设置文件不存在也不报错 仅根应用有效

        'exception_reporter' => null,
异常报告类

        'exception_reporter_for_class' => null,
异常报告仅针对的异常

        'database_driver' => '',

        'cli_command_with_app' => true,

        'cli_command_with_common' => true,

        'cli_command_with_fast_installer' => false,



### 来自控制器的选项

        'namespace_controller' => 'Controller',

        'controller_path_ext' => '',

        'controller_welcome_class' => 'Main',

        'controller_welcome_class_visible' => false,

        'controller_welcome_method' => 'index',

        'controller_class_base' => '',

        'controller_class_postfix' => 'Controller',

        'controller_method_prefix' => 'action_',

        'controller_prefix_post' => 'do_',

        'controller_class_map' => [],

        'controller_resource_prefix' => '',

        'controller_url_prefix' => '',
### 来自运行时的选项
        'use_output_buffer' => false,

        'path_runtime' => 'runtime',

### 来自控制台的选项
        

### 来自异常管理器的选项

## 说明

也许你想从这个入口类了解 DuckPhp 的所有配置，但这个类只是扩展自 DuckPhp 类只是弥补了 [DuckPhp\Core\App](Core-App.md) 缺失的方法。
具体的方法在 DuckPhp\Core\App 里。主要流程在 [DuckPhp\Core\KernelTrait](Core-KernelTrait.md)  里

## 公开方法

### 方法
    protected function initComponents(array $options, object $context = null)
override 初始化的时候把相关额外组件初始化

    public function install($options, $parent_options = [])
安装

## 详解

App 类，继承了 DuckPhp\Core\App 的功能，在默认配置里

+ 如果你要看有什么选项，查看  Kernel 和 App  文档
+ 如果你要看核心流程，查看  Kernel  文档
+ 如果你要看有什么方法，查看 App 文档。

    
## 继承自 DuckPhp\Core\App 的方法


## 完毕




    protected function prepareComponents()

    protected function isLocalDatabase()

    protected function isLocalRedis()



## 完毕