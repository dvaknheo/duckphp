# 选项参考
[toc]

## 选项索引
按字母顺序，加粗表示默认选项。

@forscript genoptions.php#options-md-alpha
+ **'alias' => NULL,** 

    别名，目前只用于视图目录   // [DuckPhp\Core\App](Core-App.md)
+ **'allow_require_ext_app' => true,** 

       // 
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
+ **'app' => array ( ),** 

    子应用，保存 类名=>选项对   // [DuckPhp\Core\App](Core-App.md)
+ 'autoloader' => 'vendor/autoload.php', 

       // [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md)
+ 'callable_view_class' => NULL, 

    CallableView 限定于此类内 callable_view_class 。   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_foot' => NULL, 

    CallableView 页脚函数   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_head' => NULL, 

    CallableView 页眉函数   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_is_object_call' => true, 

       // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_prefix' => NULL, 

    CallableView 视图方法前缀   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ 'callable_view_skip_replace' => false, 

    CallableView 是否替换默认 View   // [DuckPhp\Ext\CallableView](Ext-CallableView.md)
+ **'class_admin' => '',** 

    管理员类名，设置这个类以实现默认的管理员类   // 
+ **'class_user' => '',** 

    用户类名，设置这个类以实现默认的用户类   // 
+ **'cli_command_classes' => array ( ),** 

       // [DuckPhp\Core\App](Core-App.md)
+ **'cli_command_default' => 'help',** 

    命令行,默认调用指令   // [DuckPhp\Core\Console](Core-Console.md)
+ **'cli_command_group' => array ( ),** 

       // [DuckPhp\Core\Console](Core-Console.md)
+ **'cli_command_method_prefix' => 'command_',** 

       // [DuckPhp\Core\App](Core-App.md)
+ **'cli_command_prefix' => NULL,** 

       // [DuckPhp\Core\App](Core-App.md)
+ **'cli_command_with_app' => true,** 

       // 
+ **'cli_command_with_common' => true,** 

       // 
+ **'cli_command_with_fast_installer' => true,** 

       // 
+ **'cli_enable' => true,** 

    启用命令行模式   // [DuckPhp\Core\App](Core-App.md)
+ **'cli_readlines_logfile' => '',** 

       // [DuckPhp\Core\Console](Core-Console.md)
+ **'close_resource_at_output' => false,** 

    输出时候关闭资源输出（仅供第三方扩展参考   // [DuckPhp\Core\App](Core-App.md)
+ 'controller_base_class' => NULL, 

    控制器基类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'controller_class_adjust' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_class_base' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_class_map' => array ( ),** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_class_postfix' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_method_prefix' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_path_ext' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_prefix_post' => 'do_',** 

       // [DuckPhp\Core\Route](Core-Route.md)
+ **'controller_resource_prefix' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_url_prefix' => '',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md), [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md), [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_welcome_class' => 'Main',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_welcome_class_visible' => false,** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'controller_welcome_method' => 'index',** 

       // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)
+ **'database' => NULL,** 

    数据库，单一数据库配置   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_class' => '',** 

    数据库，默认为 Db::class。   // [DuckPhp\Component\DbManager](Component-DbManager.md)
+ **'database_driver' => '',** 

       // [DuckPhp\Component\DbManager](Component-DbManager.md)
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
+ **'default_exception_handler' => NULL,** 

    默认的异常处理回调   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'dev_error_handler' => NULL,** 

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

    404 错误处理的View或者回调，仅根应用有效   // [DuckPhp\Core\App](Core-App.md)
+ **'error_500' => NULL,** 

    500 错误处理的View或者回调，仅根应用有效   // [DuckPhp\Core\App](Core-App.md)
+ **'error_maintain' => NULL,** 

    维修页面view ，类似 error_404   // [DuckPhp\Component\RouteHookCheckStatus](Component-RouteHookCheckStatus.md)
+ **'error_need_install' => NULL,** 

    需要安装的页面   // [DuckPhp\Component\RouteHookCheckStatus](Component-RouteHookCheckStatus.md)
+ **'exception_for_project' => NULL,** 

    异常报告仅针对的异常   // [DuckPhp\Core\App](Core-App.md)
+ **'exception_reporter' => NULL,** 

    异常报告类   // [DuckPhp\Core\App](Core-App.md)
+ **'ext' => array ( ),** 

       // [DuckPhp\Core\App](Core-App.md)
+ **'ext_options_file' => 'config/DuckPhpApps.config.php',** 

    配置文件名字   // 
+ **'ext_options_file_enable' => true,** 

    额外配置文件   // 
+ 'facades_enable_autoload' => true, 

    门面扩展，门面类启用自动加载   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+ 'facades_map' => array ( ), 

    门面扩展，门面类门面映射   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+ 'facades_namespace' => 'MyFacades', 

    门面扩展，门面类前缀   // [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)
+ 'force' => false, 

       // [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md)
+ 'function_route' => false, 

    函数模式路由，开关   // [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)
+ 'function_route_404_to_index' => false, 

    函数模式路由，404 是否由 index 来执行   // [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)
+ 'function_route_method_prefix' => 'action_', 

    函数模式路由，函数前缀   // [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)
+ **'handle_all_dev_error' => true,** 

    抓取调试错误   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'handle_all_exception' => true,** 

    抓取全部异常   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'handle_exception_on_init' => true,** 

       // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ 'help' => false, 

       // [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md)
+ **'html_handler' => NULL,** 

    HTML编码函数   // [DuckPhp\Core\App](Core-App.md)
+ **'is_debug' => false,** 

    是否调试模式   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'json_view_skip_replace' => false, 

    jsonview, 跳过替换默认的View   // [DuckPhp\Ext\JsonView](Ext-JsonView.md)
+ 'json_view_skip_vars' => array ( ), 

    jsonview, 排除变量   // [DuckPhp\Ext\JsonView](Ext-JsonView.md)
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
+ **'lang_handler' => NULL,** 

    语言编码回调   // [DuckPhp\Core\App](Core-App.md)
+ **'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',** 

    日志文件名模板    // [DuckPhp\Core\Logger](Core-Logger.md)
+ **'log_prefix' => 'DuckPhpLog',** 

    日志前缀   // [DuckPhp\Core\Logger](Core-Logger.md)
+ 'middleware' => array ( ), 

    middelware 放的是回调列表   // [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md)
+ 'mode_dir_basepath' => '', 

    目录模式的基准路径   // [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md)
+ **'namespace' => '',** 

    命名空间   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md), [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'namespace_business' => '', 

    严格检查扩展，业务类命名空间   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'namespace_controller' => 'Controller',** 

    控制器命名空间   // [DuckPhp\Core\Route](Core-Route.md), [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md), [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'namespace_model' => '', 

    严格检查扩展，模型命名空间   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'on_init' => NULL,** 

    初始化完成后处理回调   // [DuckPhp\Core\App](Core-App.md)
+ **'override_class' => NULL,** 

    如果这个选项的类存在，则且新建 `override_class` 初始化   // [DuckPhp\Core\App](Core-App.md)
+ **'override_class_from' => NULL,** 

    `override_class`切过去的时候会在此保存旧的`override_class`   // [DuckPhp\Core\App](Core-App.md)
+ **'path' => '',** 

    工程路径   // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\Logger](Core-Logger.md), [DuckPhp\Core\View](Core-View.md), [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md), [DuckPhp\Ext\JsonView](Ext-JsonView.md), [DuckPhp\Ext\Misc](Ext-Misc.md)
+ **'path_document' => 'public',** 

    文档路径   // [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md)
+ **'path_info_compact_action_key' => '_r',** 

    无PATH_INFO兼容，替代的 action   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ **'path_info_compact_class_key' => '',** 

    无PATH_INFO兼容，替代的 class   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ **'path_info_compact_enable' => true,** 

    PATH_INFO 兼容模式   // [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)
+ 'path_lib' => 'lib', 

    导入的 Import 库目录路径   // [DuckPhp\Ext\Misc](Ext-Misc.md)
+ **'path_log' => 'runtime',** 

    日志目录路径   // [DuckPhp\Core\Logger](Core-Logger.md)
+ **'path_resource' => 'res',** 

    资源目录   // [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md)
+ **'path_runtime' => 'runtime',** 

       // [DuckPhp\Core\App](Core-App.md), [DuckPhp\Core\Runtime](Core-Runtime.md)
+ **'path_view' => 'view',** 

    视图路径   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md), [DuckPhp\Ext\JsonView](Ext-JsonView.md)
+ 'postfix_batch_business' => 'BatchBusiness', 

    严格检查扩展，跳过批量业务的类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'postfix_business_lib' => 'Lib', 

    严格检查扩展，跳过非业务类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'postfix_ex_model' => 'ExModel', 

    严格检查扩展，混合模型后缀   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'postfix_model' => 'Model', 

    严格检查扩展，模型后缀   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'redis' => NULL,** 

    redis 设置   // [DuckPhp\Component\RedisManager](Component-RedisManager.md)
+ **'redis_list' => NULL,** 

    redis 列表   // [DuckPhp\Component\RedisManager](Component-RedisManager.md)
+ **'redis_list_reload_by_setting' => true,** 

    是否从设置里再入 redis 设置   // [DuckPhp\Component\RedisManager](Component-RedisManager.md)
+ **'redis_list_try_single' => true,** 

    redis 设置是否同时支持单个和多个   // [DuckPhp\Component\RedisManager](Component-RedisManager.md)
+ **'rewrite_map' => array ( ),** 

    路由重写，重写映射表   // [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md)
+ **'route_map' => array ( ),** 

    路由映射，在默认路由失败后执行的路由映射   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ **'route_map_important' => array ( ),** 

    路由映射，在默认路由前执行的路由映射   // [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)
+ **'session_prefix' => NULL,** 

    Session 前缀   // 
+ **'setting_file' => 'config/DuckPhpSettings.config.php',** 

    设置文件名。仅根应用有效   // [DuckPhp\Core\App](Core-App.md)
+ **'setting_file_enable' => true,** 

    使用设置文件: $path/$path_config/$setting_file.php 仅根应用有效   // [DuckPhp\Core\App](Core-App.md)
+ **'setting_file_ignore_exists' => true,** 

    如果设置文件不存在也不报错 仅根应用有效   // [DuckPhp\Core\App](Core-App.md)
+ **'skip_404' => false,** 

    不处理 404 ，用于配合其他框架使用。   // [DuckPhp\Core\App](Core-App.md)
+ **'skip_exception_check' => false,** 

    不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用   // [DuckPhp\Core\App](Core-App.md)
+ 'strict_check_context_class' => NULL, 

    严格检查扩展，不用传输过来的 app类，而是特别指定类   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ 'strict_check_enable' => true, 

    严格检查模式开启   // [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md)
+ **'superglobal_auto_define' => false,** 

    初始化时定义  `__SUPERGLOBAL_CONTEXT`宏   // [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)
+ **'system_exception_handler' => NULL,** 

    系统的异常调试回调   // [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)
+ **'table_prefix' => NULL,** 

    数据库表前缀   // 
+ **'use_env_file' => false,** 

    使用 .env 文件。 仅根应用有效   // [DuckPhp\Core\App](Core-App.md)
+ **'use_output_buffer' => false,** 

    使用 OB 函数缓冲数据   // [DuckPhp\Core\Runtime](Core-Runtime.md)
+ 'verbose' => false, 

       // [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md)
+ **'view_skip_notice_error' => true,** 

    关闭  View 视图的 notice 警告，以避免麻烦的处理。   // [DuckPhp\Core\View](Core-View.md), [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\EmptyView](Ext-EmptyView.md), [DuckPhp\Ext\JsonView](Ext-JsonView.md)

@forscript end

## 选项索引,类名顺序
按类名排序加粗表示默认选项。

@forscript genoptions.php#options-md-class
+ DuckPhp\DuckPhp
    - 'alias' => NULL,
        别名，目前只用于视图目录
    - 'allow_require_ext_app' => true,
        
    - 'app' => array ( ),
        子应用，保存 类名=>选项对
    - 'class_admin' => '',
        管理员类名，设置这个类以实现默认的管理员类
    - 'class_user' => '',
        用户类名，设置这个类以实现默认的用户类
    - 'cli_command_classes' => array ( ),
        
    - 'cli_command_method_prefix' => 'command_',
        
    - 'cli_command_prefix' => NULL,
        
    - 'cli_command_with_app' => true,
        
    - 'cli_command_with_common' => true,
        
    - 'cli_command_with_fast_installer' => true,
        
    - 'cli_enable' => true,
        启用命令行模式
    - 'close_resource_at_output' => false,
        输出时候关闭资源输出（仅供第三方扩展参考
    - 'database_driver' => '',
        
    - 'default_exception_do_log' => true,
        发生异常时候记录日志
    - 'error_404' => NULL,
        404 错误处理的View或者回调，仅根应用有效
    - 'error_500' => NULL,
        500 错误处理的View或者回调，仅根应用有效
    - 'exception_for_project' => NULL,
        异常报告仅针对的异常
    - 'exception_reporter' => NULL,
        异常报告类
    - 'ext' => array (   'DuckPhp\\Component\\RouteHookCheckStatus' => true,   'DuckPhp\\Component\\RouteHookRewrite' => true,   'DuckPhp\\Component\\RouteHookRouteMap' => true,   'DuckPhp\\Component\\RouteHookResource' => true, ),
        
    - 'ext_options_file' => 'config/DuckPhpApps.config.php',
        配置文件名字
    - 'ext_options_file_enable' => true,
        额外配置文件
    - 'html_handler' => NULL,
        HTML编码函数
    - 'is_debug' => false,
        是否调试模式
    - 'lang_handler' => NULL,
        语言编码回调
    - 'namespace' => NULL,
        命名空间
    - 'on_init' => NULL,
        初始化完成后处理回调
    - 'override_class' => NULL,
        如果这个选项的类存在，则且新建 `override_class` 初始化
    - 'override_class_from' => NULL,
        `override_class`切过去的时候会在此保存旧的`override_class`
    - 'path' => NULL,
        工程路径
    - 'path_info_compact_enable' => false,
        PATH_INFO 兼容模式
    - 'path_runtime' => 'runtime',
        
    - 'session_prefix' => NULL,
        Session 前缀
    - 'setting_file' => 'config/DuckPhpSettings.config.php',
        设置文件名。仅根应用有效
    - 'setting_file_enable' => true,
        使用设置文件: $path/$path_config/$setting_file.php 仅根应用有效
    - 'setting_file_ignore_exists' => true,
        如果设置文件不存在也不报错 仅根应用有效
    - 'skip_404' => false,
        不处理 404 ，用于配合其他框架使用。
    - 'skip_exception_check' => false,
        不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用
    - 'table_prefix' => NULL,
        数据库表前缀
    - 'use_env_file' => false,
        使用 .env 文件。 仅根应用有效
+ DuckPhp\Core\App
    - 'alias' => NULL,
        别名，目前只用于视图目录
    - 'app' => array ( ),
        子应用，保存 类名=>选项对
    - 'cli_command_classes' => array ( ),
        
    - 'cli_command_method_prefix' => 'command_',
        
    - 'cli_command_prefix' => NULL,
        
    - 'cli_enable' => true,
        启用命令行模式
    - 'close_resource_at_output' => false,
        输出时候关闭资源输出（仅供第三方扩展参考
    - 'default_exception_do_log' => true,
        发生异常时候记录日志
    - 'error_404' => NULL,
        404 错误处理的View或者回调，仅根应用有效
    - 'error_500' => NULL,
        500 错误处理的View或者回调，仅根应用有效
    - 'exception_for_project' => NULL,
        异常报告仅针对的异常
    - 'exception_reporter' => NULL,
        异常报告类
    - 'ext' => array ( ),
        
    - 'html_handler' => NULL,
        HTML编码函数
    - 'is_debug' => false,
        是否调试模式
    - 'lang_handler' => NULL,
        语言编码回调
    - 'namespace' => NULL,
        命名空间
    - 'on_init' => NULL,
        初始化完成后处理回调
    - 'override_class' => NULL,
        如果这个选项的类存在，则且新建 `override_class` 初始化
    - 'override_class_from' => NULL,
        `override_class`切过去的时候会在此保存旧的`override_class`
    - 'path' => NULL,
        工程路径
    - 'path_runtime' => 'runtime',
        
    - 'setting_file' => 'config/DuckPhpSettings.config.php',
        设置文件名。仅根应用有效
    - 'setting_file_enable' => true,
        使用设置文件: $path/$path_config/$setting_file.php 仅根应用有效
    - 'setting_file_ignore_exists' => true,
        如果设置文件不存在也不报错 仅根应用有效
    - 'skip_404' => false,
        不处理 404 ，用于配合其他框架使用。
    - 'skip_exception_check' => false,
        不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用
    - 'use_env_file' => false,
        使用 .env 文件。 仅根应用有效
+ DuckPhp\Core\ExceptionManager
    - 'default_exception_handler' => NULL,
        默认的异常处理回调
    - 'dev_error_handler' => NULL,
        调试错误的回调
    - 'handle_all_dev_error' => true,
        抓取调试错误
    - 'handle_all_exception' => true,
        抓取全部异常
    - 'handle_exception_on_init' => true,
        
    - 'system_exception_handler' => NULL,
        系统的异常调试回调
+ DuckPhp\Component\ExtOptionsLoader
+ DuckPhp\Core\Console
    - 'cli_command_default' => 'help',
        命令行,默认调用指令
    - 'cli_command_group' => array ( ),
        
    - 'cli_readlines_logfile' => '',
        
+ DuckPhp\Core\Route
    - 'controller_class_adjust' => '',
        
    - 'controller_class_base' => '',
        
    - 'controller_class_map' => array ( ),
        
    - 'controller_class_postfix' => 'Controller',
        
    - 'controller_method_prefix' => 'action_',
        
    - 'controller_path_ext' => '',
        
    - 'controller_prefix_post' => 'do_',
        
    - 'controller_resource_prefix' => '',
        
    - 'controller_url_prefix' => '',
        
    - 'controller_welcome_class' => 'Main',
        
    - 'controller_welcome_class_visible' => false,
        
    - 'controller_welcome_method' => 'index',
        
    - 'namespace' => '',
        命名空间
    - 'namespace_controller' => 'Controller',
        控制器命名空间
+ DuckPhp\Core\Runtime
    - 'path_runtime' => 'runtime',
        
    - 'use_output_buffer' => false,
        使用 OB 函数缓冲数据
+ DuckPhp\Core\Logger
    - 'log_file_template' => 'log_%Y-%m-%d_%H_%i.log',
        日志文件名模板 
    - 'log_prefix' => 'DuckPhpLog',
        日志前缀
    - 'path' => '',
        工程路径
    - 'path_log' => 'runtime',
        日志目录路径
+ DuckPhp\Core\SuperGlobal
    - 'superglobal_auto_define' => false,
        初始化时定义  `__SUPERGLOBAL_CONTEXT`宏
+ DuckPhp\Core\SystemWrapper
+ DuckPhp\Core\View
    - 'path' => '',
        工程路径
    - 'path_view' => 'view',
        视图路径
    - 'view_skip_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Component\DbManager
    - 'database' => NULL,
        数据库，单一数据库配置
    - 'database_class' => '',
        数据库，默认为 Db::class。
    - 'database_driver' => '',
        
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
+ DuckPhp\Component\RedisManager
    - 'redis' => NULL,
        redis 设置
    - 'redis_list' => NULL,
        redis 列表
    - 'redis_list_reload_by_setting' => true,
        是否从设置里再入 redis 设置
    - 'redis_list_try_single' => true,
        redis 设置是否同时支持单个和多个
+ DuckPhp\GlobalAdmin\GlobalAdmin
+ DuckPhp\GlobalUser\GlobalUser
+ DuckPhp\Component\RouteHookPathInfoCompat
    - 'path_info_compact_action_key' => '_r',
        无PATH_INFO兼容，替代的 action
    - 'path_info_compact_class_key' => '',
        无PATH_INFO兼容，替代的 class
    - 'path_info_compact_enable' => true,
        PATH_INFO 兼容模式
+ DuckPhp\Component\RouteHookCheckStatus
    - 'error_maintain' => NULL,
        维修页面view ，类似 error_404
    - 'error_need_install' => NULL,
        需要安装的页面
+ DuckPhp\Component\RouteHookRewrite
    - 'controller_url_prefix' => '',
        
    - 'rewrite_map' => array ( ),
        路由重写，重写映射表
+ DuckPhp\Component\RouteHookRouteMap
    - 'controller_url_prefix' => '',
        
    - 'route_map' => array ( ),
        路由映射，在默认路由失败后执行的路由映射
    - 'route_map_important' => array ( ),
        路由映射，在默认路由前执行的路由映射
+ DuckPhp\Component\RouteHookResource
    - 'controller_resource_prefix' => '',
        
    - 'controller_url_prefix' => '',
        
    - 'path' => '',
        工程路径
    - 'path_document' => 'public',
        文档路径
    - 'path_resource' => 'res',
        资源目录
+ DuckPhp\Ext\CallableView
    - 'callable_view_class' => NULL,
        CallableView 限定于此类内 callable_view_class 。
    - 'callable_view_foot' => NULL,
        CallableView 页脚函数
    - 'callable_view_head' => NULL,
        CallableView 页眉函数
    - 'callable_view_is_object_call' => true,
        
    - 'callable_view_prefix' => NULL,
        CallableView 视图方法前缀
    - 'callable_view_skip_replace' => false,
        CallableView 是否替换默认 View
    - 'path' => '',
        工程路径
    - 'path_view' => 'view',
        视图路径
    - 'view_skip_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Ext\DuckPhpInstaller
    - 'autoloader' => 'vendor/autoload.php',
        
    - 'force' => false,
        
    - 'help' => false,
        
    - 'namespace' => '',
        命名空间
    - 'path' => '',
        工程路径
    - 'verbose' => false,
        
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
    - 'view_skip_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Ext\ExceptionWrapper
+ DuckPhp\Ext\FinderForController
+ DuckPhp\Ext\JsonRpcClientBase
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
+ DuckPhp\Ext\JsonView
    - 'json_view_skip_replace' => false,
        jsonview, 跳过替换默认的View
    - 'json_view_skip_vars' => array ( ),
        jsonview, 排除变量
    - 'path' => '',
        工程路径
    - 'path_view' => 'view',
        视图路径
    - 'view_skip_notice_error' => true,
        关闭  View 视图的 notice 警告，以避免麻烦的处理。
+ DuckPhp\Ext\MiniRoute
    - 'controller_class_map' => array ( ),
        
    - 'controller_class_postfix' => '',
        
    - 'controller_method_prefix' => '',
        
    - 'controller_path_ext' => '',
        
    - 'controller_resource_prefix' => '',
        
    - 'controller_url_prefix' => '',
        
    - 'controller_welcome_class' => 'Main',
        
    - 'controller_welcome_class_visible' => false,
        
    - 'controller_welcome_method' => 'index',
        
    - 'namespace' => '',
        命名空间
    - 'namespace_controller' => 'Controller',
        控制器命名空间
+ DuckPhp\Ext\Misc
    - 'path' => '',
        工程路径
    - 'path_lib' => 'lib',
        导入的 Import 库目录路径
+ DuckPhp\Ext\MyFacadesAutoLoader
    - 'facades_enable_autoload' => true,
        门面扩展，门面类启用自动加载
    - 'facades_map' => array ( ),
        门面扩展，门面类门面映射
    - 'facades_namespace' => 'MyFacades',
        门面扩展，门面类前缀
+ DuckPhp\Ext\MyFacadesBase
+ DuckPhp\Ext\MyMiddlewareManager
    - 'middleware' => array ( ),
        middelware 放的是回调列表
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
+ DuckPhp\Ext\RouteHookManager
+ DuckPhp\Ext\StaticReplacer
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
###  DuckPhp\HttpServer\HttpServer
    'host' => '127.0.0.1',
    'port' => '8080',
    'path' => '',
    'path_document' => 'public',
### DuckPhp\Component\Pager

    'url' => null,
    'current' => null,
    'page_size' => 30,
    'page_key' => 'page',
    'rewrite' => null,
    'pager_context_class' => null,