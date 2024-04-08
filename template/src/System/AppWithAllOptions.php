<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\System;

use DuckPhp\DuckPhp;
use ProjectNameTemplate\Controller\ExceptionReporter;

class AppWithAllOptions extends DuckPhp
{
    //@override
    public $options = [
        //'is_debug' => true, // debug switch
        //'path_info_compact_enable' => false,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'exception_reporter' => ExceptionReporter::class,
        //'ext' => [],
    ];
    //@override
    protected function onInit()
    {
        // @autogen by tests/genoptions.php
        // ---- 脚本生成,下面是可用的默认选项 ---- 

        // 别名，目前只用于视图目录 (DuckPhp\Core\App)
        // $options['alias'] = NULL;

        // 子应用，保存 类名=>选项对 (DuckPhp\Core\App)
        // $options['app'] = array ( );

        // 管理员类名，设置这个类以实现默认的管理员类 ()
        // $options['class_admin'] = '';

        // 用户类名，设置这个类以实现默认的用户类 ()
        // $options['class_user'] = '';

        //  (DuckPhp\Core\App)
        // $options['cli_command_class'] = NULL;

        // 命令行,默认调用指令 (DuckPhp\Core\Console)
        // $options['cli_command_default'] = 'help';

        //  (DuckPhp\Core\Console)
        // $options['cli_command_group'] = array ( );

        //  (DuckPhp\Core\App)
        // $options['cli_command_method_prefix'] = 'command_';

        //  (DuckPhp\Core\App)
        // $options['cli_command_prefix'] = NULL;

        // 启用命令行模式 (DuckPhp\Core\App)
        // $options['cli_enable'] = true;

        // 输出时候关闭资源输出（仅供第三方扩展参考 (DuckPhp\Core\App)
        // $options['close_resource_at_output'] = false;

        //  (DuckPhp\Core\Route)
        // $options['controller_class_adjust'] = '';

        //  (DuckPhp\Core\Route)
        // $options['controller_class_base'] = '';

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_class_map'] = array ( );

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_class_postfix'] = '';

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_method_prefix'] = '';

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_path_ext'] = '';

        //  (DuckPhp\Core\Route)
        // $options['controller_prefix_post'] = 'do_';

        //  (DuckPhp\Core\Route, DuckPhp\Component\RouteHookResource, DuckPhp\Ext\MiniRoute)
        // $options['controller_resource_prefix'] = '';

        //  (DuckPhp\Core\Route, DuckPhp\Component\RouteHookRouteMap, DuckPhp\Component\RouteHookRewrite, DuckPhp\Component\RouteHookResource, DuckPhp\Ext\MiniRoute)
        // $options['controller_url_prefix'] = '';

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_welcome_class'] = 'Main';

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_welcome_class_visible'] = false;

        //  (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute)
        // $options['controller_welcome_method'] = 'index';

        // 数据库，单一数据库配置 (DuckPhp\Component\DbManager)
        // $options['database'] = NULL;

        // 数据库，默认为 Db::class。 (DuckPhp\Component\DbManager)
        // $options['database_class'] = '';

        // 数据库，多数据库配置 (DuckPhp\Component\DbManager)
        // $options['database_list'] = NULL;

        // 数据库，从设置里再入数据库配置 (DuckPhp\Component\DbManager)
        // $options['database_list_reload_by_setting'] = true;

        // 数据库，尝试使用单一数据库配置 (DuckPhp\Component\DbManager)
        // $options['database_list_try_single'] = true;

        // 数据库，记录sql 错误等级 (DuckPhp\Component\DbManager)
        // $options['database_log_sql_level'] = 'debug';

        // 数据库，记录sql 查询 (DuckPhp\Component\DbManager)
        // $options['database_log_sql_query'] = false;

        // 发生异常时候记录日志 (DuckPhp\Core\App)
        // $options['default_exception_do_log'] = true;

        // 默认的异常处理回调 (DuckPhp\Core\ExceptionManager)
        // $options['default_exception_handler'] = NULL;

        // 调试错误的回调 (DuckPhp\Core\ExceptionManager)
        // $options['dev_error_handler'] = NULL;

        // 404 错误处理的View或者回调，仅根应用有效 (DuckPhp\Core\App)
        // $options['error_404'] = NULL;

        // 500 错误处理的View或者回调，仅根应用有效 (DuckPhp\Core\App)
        // $options['error_500'] = NULL;

        // 异常报告类 (DuckPhp\Core\App)
        // $options['exception_reporter'] = NULL;

        // 异常报告仅针对的异常 (DuckPhp\Core\App)
        // $options['exception_reporter_for_class'] = NULL;

        //  (DuckPhp\Core\App)
        // $options['ext'] = array ( );

        // 配置文件名字 ()
        // $options['ext_options_file'] = 'config/DuckPhpApps.config.php';

        //  ()
        // $options['ext_options_file_enable'] = true;

        // 抓取调试错误 (DuckPhp\Core\ExceptionManager)
        // $options['handle_all_dev_error'] = true;

        // 抓取全部异常 (DuckPhp\Core\ExceptionManager)
        // $options['handle_all_exception'] = true;

        //  (DuckPhp\Core\ExceptionManager)
        // $options['handle_exception_on_init'] = true;

        // HTML编码函数 (DuckPhp\Core\App)
        // $options['html_handler'] = NULL;

        // 是否调试模式 (DuckPhp\Core\App, DuckPhp\Ext\StrictCheck)
        // $options['is_debug'] = false;

        // 语言编码回调 (DuckPhp\Core\App)
        // $options['lang_handler'] = NULL;

        // 日志文件名模板  (DuckPhp\Core\Logger)
        // $options['log_file_template'] = 'log_%Y-%m-%d_%H_%i.log';

        // 日志前缀 (DuckPhp\Core\Logger)
        // $options['log_prefix'] = 'DuckPhpLog';

        // 命名空间 (DuckPhp\Core\App, DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute, DuckPhp\Ext\RouteHookApiServer, DuckPhp\Ext\StrictCheck)
        // $options['namespace'] = '';

        // 控制器命名空间 (DuckPhp\Core\Route, DuckPhp\Ext\MiniRoute, DuckPhp\Ext\StrictCheck)
        // $options['namespace_controller'] = 'Controller';

        // 初始化完成后处理回调 (DuckPhp\Core\App)
        // $options['on_init'] = NULL;

        // 如果这个选项的类存在，则且新建 `override_class` 初始化 (DuckPhp\Core\App)
        // $options['override_class'] = NULL;

        // `override_class`切过去的时候会在此保存旧的`override_class` (DuckPhp\Core\App)
        // $options['override_class_from'] = NULL;

        // 工程路径 (DuckPhp\Core\App, DuckPhp\Core\Logger, DuckPhp\Core\View, DuckPhp\Component\RouteHookResource, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView, DuckPhp\Ext\JsonView, DuckPhp\Ext\Misc)
        // $options['path'] = '';

        // 文档路径 (DuckPhp\Component\RouteHookResource)
        // $options['path_document'] = 'public';

        // 无PATH_INFO兼容，替代的 action (DuckPhp\Component\RouteHookPathInfoCompat)
        // $options['path_info_compact_action_key'] = '_r';

        // 无PATH_INFO兼容，替代的 class (DuckPhp\Component\RouteHookPathInfoCompat)
        // $options['path_info_compact_class_key'] = '';

        // PATH_INFO 兼容模式 (DuckPhp\Component\RouteHookPathInfoCompat)
        // $options['path_info_compact_enable'] = true;

        // 日志目录路径 (DuckPhp\Core\Logger)
        // $options['path_log'] = 'runtime';

        // 资源目录 (DuckPhp\Component\RouteHookResource)
        // $options['path_resource'] = 'res';

        //  (DuckPhp\Core\App, DuckPhp\Core\Runtime)
        // $options['path_runtime'] = 'runtime';

        // 视图路径 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView, DuckPhp\Ext\JsonView)
        // $options['path_view'] = 'view';

        // redis 设置 (DuckPhp\Component\RedisManager)
        // $options['redis'] = NULL;

        // redis 列表 (DuckPhp\Component\RedisManager)
        // $options['redis_list'] = NULL;

        // 是否从设置里再入 redis 设置 (DuckPhp\Component\RedisManager)
        // $options['redis_list_reload_by_setting'] = true;

        // redis 设置是否同时支持单个和多个 (DuckPhp\Component\RedisManager)
        // $options['redis_list_try_single'] = true;

        // 路由重写，重写映射表 (DuckPhp\Component\RouteHookRewrite)
        // $options['rewrite_map'] = array ( );

        // 路由映射，在默认路由失败后执行的路由映射 (DuckPhp\Component\RouteHookRouteMap)
        // $options['route_map'] = array ( );

        // 路由映射，在默认路由前执行的路由映射 (DuckPhp\Component\RouteHookRouteMap)
        // $options['route_map_important'] = array ( );

        // Session 前缀 ()
        // $options['session_prefix'] = NULL;

        // 设置文件名。仅根应用有效 (DuckPhp\Core\App)
        // $options['setting_file'] = 'config/DuckPhpSettings.config.php';

        // 使用设置文件: $path/$path_config/$setting_file.php 仅根应用有效 (DuckPhp\Core\App)
        // $options['setting_file_enable'] = true;

        // 如果设置文件不存在也不报错 仅根应用有效 (DuckPhp\Core\App)
        // $options['setting_file_ignore_exists'] = true;

        // 不处理 404 ，用于配合其他框架使用。 (DuckPhp\Core\App)
        // $options['skip_404'] = false;

        // 不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用 (DuckPhp\Core\App)
        // $options['skip_exception_check'] = false;

        // 初始化时定义  `__SUPERGLOBAL_CONTEXT`宏 (DuckPhp\Core\SuperGlobal)
        // $options['superglobal_auto_define'] = false;

        // 系统的异常调试回调 (DuckPhp\Core\ExceptionManager)
        // $options['system_exception_handler'] = NULL;

        // 数据库表前缀 ()
        // $options['table_prefix'] = NULL;

        // 使用 .env 文件。 仅根应用有效 (DuckPhp\Core\App)
        // $options['use_env_file'] = false;

        // 使用 OB 函数缓冲数据 (DuckPhp\Core\Runtime)
        // $options['use_output_buffer'] = false;

        // 关闭  View 视图的 notice 警告，以避免麻烦的处理。 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView, DuckPhp\Ext\JsonView)
        // $options['view_skip_notice_error'] = true;

        // ---- 下面是默认未使用的扩展 ----

        /*
        $options['ext']['DuckPhp\\Ext\\CallableView'] = true;
            // CallableView 限定于此类内 callable_view_class 。
            $options['callable_view_class'] = NULL;

            // CallableView 页脚函数
            $options['callable_view_foot'] = NULL;

            // CallableView 页眉函数
            $options['callable_view_head'] = NULL;

            // 
            $options['callable_view_is_object_call'] = true;

            // CallableView 视图方法前缀
            $options['callable_view_prefix'] = NULL;

            // CallableView 是否替换默认 View
            $options['callable_view_skip_replace'] = false;

            // 【共享】工程路径
            // $options['path'] = '';

            // 【共享】视图路径
            // $options['path_view'] = 'view';

            // 【共享】关闭  View 视图的 notice 警告，以避免麻烦的处理。
            // $options['view_skip_notice_error'] = true;

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\EmptyView'] = true;
            // 空视图扩展，_Show 的时候给的 $data 的key
            $options['empty_view_key_view'] = 'view';

            // 空视图扩展，view 为这个的时候跳过显示
            $options['empty_view_key_wellcome_class'] = 'Main/';

            // 空视图扩展，替换默认的 view
            $options['empty_view_skip_replace'] = false;

            // 空视图扩展，剪掉 view。 
            $options['empty_view_trim_view_wellcome'] = true;

            // 【共享】工程路径
            // $options['path'] = '';

            // 【共享】视图路径
            // $options['path_view'] = 'view';

            // 【共享】关闭  View 视图的 notice 警告，以避免麻烦的处理。
            // $options['view_skip_notice_error'] = true;

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\ExceptionWrapper'] = true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\JsonRpcClientBase'] = true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\JsonRpcExt'] = true;
            // jsonrpc 后端地址
            $options['jsonrpc_backend'] = 'https://127.0.0.1';

            // jsonrpc Token 处理
            $options['jsonrpc_check_token_handler'] = NULL;

            // jsonrpc 是否要自动加载
            $options['jsonrpc_enable_autoload'] = true;

            // jsonrpc 是否调试
            $options['jsonrpc_is_debug'] = false;

            // jsonrpc 默认jsonrpc 的命名空间
            $options['jsonrpc_namespace'] = 'JsonRpc';

            // jsonrpc 限制指定接口或者基类——todo 调整名字
            $options['jsonrpc_service_interface'] = '';

            // jsonrpc 限定服务命名空间
            $options['jsonrpc_service_namespace'] = '';

            // jsonrpc 封装调整
            $options['jsonrpc_wrap_auto_adjust'] = true;

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\JsonView'] = true;
            // jsonview, 跳过替换默认的View
            $options['json_view_skip_replace'] = false;

            // jsonview, 排除变量
            $options['json_view_skip_vars'] = array ( );

            // 【共享】工程路径
            // $options['path'] = '';

            // 【共享】视图路径
            // $options['path_view'] = 'view';

            // 【共享】关闭  View 视图的 notice 警告，以避免麻烦的处理。
            // $options['view_skip_notice_error'] = true;

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\MiniRoute'] = true;
            // 【共享】
            // $options['controller_class_map'] = array ( );

            // 【共享】
            // $options['controller_class_postfix'] = '';

            // 【共享】
            // $options['controller_method_prefix'] = '';

            // 【共享】
            // $options['controller_path_ext'] = '';

            // 【共享】
            // $options['controller_resource_prefix'] = '';

            // 【共享】
            // $options['controller_url_prefix'] = '';

            // 【共享】
            // $options['controller_welcome_class'] = 'Main';

            // 【共享】
            // $options['controller_welcome_class_visible'] = false;

            // 【共享】
            // $options['controller_welcome_method'] = 'index';

            // 【共享】命名空间
            // $options['namespace'] = '';

            // 【共享】控制器命名空间
            // $options['namespace_controller'] = 'Controller';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\Misc'] = true;
            // 【共享】工程路径
            // $options['path'] = '';

            // 导入的 Import 库目录路径
            $options['path_lib'] = 'lib';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\MyFacadesAutoLoader'] = true;
            // 门面扩展，门面类启用自动加载
            $options['facades_enable_autoload'] = true;

            // 门面扩展，门面类门面映射
            $options['facades_map'] = array ( );

            // 门面扩展，门面类前缀
            $options['facades_namespace'] = 'MyFacades';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\MyFacadesBase'] = true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\MyMiddlewareManager'] = true;
            // middelware 放的是回调列表
            $options['middleware'] = array ( );

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookApiServer'] = true;
            // API服务器， 404 引发异常的模式
            $options['api_server_404_as_exception'] = false;

            // API服务器，限定的接口或基类，  ~ 开始的表示是当前命名空间
            $options['api_server_base_class'] = '';

            // API服务器， 限定类名后缀
            $options['api_server_class_postfix'] = '';

            // API服务器， 命名空间，配合 namespace选项使用
            $options['api_server_namespace'] = 'Api';

            // API服务器， 使用可变单例模式，方便替换实现
            $options['api_server_use_singletonex'] = false;

            // 【共享】命名空间
            // $options['namespace'] = '';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookDirectoryMode'] = true;
            // 目录模式的基准路径
            $options['mode_dir_basepath'] = '';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookFunctionRoute'] = true;
            // 函数模式路由，开关
            $options['function_route'] = false;

            // 函数模式路由，404 是否由 index 来执行
            $options['function_route_404_to_index'] = false;

            // 函数模式路由，函数前缀
            $options['function_route_method_prefix'] = 'action_';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookManager'] = true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\StaticReplacer'] = true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\StrictCheck'] = true;
            // 控制器基类
            $options['controller_base_class'] = NULL;

            // 【共享】是否调试模式
            // $options['is_debug'] = false;

            // 【共享】命名空间
            // $options['namespace'] = '';

            // 严格检查扩展，业务类命名空间
            $options['namespace_business'] = '';

            // 【共享】控制器命名空间
            // $options['namespace_controller'] = 'Controller';

            // 严格检查扩展，模型命名空间
            $options['namespace_model'] = '';

            // 严格检查扩展，跳过批量业务的类
            $options['postfix_batch_business'] = 'BatchBusiness';

            // 严格检查扩展，跳过非业务类
            $options['postfix_business_lib'] = 'Lib';

            // 严格检查扩展，混合模型后缀
            $options['postfix_ex_model'] = 'ExModel';

            // 严格检查扩展，模型后缀
            $options['postfix_model'] = 'Model';

            // 严格检查扩展，不用传输过来的 app类，而是特别指定类
            $options['strict_check_context_class'] = NULL;

            // 严格检查模式开启
            $options['strict_check_enable'] = true;

        //*/
        // @autogen end
        // your code here
    }
    /**
     * console command sample
     */
    public function command_hello()
    {
        echo "hello ". static::class ."\n";
    }
}
