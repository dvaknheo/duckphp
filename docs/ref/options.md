# 选项参考
[toc]

## 选项索引
按字母顺序，加粗表示默认选项。

@forscript genoptions.php#options-md-alpha
+ **'all_config' => array ( ),** 

    默认自带的所有配置   // [DuckPhp\Core\Configer](Core-Configer.md)
+ 'api_server_404_as_exception' => false, 

    API服务器， 404 引发异常的模式   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ 'api_server_base_class' => '', 

    API服务器，限定的接口或基类，  ~ 开始的表示是当前命名空间   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ 'api_server_class_postfix' => '', 

    API服务器， 限定类名后缀   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ 'api_server_namespace' => 'Api', 

    API服务器， 命名空间，配合 namespace选项使用   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ 'api_server_use_singletonex' => false, 

    API服务器， 使用可变单例模式，方便替换实现   // [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)
+ **'autoload_cache_in_cli' => false,** 

    在命令行模式下开启 opcache 缓存   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ **'autoload_path_namespace_map' => array ( ),** 

    psr4 风格自动加载路径和命名空间映射   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ 'callable_view_class' => NULL, 

    CallableView 限定于此类内 callable_view_class 。   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_foot' => NULL, 

    CallableView 页脚函数   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_head' => NULL, 

    CallableView 页眉函数   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_prefix' => NULL, 

    CallableView 视图方法前缀   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_skip_replace' => false, 

    CallableView 是否替换默认 View   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'cli_command_alias' => array ( ), 

    命令行,类别名列表   // [DuckPhp\Component\Console](Component-Console.md)
+ 'cli_command_default' => 'help', 

    命令行,默认调用指令   // [DuckPhp\Component\Console](Component-Console.md)
+ 'cli_command_method_prefix' => 'command_', 

    命令行,默认方法前缀   // [DuckPhp\Component\Console](Component-Console.md)
+ 'cli_default_command_class' => '', 

    命令行,默认类   // [DuckPhp\Component\Console](Component-Console.md)
+ 'cli_enable' => true, 

    命令行,启用命令行扩展   // [DuckPhp\Component\Console](Component-Console.md)
+ 'cli_mode' => 'replace', 

    命令行,模式，替换模式或者是路由钩子的模式   // [DuckPhp\Component\Console](Component-Console.md)
+ **'close_resource_at_output' => false,** 

    输出时候关闭资源输出（仅供第三方扩展参考   // [DuckPhp\Core\App](Core-App.md)
+ **'config_ext_file_map' => array ( ),** 

    额外的配置文件数组，用于 AppPluginTrait   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'controller_base_class' => NULL,** 

    控制器基类   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'controller_class_map' => array ( ),** 

    控制器，类映射，用于替换   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_class_postfix' => '',** 

    控制器，控制器类名后缀   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_enable_slash' => false,** 

    控制器，允许结尾的 /   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_hide_boot_class' => false,** 

    控制器，隐藏启动的类   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_methtod_for_miss' => '__missing',** 

    控制器，方法丢失调用的方法。   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_path_ext' => '',** 

    控制器，后缀,如 .html   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_path_prefix' => '',** 

    控制器，路由的前缀，只处理限定前缀的 PATH_INFO   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_prefix_post' => 'do_',** 

    控制器，POST 的方法会在方法名前加前缀 do_   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_stop_static_method' => true,** 

    控制器，禁止直接访问控制器静态方法   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_strict_mode' => true,** 

    控制器，严格模式，区分大小写   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_use_singletonex' => false,** 

    控制器，使用可变单例模式   // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_welcome_class' => 'Main',** 

    控制器，欢迎类，默认欢迎类是  Main 。   // [DuckPhp\Core\Route](Core-Route.md)
+ **'database' => NULL,** 

    数据库，单一数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_auto_extend_method' => true,** 

    数据库，是否扩充方法至助手类   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_class' => '',** 

    数据库，默认为 Db::class。   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_list' => NULL,** 

    数据库，多数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_list_reload_by_setting' => true,** 

    数据库，从设置里再入数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_list_try_single' => true,** 

    数据库，尝试使用单一数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_log_sql_level' => 'debug',** 

    数据库，记录sql 错误等级   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_log_sql_query' => false,** 

    数据库，记录sql 查询   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'default_exception_do_log' => true,** 

    发生异常时候记录日志   // [DuckPhp\Core\App](Core-App.md)
+ 'default_exception_handler' => NULL, 

    默认的异常处理回调   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'default_exception_self_display' => true,** 

    发生异常的时候如有可能，调用异常类的 display() 方法。   // [DuckPhp\Core\App](Core-App.md)
+ 'dev_error_handler' => NULL, 

    调试错误的回调   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ 'empty_view_key_view' => 'view', 

    空视图扩展，_Show 的时候给的 $data 的key   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ 'empty_view_key_wellcome_class' => 'Main/', 

    空视图扩展，view 为这个的时候跳过显示   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ 'empty_view_skip_replace' => false, 

    空视图扩展，替换默认的 view   // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ 'empty_view_trim_view_wellcome' => true, 

    空视图扩展，剪掉 view。    // [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ **'error_404' => NULL,** 

    404 错误处理 的View或者回调   // [DuckPhp\Core\App](Core-App.md)
+ **'error_500' => NULL,** 

    500 错误处理 View或者回调   // [DuckPhp\Core\App](Core-App.md)
+ **'error_debug' => NULL,** 

    调试的View或者回调   // [DuckPhp\Core\App](Core-App.md)
+ **'ext' => array ( ),** 

    **重要选项** 扩展   // [DuckPhp\Core\App](Core-App.md)
+ 'facades_enable_autoload' => true, 

    门面扩展，门面类启用自动加载   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+ 'facades_map' => array ( ), 

    门面扩展，门面类门面映射   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+ 'facades_namespace' => 'MyFacades', 

    门面扩展，门面类前缀   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+ 'function_route' => false, 

    函数模式路由，开关   // [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)
+ 'function_route_404_to_index' => false, 

    函数模式路由，404 是否由 index 来执行   // [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)
+ 'function_route_method_prefix' => 'action_', 

    函数模式路由，函数前缀   // [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)
+ 'handle_all_dev_error' => true, 

    抓取调试错误   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ 'handle_all_exception' => true, 

    抓取全部异常   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'injected_helper_map' => '',** 

    injected_helper_map 比较复杂待文档。和助手类映射相关。 v1.2.8-dev   // [DuckPhp\Core\App](Core-App.md)
+ **'is_debug' => false,** 

    是否调试模式   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'jsonrpc_backend' => 'https://127.0.0.1', 

    jsonrpc 后端地址   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_check_token_handler' => NULL, 

    jsonrpc Token 处理   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_enable_autoload' => true, 

    jsonrpc 是否要自动加载   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_is_debug' => false, 

    jsonrpc 是否调试   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_namespace' => 'JsonRpc', 

    jsonrpc 默认jsonrpc 的命名空间   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_service_interface' => '', 

    jsonrpc 限制指定接口或者基类——todo 调整名字   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_service_namespace' => '', 

    jsonrpc 限定服务命名空间   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ 'jsonrpc_wrap_auto_adjust' => true, 

    jsonrpc 封装调整   // [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)
+ **'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',** 

    日志文件名模板    // [DuckPhp\Core\Logger](Core-Logger.md)
+ **'log_prefix' => 'DuckPhpLog',** 

    日志前缀   // [DuckPhp\Core\Logger](Core-Logger.md)
+ 'misc_auto_method_extend' => true, 

    是否扩充 Misc 类方法至助手类   // [DuckPhp\Ext\Misc](Ext-Misc.md)
+ 'mode_dir_basepath' => '', 

    目录模式的基准路径   // [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md)
+ **'namespace' => '',** 

    命名空间   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md), [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'namespace_business' => '', 

    严格检查扩展，业务类命名空间   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'namespace_controller' => 'Controller',** 

    控制器命名空间   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'namespace_model' => '', 

    严格检查扩展，模型命名空间   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'override_class' => '',** 

    **重要选项** 如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。   // [DuckPhp\Core\App](Core-App.md)
+ **'path' => '',** 

    工程路径   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\AutoLoader](Core-AutoLoader.md), [DuckPhp\Core\Configer](Core-Configer.md), [DuckPhp\Core\Logger](Core-Logger.md), [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md), [DuckPhp\Ext\Misc](Ext-Misc.md)
+ **'path_config' => 'config',** 

    配置路径   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'path_info_compact_action_key' => '_r',** 

    无PATH_INFO兼容，替代的 action   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ **'path_info_compact_class_key' => '',** 

    无PATH_INFO兼容，替代的 class   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ **'path_info_compact_enable' => false,** 

    无PATH_INFO兼容，启用   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ 'path_lib' => 'lib', 

    导入的 Import 库目录路径   // [DuckPhp\Ext\Misc](Ext-Misc.md)
+ **'path_log' => 'logs',** 

    日志目录路径   // [DuckPhp\Core\Logger](Core-Logger.md)
+ **'path_namespace' => 'app',** 

    自动加载，命名空间的相对路径   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ **'path_view' => 'view',** 

    视图路径   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ **'path_view_override' => '',** 

    用于覆盖的路径——用于插件模式   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ **'platform' => '',** 

    平台， 自定义字符，用于 Platform() 方法。   // [DuckPhp\Core\App](Core-App.md)
+ 'postfix_batch_business' => 'BatchBusiness', 

    严格检查扩展，跳过批量业务的类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'postfix_business_lib' => 'Lib', 

    严格检查扩展，跳过非业务类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'postfix_ex_model' => 'ExModel', 

    严格检查扩展，混合模型后缀   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'postfix_model' => 'Model', 

    严格检查扩展，模型后缀   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'redis' => NULL, 

    redis 设置   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+ 'redis_auto_extend_method' => true, 

    自动增加Reis扩展方法到助手方法   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+ 'redis_cache_prefix' => '', 

    Redis Cache 的 key 前缀   // [DuckPhp\Ext\RedisCache](Ext-RedisCache.md)
+ 'redis_cache_skip_replace' => false, 

    跳过默认 cache 替换   // [DuckPhp\Ext\RedisCache](Ext-RedisCache.md)
+ 'redis_list' => NULL, 

    redis 列表   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+ 'redis_list_reload_by_setting' => true, 

    是否从设置里再入 redis 设置   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+ 'redis_list_try_single' => true, 

    redis 设置是否同时支持单个和多个   // [DuckPhp\Ext\RedisManager](Ext-RedisManager.md)
+ 'rewrite_auto_extend_method' => true, 

    路由重写，自动扩展方法   // [DuckPhp\Ext\RouteHookRewrite](Ext-RouteHookRewrite.md)
+ 'rewrite_map' => array ( ), 

    路由重写，重写映射表   // [DuckPhp\Ext\RouteHookRewrite](Ext-RouteHookRewrite.md)
+ **'route_map' => array ( ),** 

    路由映射，在默认路由失败后执行的路由映射   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ **'route_map_auto_extend_method' => true,** 

    路由映射，扩充方法至助手类   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ **'route_map_by_config_name' => '',** 

    路由映射，从配置中读取  route_map_important 和 route_map   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ **'route_map_important' => array ( ),** 

    路由映射，在默认路由前执行的路由映射   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ **'setting' => array ( ),** 

    默认自带的设置   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'setting_file' => 'setting',** 

    设置文件名。   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'setting_file_ignore_exists' => false,** 

    如果设置文件不存在也不报错   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'skip_404_handler' => false,** 

    不处理 404 ，用于配合其他框架使用。   // [DuckPhp\Core\App](Core-App.md)
+ **'skip_app_autoload' => false,** 

    跳过 app 的加载   // [DuckPhp\Core\AutoLoader](Core-AutoLoader.md)
+ **'skip_exception_check' => false,** 

    不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用   // [DuckPhp\Core\App](Core-App.md)
+ **'skip_plugin_mode_check' => false,** 

    跳过插件模式检查   // [DuckPhp\Core\App](Core-App.md)
+ **'skip_view_notice_error' => true,** 

    关闭  View 视图的 notice 警告，以避免麻烦的处理。   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md)
+ 'strict_check_context_class' => NULL, 

    严格检查扩展，不用传输过来的 app类，而是特别指定类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'strict_check_enable' => true, 

    严格检查模式开启   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'system_exception_handler' => NULL, 

    系统的异常调试回调   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'use_autoloader' => false,** 

    使用系统自带自动加载器   // [DuckPhp\Core\App](Core-App.md)
+ **'use_env_file' => false,** 

    使用 .env 文件   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'use_flag_by_setting' => true,** 

    从设置文件里再入 is_debug,platform。   // [DuckPhp\Core\App](Core-App.md)
+ **'use_output_buffer' => false,** 

    使用 OB 函数缓冲数据   // [DuckPhp\Core\RuntimeState](Core-RuntimeState.md)
+ **'use_setting_file' => false,** 

    使用设置文件: $path/$path_config/$setting_file.php   // [DuckPhp\Core\Configer](Core-Configer.md)
+ **'use_short_functions' => true,** 

    使用短函数， \\_\\_url, \\_\\_h 等。   // [DuckPhp\Core\App](Core-App.md)

@forscript end

## 选项索引,类名顺序
按类名排序加粗表示默认选项。

@forscript genoptions.php#options-md-class
+ DuckPhp\Core\App
    - 'close_resource_at_output' => false,
        输出时候关闭资源输出（仅供第三方扩展参考
    - 'default_exception_do_log' => true,
        发生异常时候记录日志
    - 'default_exception_self_display' => true,
        发生异常的时候如有可能，调用异常类的 display() 方法。
    - 'error_404' => NULL,
        404 错误处理 的View或者回调
    - 'error_500' => NULL,
        500 错误处理 View或者回调
    - 'error_debug' => NULL,
        调试的View或者回调
    - 'ext' => array ( ),
        **重要选项** 扩展
    - 'injected_helper_map' => '',
        injected_helper_map 比较复杂待文档。和助手类映射相关。 v1.2.8-dev
    - 'is_debug' => false,
        是否调试模式
    - 'namespace' => NULL,
        命名空间
    - 'override_class' => '',
        **重要选项** 如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。
    - 'path' => NULL,
        工程路径
    - 'platform' => '',
        平台， 自定义字符，用于 Platform() 方法。
    - 'skip_404_handler' => false,
        不处理 404 ，用于配合其他框架使用。
    - 'skip_exception_check' => false,
        不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用
    - 'skip_plugin_mode_check' => false,
        跳过插件模式检查
    - 'use_autoloader' => false,
        使用系统自带自动加载器
    - 'use_flag_by_setting' => true,
        从设置文件里再入 is_debug,platform。
    - 'use_short_functions' => true,
        使用短函数， \\_\\_url, \\_\\_h 等。
+ DuckPhp\Core\AutoLoader
    - 'autoload_cache_in_cli' => false,
        在命令行模式下开启 opcache 缓存
    - 'autoload_path_namespace_map' => array ( ),
        psr4 风格自动加载路径和命名空间映射
    - 'namespace' => '',
        命名空间
    - 'path' => '',
        工程路径
    - 'path_namespace' => 'app',
        自动加载，命名空间的相对路径
    - 'skip_app_autoload' => false,
        跳过 app 的加载
+ DuckPhp\Core\Configer
    - 'all_config' => array ( ),
        默认自带的所有配置
    - 'config_ext_file_map' => array ( ),
        额外的配置文件数组，用于 AppPluginTrait
    - 'path' => '',
        工程路径
    - 'path_config' => 'config',
        配置路径
    - 'setting' => array ( ),
        默认自带的设置
    - 'setting_file' => 'setting',
        设置文件名。
    - 'setting_file_ignore_exists' => false,
        如果设置文件不存在也不报错
    - 'use_env_file' => false,
        使用 .env 文件
    - 'use_setting_file' => false,
        使用设置文件: $path/$path_config/$setting_file.php
+ DuckPhp\Core\ExceptionManager
    - 'default_exception_handler' => NULL,
        默认的异常处理回调
    - 'dev_error_handler' => NULL,
        调试错误的回调
    - 'handle_all_dev_error' => true,
        抓取调试错误
    - 'handle_all_exception' => true,
        抓取全部异常
    - 'system_exception_handler' => NULL,
        系统的异常调试回调
+ DuckPhp\Core\Logger
    - 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        日志文件名模板 
    - 'log_prefix' => 'DuckPhpLog',
        日志前缀
    - 'path' => '',
        工程路径
    - 'path_log' => 'logs',
        日志目录路径
+ DuckPhp\Core\Route
    - 'controller_base_class' => '',
        控制器基类
    - 'controller_class_map' => array ( ),
        控制器，类映射，用于替换
    - 'controller_class_postfix' => '',
        控制器，控制器类名后缀
    - 'controller_enable_slash' => false,
        控制器，允许结尾的 /
    - 'controller_hide_boot_class' => false,
        控制器，隐藏启动的类
    - 'controller_methtod_for_miss' => '__missing',
        控制器，方法丢失调用的方法。
    - 'controller_path_ext' => '',
        控制器，后缀,如 .html
    - 'controller_path_prefix' => '',
        控制器，路由的前缀，只处理限定前缀的 PATH_INFO
    - 'controller_prefix_post' => 'do_',
        控制器，POST 的方法会在方法名前加前缀 do_
    - 'controller_stop_static_method' => true,
        控制器，禁止直接访问控制器静态方法
    - 'controller_strict_mode' => true,
        控制器，严格模式，区分大小写
    - 'controller_use_singletonex' => false,
        控制器，使用可变单例模式
    - 'controller_welcome_class' => 'Main',
        控制器，欢迎类，默认欢迎类是  Main 。
    - 'namespace' => '',
        命名空间
    - 'namespace_controller' => 'Controller',
        控制器命名空间
+ DuckPhp\Core\RuntimeState
    - 'use_output_buffer' => false,
        使用 OB 函数缓冲数据
+ DuckPhp\Core\View
    - 'path' => '',
        工程路径
    - 'path_view' => 'view',
        视图路径
    - 'path_view_override' => '',
        用于覆盖的路径——用于插件模式
    - 'skip_view_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Component\Cache
+ DuckPhp\Component\Console
    - 'cli_command_alias' => array ( ),
        命令行,类别名列表
    - 'cli_command_default' => 'help',
        命令行,默认调用指令
    - 'cli_command_method_prefix' => 'command_',
        命令行,默认方法前缀
    - 'cli_default_command_class' => '',
        命令行,默认类
    - 'cli_enable' => true,
        命令行,启用命令行扩展
    - 'cli_mode' => 'replace',
        命令行,模式，替换模式或者是路由钩子的模式
+ DuckPhp\Component\DbManager
    - 'database' => NULL,
        数据库，单一数据库配置
    - 'database_auto_extend_method' => true,
        数据库，是否扩充方法至助手类
    - 'database_class' => '',
        数据库，默认为 Db::class。
    - 'database_list' => NULL,
        数据库，多数据库配置
    - 'database_list_reload_by_setting' => true,
        数据库，从设置里再入数据库配置
    - 'database_list_try_single' => true,
        数据库，尝试使用单一数据库配置
    - 'database_log_sql_level' => 'debug',
        数据库，记录sql 错误等级
    - 'database_log_sql_query' => false,
        数据库，记录sql 查询
+ DuckPhp\Component\EventManager
+ DuckPhp\Component\RouteHookPathInfoCompat
    - 'path_info_compact_action_key' => '_r',
        无PATH_INFO兼容，替代的 action
    - 'path_info_compact_class_key' => '',
        无PATH_INFO兼容，替代的 class
    - 'path_info_compact_enable' => false,
        无PATH_INFO兼容，启用
+ DuckPhp\Component\RouteHookRouteMap
    - 'route_map' => array ( ),
        路由映射，在默认路由失败后执行的路由映射
    - 'route_map_auto_extend_method' => true,
        路由映射，扩充方法至助手类
    - 'route_map_by_config_name' => '',
        路由映射，从配置中读取  route_map_important 和 route_map
    - 'route_map_important' => array ( ),
        路由映射，在默认路由前执行的路由映射
+ DuckPhp\Ext\CallableView
    - 'callable_view_class' => NULL,
        CallableView 限定于此类内 callable_view_class 。
    - 'callable_view_foot' => NULL,
        CallableView 页脚函数
    - 'callable_view_head' => NULL,
        CallableView 页眉函数
    - 'callable_view_prefix' => NULL,
        CallableView 视图方法前缀
    - 'callable_view_skip_replace' => false,
        CallableView 是否替换默认 View
    - 'path' => '',
        工程路径
    - 'path_view' => 'view',
        视图路径
    - 'path_view_override' => '',
        用于覆盖的路径——用于插件模式
    - 'skip_view_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Ext\EmptyView
    - 'empty_view_key_view' => 'view',
        空视图扩展，_Show 的时候给的 $data 的key
    - 'empty_view_key_wellcome_class' => 'Main/',
        空视图扩展，view 为这个的时候跳过显示
    - 'empty_view_skip_replace' => false,
        空视图扩展，替换默认的 view
    - 'empty_view_trim_view_wellcome' => true,
        空视图扩展，剪掉 view。 
    - 'path' => '',
        工程路径
    - 'path_view' => 'view',
        视图路径
    - 'path_view_override' => '',
        用于覆盖的路径——用于插件模式
    - 'skip_view_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Ext\MyFacadesAutoLoader
    - 'facades_enable_autoload' => true,
        门面扩展，门面类启用自动加载
    - 'facades_map' => array ( ),
        门面扩展，门面类门面映射
    - 'facades_namespace' => 'MyFacades',
        门面扩展，门面类前缀
+ DuckPhp\Ext\JsonRpcExt
    - 'jsonrpc_backend' => 'https://127.0.0.1',
        jsonrpc 后端地址
    - 'jsonrpc_check_token_handler' => NULL,
        jsonrpc Token 处理
    - 'jsonrpc_enable_autoload' => true,
        jsonrpc 是否要自动加载
    - 'jsonrpc_is_debug' => false,
        jsonrpc 是否调试
    - 'jsonrpc_namespace' => 'JsonRpc',
        jsonrpc 默认jsonrpc 的命名空间
    - 'jsonrpc_service_interface' => '',
        jsonrpc 限制指定接口或者基类——todo 调整名字
    - 'jsonrpc_service_namespace' => '',
        jsonrpc 限定服务命名空间
    - 'jsonrpc_wrap_auto_adjust' => true,
        jsonrpc 封装调整
+ DuckPhp\Ext\Misc
    - 'misc_auto_method_extend' => true,
        是否扩充 Misc 类方法至助手类
    - 'path' => '',
        工程路径
    - 'path_lib' => 'lib',
        导入的 Import 库目录路径
+ DuckPhp\Ext\RedisCache
    - 'redis_cache_prefix' => '',
        Redis Cache 的 key 前缀
    - 'redis_cache_skip_replace' => false,
        跳过默认 cache 替换
+ DuckPhp\Ext\RedisManager
    - 'redis' => NULL,
        redis 设置
    - 'redis_auto_extend_method' => true,
        自动增加Reis扩展方法到助手方法
    - 'redis_list' => NULL,
        redis 列表
    - 'redis_list_reload_by_setting' => true,
        是否从设置里再入 redis 设置
    - 'redis_list_try_single' => true,
        redis 设置是否同时支持单个和多个
+ DuckPhp\Ext\RouteHookApiServer
    - 'api_server_404_as_exception' => false,
        API服务器， 404 引发异常的模式
    - 'api_server_base_class' => '',
        API服务器，限定的接口或基类，  ~ 开始的表示是当前命名空间
    - 'api_server_class_postfix' => '',
        API服务器， 限定类名后缀
    - 'api_server_namespace' => 'Api',
        API服务器， 命名空间，配合 namespace选项使用
    - 'api_server_use_singletonex' => false,
        API服务器， 使用可变单例模式，方便替换实现
    - 'namespace' => '',
        命名空间
+ DuckPhp\Ext\RouteHookDirectoryMode
    - 'mode_dir_basepath' => '',
        目录模式的基准路径
+ DuckPhp\Ext\RouteHookFunctionRoute
    - 'function_route' => false,
        函数模式路由，开关
    - 'function_route_404_to_index' => false,
        函数模式路由，404 是否由 index 来执行
    - 'function_route_method_prefix' => 'action_',
        函数模式路由，函数前缀
+ DuckPhp\Ext\RouteHookRewrite
    - 'rewrite_auto_extend_method' => true,
        路由重写，自动扩展方法
    - 'rewrite_map' => array ( ),
        路由重写，重写映射表
+ DuckPhp\Ext\StrictCheck
    - 'controller_base_class' => NULL,
        控制器基类
    - 'is_debug' => false,
        是否调试模式
    - 'namespace' => '',
        命名空间
    - 'namespace_business' => '',
        严格检查扩展，业务类命名空间
    - 'namespace_controller' => 'Controller',
        控制器命名空间
    - 'namespace_model' => '',
        严格检查扩展，模型命名空间
    - 'postfix_batch_business' => 'BatchBusiness',
        严格检查扩展，跳过批量业务的类
    - 'postfix_business_lib' => 'Lib',
        严格检查扩展，跳过非业务类
    - 'postfix_ex_model' => 'ExModel',
        严格检查扩展，混合模型后缀
    - 'postfix_model' => 'Model',
        严格检查扩展，模型后缀
    - 'strict_check_context_class' => NULL,
        严格检查扩展，不用传输过来的 app类，而是特别指定类
    - 'strict_check_enable' => true,
        严格检查模式开启

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