# 选项参考
[toc]

## 选项索引
按字母顺序，加粗表示默认选项。

@forscript genoptions.php#options-md-alpha
+ ** 'all_config' => array ( ),  ** 

    所有配置   // [DuckPhp\Core\Configer](Core-Configer.md)
+  'api_server_404_as_exception' => false,   

    API服务器， 404 引发异常的模式   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+  'api_server_base_class' => '',   

    API服务器， 接口，或基类，  ~ 开始的表示是当前命名空间   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+  'api_server_class_postfix' => '',   

    API服务器， 类名后缀   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+  'api_server_namespace' => 'Api',   

    API服务器， 命名空间，配合 namespace选项使用   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+  'api_server_use_singletonex' => false,   

    API服务器，  使用可变单例模式，方便替换实现   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ ** 'autoload_cache_in_cli' => false,  ** 

    在 cli 下开启缓存模式   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ ** 'autoload_path_namespace_map' => array ( ),  ** 

    自动加载的目录和命名空间映射   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+  'callable_view_class' => NULL,   

    callableview 视图类   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_foot' => NULL,   

    callableview 页脚   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_head' => NULL,   

    callableview 页眉   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_prefix' => NULL,   

    callableview 视图函数模板   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'callable_view_skip_replace' => false,   

    callableview 可调用视图跳过默认视图替换   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+  'cli_command_alias' => array ( ),   

    命令行类别名   // [DuckPhp\Component\Console](Component-Console.md)
+  'cli_command_default' => 'help',   

       // [DuckPhp\Component\Console](Component-Console.md)
+  'cli_command_method_prefix' => 'command_',   

       // [DuckPhp\Component\Console](Component-Console.md)
+  'cli_default_command_class' => '',   

       // [DuckPhp\Component\Console](Component-Console.md)
+  'cli_enable' => true,   

    启用命令行   // [DuckPhp\Component\Console](Component-Console.md)
+  'cli_mode' => 'replace',   

    命令行启用模式   // [DuckPhp\Component\Console](Component-Console.md)
+ ** 'close_resource_at_output' => false,  ** 

    在输出前关闭资源（DB,Redis）   // [DuckPhp\Core\App](Core-App.md)
+ ** 'config_ext_file_map' => array ( ),  ** 

    额外的配置文件数组   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'controller_base_class' => NULL,  ** 

    控制器基类   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ ** 'controller_class_postfix' => '',  ** 

    控制器类名后缀   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_enable_slash' => false,  ** 

    激活兼容后缀的 /    // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_hide_boot_class' => false,  ** 

    控制器标记，隐藏特别的入口   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_methtod_for_miss' => '__missing',  ** 

    控制器，缺失方法的调用方法   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_path_ext' => '',  ** 

    扩展名，比如你要 .html   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_path_prefix' => '',  ** 

    路由前缀，特殊情况用，限定前缀的 Path_info   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_prefix_post' => 'do_',  ** 

    控制器，POST 方法前缀   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_stop_static_method' => true,  ** 

    控制器禁止直接访问静态方法   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_use_singletonex' => false,  ** 

    控制器使用单例模式   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'controller_welcome_class' => 'Main',  ** 

    控制器默认欢迎方法   // [DuckPhp\Core\Route](Core-Route.md)
+ ** 'database' => NULL,  ** 

    单一数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_auto_extend_method' => true,  ** 

    是否扩充方法至助手类   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_class' => '',  ** 

    DB类   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_list' => NULL,  ** 

    数据库列表   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_list_reload_by_setting' => true,  ** 

    从设置里读取数据库列表   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_list_try_single' => true,  ** 

    尝试使用单一数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_log_sql_level' => 'debug',  ** 

    记录sql 错误等级   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'database_log_sql_query' => false,  ** 

    记录sql 查询   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ ** 'default_exception_do_log' => true,  ** 

    错误的时候打开日志   // [DuckPhp\Core\App](Core-App.md)
+  'default_exception_handler' => NULL,   

    默认异常句柄   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'default_exception_self_display' => true,  ** 

    错误的时候打开日志   // [DuckPhp\Core\App](Core-App.md)
+  'dev_error_handler' => NULL,   

    默认开发错误句柄   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+  'empty_view_key_view' => 'view',   

    给View 的key   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'empty_view_key_wellcome_class' => 'Main/',   

    默认的 Main   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'empty_view_skip_replace' => false,   

    跳过默认的view   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'empty_view_trim_view_wellcome' => true,   

    跳过 Main/   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ ** 'error_404' => NULL,  ** 

    404 页面   // [DuckPhp\Core\App](Core-App.md)
+ ** 'error_500' => NULL,  ** 

    500 页面   // [DuckPhp\Core\App](Core-App.md)
+ ** 'error_debug' => NULL,  ** 

    错误调试页面   // [DuckPhp\Core\App](Core-App.md)
+ ** 'ext' => array ( ),  ** 

    默认开启的扩展   // [DuckPhp\Core\App](Core-App.md)
+  'facades_enable_autoload' => true,   

    使用 facdes 的 autoload   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+  'facades_map' => array ( ),   

    facade 映射   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+  'facades_namespace' => 'MyFacades',   

    facades 开始的namespace   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+  'handle_all_dev_error' => true,   

    接管一切开发错误   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+  'handle_all_exception' => true,   

    接管一切异常   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'injected_helper_map' => '',  ** 

    助手类映射，比较复杂   // [DuckPhp\Core\App](Core-App.md)
+ ** 'is_debug' => false,  ** 

    是否调试状态   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'jsonrpc_backend' => 'https://127.0.0.1',   

    json 的后端   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_check_token_handler' => NULL,   

    设置 token 检查回调   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_enable_autoload' => true,   

    json 启用 autoload   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_is_debug' => false,   

    jsonrpc 是否开启 debug 模式   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_namespace' => 'JsonRpc',   

    jsonrpc 默认的命名空间   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_service_interface' => '',   

    json 服务接口   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_service_namespace' => '',   

    json 命名空间   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+  'jsonrpc_wrap_auto_adjust' => true,   

    jsonrpc 自动调整 wrap   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ ** 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',  ** 

    日志文件名模板   // [DuckPhp\Core\Logger](Core-Logger.md)
+ ** 'log_prefix' => 'DuckPhpLog',  ** 

    日志前缀   // [DuckPhp\Core\Logger](Core-Logger.md)
+  'misc_auto_method_extend' => true,   

    是否扩方法至助手类   // [DuckPhp\Ext\Misc](Ext-Misc.md)
+  'mode_dir_basepath' => '',   

    目录模式的基类   // [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md)
+ ** 'namespace' => '',  ** 

    命名空间   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md), [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'namespace_business' => '',   

    strict_check 的business目录   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ ** 'namespace_controller' => 'Controller',  ** 

    控制器的命名空间   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'namespace_model' => '',   

    strict_check 的model 目录   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ ** 'override_class' => '',  ** 

    重写类名   // [DuckPhp\Core\App](Core-App.md)
+ ** 'path' => '',  ** 

    基础目录   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md), [DuckPhp\Core\Configer](Core-Configer.md), [DuckPhp\Core\Logger](Core-Logger.md), [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md), [DuckPhp\Ext\Misc](Ext-Misc.md)
+ ** 'path_config' => 'config',  ** 

    配置目录   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'path_info_compact_action_key' => '_r',  ** 

    GET 动作方法名的 key   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ ** 'path_info_compact_class_key' => '',  ** 

    GET 模式类名的 key   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ ** 'path_info_compact_enable' => false,  ** 

    使用 _GET 模拟无 PathInfo 配置   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+  'path_lib' => 'lib',   

    库目录   // [DuckPhp\Ext\Misc](Ext-Misc.md)
+ ** 'path_log' => 'logs',  ** 

    日志目录   // [DuckPhp\Core\Logger](Core-Logger.md)
+ ** 'path_namespace' => 'app',  ** 

    命名空间目录   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ ** 'path_view' => 'view',  ** 

    视图目录   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ ** 'path_view_override' => '',  ** 

    覆盖视图目录   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ ** 'platform' => '',  ** 

    平台   // [DuckPhp\Core\App](Core-App.md)
+  'postfix_batch_business' => 'BatchBusiness',   

    batchbusiness   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'postfix_business_lib' => 'Lib',   

     businesslib   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'postfix_ex_model' => 'ExModel',   

    ExModel   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'postfix_model' => 'Model',   

    model   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'redis' => NULL,   

    单一Redisc配置   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_auto_extend_method' => true,   

    是否扩充方法至助手类   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_cache_prefix' => '',   

     redis cache 缓存前缀   // [DuckPhp\Ext\RedisCache](Ext-RedisCache.md)
+  'redis_cache_skip_replace' => false,   

    redis cache 跳过 默认 cache替换   // [DuckPhp\Ext\RedisCache](Ext-RedisCache.md)
+  'redis_list' => NULL,   

     redis 配置列表   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_list_reload_by_setting' => true,   

     redis 使用 settting 文件   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'redis_list_try_single' => true,   

    尝试使用单一Redis配置   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+  'rewrite_auto_extend_method' => true,   

    是否扩充方法至助手类   // [DuckPhp\Ext\RouteHookRewrite](Ext-RouteHookRewrite.md)
+  'rewrite_map' => array ( ),   

    目录重写映射   // [DuckPhp\Ext\RouteHookRewrite](Ext-RouteHookRewrite.md)
+ ** 'route_map' => array ( ),  ** 

    路由映射   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ ** 'route_map_auto_extend_method' => true,  ** 

    是否扩充方法至助手类   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ ** 'route_map_by_config_name' => '',  ** 

    路由配置名，使用配置模式用路由   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ ** 'route_map_important' => array ( ),  ** 

    重要路由映射   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ ** 'setting' => array ( ),  ** 

    设置，预先载入的设置   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'setting_file' => 'setting',  ** 

    设置文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'skip_404_handler' => false,  ** 

    跳过404处理   // [DuckPhp\Core\App](Core-App.md)
+ ** 'skip_app_autoload' => false,  ** 

    跳过 自动加载   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ ** 'skip_exception_check' => false,  ** 

    跳过异常检查   // [DuckPhp\Core\App](Core-App.md)
+ ** 'skip_plugin_mode_check' => false,  ** 

    跳过插件模式检查   // [DuckPhp\Core\App](Core-App.md)
+ ** 'skip_view_notice_error' => true,  ** 

    跳过 View 视图的 notice   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+  'strict_check_context_class' => NULL,   

    不用传输过来的 app类，而是特别指定类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'strict_check_enable' => true,   

    是否开启 strict chck   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+  'system_exception_handler' => NULL,   

    接管系统的异常管理   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ ** 'use_autoloader' => false,  ** 

    使用系统自带加载器   // [DuckPhp\Core\App](Core-App.md)
+ ** 'use_env_file' => false,  ** 

    使用 .env 文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'use_flag_by_setting' => true,  ** 

    从设置文件里再入is_debug,platform.    // [DuckPhp\Core\App](Core-App.md)
+ ** 'use_output_buffer' => false,  ** 

    使用 OB 函数缓冲数据   // [DuckPhp\Core\RuntimeState](Core-RuntimeState.md)
+ ** 'use_setting_file' => false,  ** 

    使用设置文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ ** 'use_short_functions' => true,  ** 

    使用短函数， \_\_url, \_\_h 等 ，详见 Core\Functions.php   // [DuckPhp\Core\App](Core-App.md)

@forscript end

## 选项索引,类名顺序
按类名排序加粗表示默认选项。

@forscript genoptions.php#options-md-class
+ DuckPhp\Core\App
    - 'close_resource_at_output' => false,
        在输出前关闭资源（DB,Redis）
    - 'default_exception_do_log' => true,
        错误的时候打开日志
    - 'default_exception_self_display' => true,
        错误的时候打开日志
    - 'error_404' => NULL,
        404 页面
    - 'error_500' => NULL,
        500 页面
    - 'error_debug' => NULL,
        错误调试页面
    - 'ext' => array ( ),
        默认开启的扩展
    - 'injected_helper_map' => '',
        助手类映射，比较复杂
    - 'is_debug' => false,
        是否调试状态
    - 'namespace' => NULL,
        命名空间
    - 'override_class' => '',
        重写类名
    - 'path' => NULL,
        基础目录
    - 'platform' => '',
        平台
    - 'skip_404_handler' => false,
        跳过404处理
    - 'skip_exception_check' => false,
        跳过异常检查
    - 'skip_plugin_mode_check' => false,
        跳过插件模式检查
    - 'use_autoloader' => false,
        使用系统自带加载器
    - 'use_flag_by_setting' => true,
        从设置文件里再入is_debug,platform. 
    - 'use_short_functions' => true,
        使用短函数， \_\_url, \_\_h 等 ，详见 Core\Functions.php
+ DuckPhp\Core\AutoLoader
    - 'autoload_cache_in_cli' => false,
        在 cli 下开启缓存模式
    - 'autoload_path_namespace_map' => array ( ),
        自动加载的目录和命名空间映射
    - 'namespace' => '',
        命名空间
    - 'path' => '',
        基础目录
    - 'path_namespace' => 'app',
        命名空间目录
    - 'skip_app_autoload' => false,
        跳过 自动加载
+ DuckPhp\Core\Configer
    - 'all_config' => array ( ),
        所有配置
    - 'config_ext_file_map' => array ( ),
        额外的配置文件数组
    - 'path' => '',
        基础目录
    - 'path_config' => 'config',
        配置目录
    - 'setting' => array ( ),
        设置，预先载入的设置
    - 'setting_file' => 'setting',
        设置文件
    - 'use_env_file' => false,
        使用 .env 文件
    - 'use_setting_file' => false,
        使用设置文件
+ DuckPhp\Core\ExceptionManager
    - 'default_exception_handler' => NULL,
        默认异常句柄
    - 'dev_error_handler' => NULL,
        默认开发错误句柄
    - 'handle_all_dev_error' => true,
        接管一切开发错误
    - 'handle_all_exception' => true,
        接管一切异常
    - 'system_exception_handler' => NULL,
        接管系统的异常管理
+ DuckPhp\Core\Logger
    - 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        日志文件名模板
    - 'log_prefix' => 'DuckPhpLog',
        日志前缀
    - 'path' => '',
        基础目录
    - 'path_log' => 'logs',
        日志目录
+ DuckPhp\Core\Route
    - 'controller_base_class' => '',
        控制器基类
    - 'controller_class_postfix' => '',
        控制器类名后缀
    - 'controller_enable_slash' => false,
        激活兼容后缀的 / 
    - 'controller_hide_boot_class' => false,
        控制器标记，隐藏特别的入口
    - 'controller_methtod_for_miss' => '__missing',
        控制器，缺失方法的调用方法
    - 'controller_path_ext' => '',
        扩展名，比如你要 .html
    - 'controller_path_prefix' => '',
        路由前缀，特殊情况用，限定前缀的 Path_info
    - 'controller_prefix_post' => 'do_',
        控制器，POST 方法前缀
    - 'controller_stop_static_method' => true,
        控制器禁止直接访问静态方法
    - 'controller_use_singletonex' => false,
        控制器使用单例模式
    - 'controller_welcome_class' => 'Main',
        控制器默认欢迎方法
    - 'namespace' => '',
        命名空间
    - 'namespace_controller' => 'Controller',
        控制器的命名空间
+ DuckPhp\Core\RuntimeState
    - 'use_output_buffer' => false,
        使用 OB 函数缓冲数据
+ DuckPhp\Core\View
    - 'path' => '',
        基础目录
    - 'path_view' => 'view',
        视图目录
    - 'path_view_override' => '',
        覆盖视图目录
    - 'skip_view_notice_error' => true,
        跳过 View 视图的 notice
+ DuckPhp\Component\Cache
+ DuckPhp\Component\Console
    - 'cli_command_alias' => array ( ),
        命令行类别名
    - 'cli_command_default' => 'help',
        
    - 'cli_command_method_prefix' => 'command_',
        
    - 'cli_default_command_class' => '',
        
    - 'cli_enable' => true,
        启用命令行
    - 'cli_mode' => 'replace',
        命令行启用模式
+ DuckPhp\Component\DbManager
    - 'database' => NULL,
        单一数据库配置
    - 'database_auto_extend_method' => true,
        是否扩充方法至助手类
    - 'database_class' => '',
        DB类
    - 'database_list' => NULL,
        数据库列表
    - 'database_list_reload_by_setting' => true,
        从设置里读取数据库列表
    - 'database_list_try_single' => true,
        尝试使用单一数据库配置
    - 'database_log_sql_level' => 'debug',
        记录sql 错误等级
    - 'database_log_sql_query' => false,
        记录sql 查询
+ DuckPhp\Component\EventManager
+ DuckPhp\Component\RouteHookPathInfoCompat
    - 'path_info_compact_action_key' => '_r',
        GET 动作方法名的 key
    - 'path_info_compact_class_key' => '',
        GET 模式类名的 key
    - 'path_info_compact_enable' => false,
        使用 _GET 模拟无 PathInfo 配置
+ DuckPhp\Component\RouteHookRouteMap
    - 'route_map' => array ( ),
        路由映射
    - 'route_map_auto_extend_method' => true,
        是否扩充方法至助手类
    - 'route_map_by_config_name' => '',
        路由配置名，使用配置模式用路由
    - 'route_map_important' => array ( ),
        重要路由映射
+ DuckPhp\Ext\CallableView
    - 'callable_view_class' => NULL,
        callableview 视图类
    - 'callable_view_foot' => NULL,
        callableview 页脚
    - 'callable_view_head' => NULL,
        callableview 页眉
    - 'callable_view_prefix' => NULL,
        callableview 视图函数模板
    - 'callable_view_skip_replace' => false,
        callableview 可调用视图跳过默认视图替换
    - 'path' => '',
        基础目录
    - 'path_view' => 'view',
        视图目录
    - 'path_view_override' => '',
        覆盖视图目录
    - 'skip_view_notice_error' => true,
        跳过 View 视图的 notice
+ DuckPhp\Ext\EmptyView
    - 'empty_view_key_view' => 'view',
        给View 的key
    - 'empty_view_key_wellcome_class' => 'Main/',
        默认的 Main
    - 'empty_view_skip_replace' => false,
        跳过默认的view
    - 'empty_view_trim_view_wellcome' => true,
        跳过 Main/
    - 'path' => '',
        基础目录
    - 'path_view' => 'view',
        视图目录
    - 'path_view_override' => '',
        覆盖视图目录
    - 'skip_view_notice_error' => true,
        跳过 View 视图的 notice
+ DuckPhp\Ext\MyFacadesAutoLoader
    - 'facades_enable_autoload' => true,
        使用 facdes 的 autoload
    - 'facades_map' => array ( ),
        facade 映射
    - 'facades_namespace' => 'MyFacades',
        facades 开始的namespace
+ DuckPhp\Ext\JsonRpcExt
    - 'jsonrpc_backend' => 'https://127.0.0.1',
        json 的后端
    - 'jsonrpc_check_token_handler' => NULL,
        设置 token 检查回调
    - 'jsonrpc_enable_autoload' => true,
        json 启用 autoload
    - 'jsonrpc_is_debug' => false,
        jsonrpc 是否开启 debug 模式
    - 'jsonrpc_namespace' => 'JsonRpc',
        jsonrpc 默认的命名空间
    - 'jsonrpc_service_interface' => '',
        json 服务接口
    - 'jsonrpc_service_namespace' => '',
        json 命名空间
    - 'jsonrpc_wrap_auto_adjust' => true,
        jsonrpc 自动调整 wrap
+ DuckPhp\Ext\Misc
    - 'misc_auto_method_extend' => true,
        是否扩方法至助手类
    - 'path' => '',
        基础目录
    - 'path_lib' => 'lib',
        库目录
+ DuckPhp\Ext\RedisCache
    - 'redis_cache_prefix' => '',
         redis cache 缓存前缀
    - 'redis_cache_skip_replace' => false,
        redis cache 跳过 默认 cache替换
+ DuckPhp\Ext\RedisManager
    - 'redis' => NULL,
        单一Redisc配置
    - 'redis_auto_extend_method' => true,
        是否扩充方法至助手类
    - 'redis_list' => NULL,
         redis 配置列表
    - 'redis_list_reload_by_setting' => true,
         redis 使用 settting 文件
    - 'redis_list_try_single' => true,
        尝试使用单一Redis配置
+ DuckPhp\Ext\RouteHookApiServer
    - 'api_server_404_as_exception' => false,
        API服务器， 404 引发异常的模式
    - 'api_server_base_class' => '',
        API服务器， 接口，或基类，  ~ 开始的表示是当前命名空间
    - 'api_server_class_postfix' => '',
        API服务器， 类名后缀
    - 'api_server_namespace' => 'Api',
        API服务器， 命名空间，配合 namespace选项使用
    - 'api_server_use_singletonex' => false,
        API服务器，  使用可变单例模式，方便替换实现
    - 'namespace' => '',
        命名空间
+ DuckPhp\Ext\RouteHookDirectoryMode
    - 'mode_dir_basepath' => '',
        目录模式的基类
+ DuckPhp\Ext\RouteHookRewrite
    - 'rewrite_auto_extend_method' => true,
        是否扩充方法至助手类
    - 'rewrite_map' => array ( ),
        目录重写映射
+ DuckPhp\Ext\StrictCheck
    - 'controller_base_class' => NULL,
        控制器基类
    - 'is_debug' => false,
        是否调试状态
    - 'namespace' => '',
        命名空间
    - 'namespace_business' => '',
        strict_check 的business目录
    - 'namespace_controller' => 'Controller',
        控制器的命名空间
    - 'namespace_model' => '',
        strict_check 的model 目录
    - 'postfix_batch_business' => 'BatchBusiness',
        batchbusiness
    - 'postfix_business_lib' => 'Lib',
         businesslib
    - 'postfix_ex_model' => 'ExModel',
        ExModel
    - 'postfix_model' => 'Model',
        model
    - 'strict_check_context_class' => NULL,
        不用传输过来的 app类，而是特别指定类
    - 'strict_check_enable' => true,
        是否开启 strict chck

@forscript end

## 其他选项
这几个选项，不是放在 $options 的，所以特地在这里参考
### DuckPhp\Core\AppPluginTrait

    'plugin_path_namespace' => null,
    'plugin_namespace' => null,
    
    'plugin_routehook_position' => 'append-outter',
    
    'plugin_path_conifg' => 'config',
    'plugin_path_view' => 'view',
    
    'plugin_search_config' => false,
    'plugin_use_helper' => true,
    'plugin_files_config' => [],
    'plugin_url_prefix' => '',
    'plugin_injected_helper_map' => '',
###  DuckPhp\HttpServer\HttpServer
    'host' => '127.0.0.1',
    'port' => '8080',
    'path' => '',
    'path_document' => 'public',
### DuckPhp\Ext\Pager

    'url' => null,
    'current' => null,
    'page_size' => 30,
    'page_key' => 'page',
    'rewrite' => null,
    'pager_context_class' => null,