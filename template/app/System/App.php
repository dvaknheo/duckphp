<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    //@override
    public $options = [
        'is_debug' => true,        
        // 'use_setting_file' => true,
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        
        //'path_info_compact_enable' => false,        
    ];
    /**
     * console command sample
     */
    public function command_hello()
    {
        // 多一个 hello 的命令
        echo "override you the routes\n";
    }
    //@override
    protected function onPrepare()
    {
        //your code here
    }
    //@override
    protected function onInit()
    {
        // your code here
    }
    //@override
    protected function onRun()
    {
        // your code here
    }
    public function __construct()
    {
        parent::__construct();
        $options = [];

        // @autogen by tests/genoptions.php
        // ---- 脚本生成,下面是可用的默认选项 ---- 

        // 默认自带的所有配置 (DuckPhp\Core\Configer)
        // $options['all_config'] = array ( );

        // 在命令行模式下开启 opcache 缓存 (DuckPhp\Core\AutoLoader)
        // $options['autoload_cache_in_cli'] = false;

        // psr4 风格自动加载路径和命名空间映射 (DuckPhp\Core\AutoLoader)
        // $options['autoload_path_namespace_map'] = array ( );

        // 输出时候关闭资源输出（仅供第三方扩展参考 (DuckPhp\Core\App)
        // $options['close_resource_at_output'] = false;

        // 额外的配置文件数组，用于 AppPluginTrait (DuckPhp\Core\Configer)
        // $options['config_ext_file_map'] = array ( );

        // 控制器基类 (DuckPhp\Core\Route, DuckPhp\Ext\StrictCheck)
        // $options['controller_base_class'] = NULL;

        // 控制器，类映射，用于替换 (DuckPhp\Core\Route)
        // $options['controller_class_map'] = array ( );

        // 控制器，控制器类名后缀 (DuckPhp\Core\Route)
        // $options['controller_class_postfix'] = '';

        // 控制器，允许结尾的 / (DuckPhp\Core\Route)
        // $options['controller_enable_slash'] = false;

        // 控制器，隐藏启动的类 (DuckPhp\Core\Route)
        // $options['controller_hide_boot_class'] = false;

        // 控制器，方法丢失调用的方法。 (DuckPhp\Core\Route)
        // $options['controller_methtod_for_miss'] = '__missing';

        // 控制器，后缀,如 .html (DuckPhp\Core\Route)
        // $options['controller_path_ext'] = '';

        // 控制器，路由的前缀，只处理限定前缀的 PATH_INFO (DuckPhp\Core\Route)
        // $options['controller_path_prefix'] = '';

        // 控制器，POST 的方法会在方法名前加前缀 do_ (DuckPhp\Core\Route)
        // $options['controller_prefix_post'] = 'do_';

        // 控制器，禁止直接访问控制器静态方法 (DuckPhp\Core\Route)
        // $options['controller_stop_static_method'] = true;

        // 控制器，严格模式，区分大小写 (DuckPhp\Core\Route)
        // $options['controller_strict_mode'] = true;

        // 控制器，使用可变单例模式 (DuckPhp\Core\Route)
        // $options['controller_use_singletonex'] = false;

        // 控制器，欢迎类，默认欢迎类是  Main 。 (DuckPhp\Core\Route)
        // $options['controller_welcome_class'] = 'Main';

        // 数据库，单一数据库配置 (DuckPhp\Component\DbManager)
        // $options['database'] = NULL;

        // 数据库，是否扩充方法至助手类 (DuckPhp\Component\DbManager)
        // $options['database_auto_extend_method'] = true;

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

        // 发生异常的时候如有可能，调用异常类的 display() 方法。 (DuckPhp\Core\App)
        // $options['default_exception_self_display'] = true;

        // 404 错误处理 的View或者回调 (DuckPhp\Core\App)
        // $options['error_404'] = NULL;

        // 500 错误处理 View或者回调 (DuckPhp\Core\App)
        // $options['error_500'] = NULL;

        // 调试的View或者回调 (DuckPhp\Core\App)
        // $options['error_debug'] = NULL;

        // **重要选项** 扩展 (DuckPhp\Core\App)
        // $options['ext'] = array ( );

        // injected_helper_map 比较复杂待文档。和助手类映射相关。 v1.2.8-dev (DuckPhp\Core\App)
        // $options['injected_helper_map'] = '';

        // 是否调试模式 (DuckPhp\Core\App, DuckPhp\Ext\StrictCheck)
        // $options['is_debug'] = false;

        // 日志文件名模板  (DuckPhp\Core\Logger)
        // $options['log_file_template'] = 'log_%Y-%m-%d_%H_%i.log';

        // 日志前缀 (DuckPhp\Core\Logger)
        // $options['log_prefix'] = 'DuckPhpLog';

        // 命名空间 (DuckPhp\Core\App, DuckPhp\Core\AutoLoader, DuckPhp\Core\Route, DuckPhp\Ext\RouteHookApiServer, DuckPhp\Ext\StrictCheck)
        // $options['namespace'] = '';

        // 控制器命名空间 (DuckPhp\Core\Route, DuckPhp\Ext\StrictCheck)
        // $options['namespace_controller'] = 'Controller';

        // **重要选项** 如果这个选项的类存在，则在init()的时候会切换到这个类完成后续初始化，并返回这个类的实例。 (DuckPhp\Core\App)
        // $options['override_class'] = '';

        // 工程路径 (DuckPhp\Core\App, DuckPhp\Core\AutoLoader, DuckPhp\Core\Configer, DuckPhp\Core\Logger, DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView, DuckPhp\Ext\Misc)
        // $options['path'] = '';

        // 配置路径 (DuckPhp\Core\Configer)
        // $options['path_config'] = 'config';

        // 无PATH_INFO兼容，替代的 action (DuckPhp\Component\RouteHookPathInfoCompat)
        // $options['path_info_compact_action_key'] = '_r';

        // 无PATH_INFO兼容，替代的 class (DuckPhp\Component\RouteHookPathInfoCompat)
        // $options['path_info_compact_class_key'] = '';

        // 无PATH_INFO兼容，启用 (DuckPhp\Component\RouteHookPathInfoCompat)
        // $options['path_info_compact_enable'] = false;

        // 日志目录路径 (DuckPhp\Core\Logger)
        // $options['path_log'] = 'logs';

        // 自动加载，命名空间的相对路径 (DuckPhp\Core\AutoLoader)
        // $options['path_namespace'] = 'app';

        // 视图路径 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView)
        // $options['path_view'] = 'view';

        // 用于覆盖的路径——用于插件模式 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView)
        // $options['path_view_override'] = '';

        // 平台， 自定义字符，用于 Platform() 方法。 (DuckPhp\Core\App)
        // $options['platform'] = '';

        // 路由映射，在默认路由失败后执行的路由映射 (DuckPhp\Component\RouteHookRouteMap)
        // $options['route_map'] = array ( );

        // 路由映射，扩充方法至助手类 (DuckPhp\Component\RouteHookRouteMap)
        // $options['route_map_auto_extend_method'] = true;

        // 路由映射，从配置中读取  route_map_important 和 route_map (DuckPhp\Component\RouteHookRouteMap)
        // $options['route_map_by_config_name'] = '';

        // 路由映射，在默认路由前执行的路由映射 (DuckPhp\Component\RouteHookRouteMap)
        // $options['route_map_important'] = array ( );

        // 默认自带的设置 (DuckPhp\Core\Configer)
        // $options['setting'] = array ( );

        // 设置文件名。 (DuckPhp\Core\Configer)
        // $options['setting_file'] = 'setting';

        // 如果设置文件不存在也不报错 (DuckPhp\Core\Configer)
        // $options['setting_file_ignore_exists'] = false;

        // 不处理 404 ，用于配合其他框架使用。 (DuckPhp\Core\App)
        // $options['skip_404_handler'] = false;

        // 跳过 app 的加载 (DuckPhp\Core\AutoLoader)
        // $options['skip_app_autoload'] = false;

        // 不在 Run 流程检查异常，把异常抛出外面。用于配合其他框架使用 (DuckPhp\Core\App)
        // $options['skip_exception_check'] = false;

        // 跳过插件模式检查 (DuckPhp\Core\App)
        // $options['skip_plugin_mode_check'] = false;

        // 关闭  View 视图的 notice 警告，以避免麻烦的处理。 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView)
        // $options['skip_view_notice_error'] = true;

        // 使用系统自带自动加载器 (DuckPhp\Core\App)
        // $options['use_autoloader'] = false;

        // 使用 .env 文件 (DuckPhp\Core\Configer)
        // $options['use_env_file'] = false;

        // 从设置文件里再入 is_debug,platform。 (DuckPhp\Core\App)
        // $options['use_flag_by_setting'] = true;

        // 使用 OB 函数缓冲数据 (DuckPhp\Core\RuntimeState)
        // $options['use_output_buffer'] = false;

        // 使用设置文件: $path/$path_config/$setting_file.php (DuckPhp\Core\Configer)
        // $options['use_setting_file'] = false;

        // 使用短函数， \\_\\_url, \\_\\_h 等。 (DuckPhp\Core\App)
        // $options['use_short_functions'] = true;

        // ---- 下面是默认未使用的扩展 ----

        /*
        $options['ext']['DuckPhp\\Ext\\CallableView'] = true;
            // CallableView 限定于此类内 callable_view_class 。
            $options['callable_view_class'] = NULL;

            // CallableView 页脚函数
            $options['callable_view_foot'] = NULL;

            // CallableView 页眉函数
            $options['callable_view_head'] = NULL;

            // CallableView 视图方法前缀
            $options['callable_view_prefix'] = NULL;

            // CallableView 是否替换默认 View
            $options['callable_view_skip_replace'] = false;

            // 【共享】工程路径
            // $options['path'] = '';

            // 【共享】视图路径
            // $options['path_view'] = 'view';

            // 【共享】用于覆盖的路径——用于插件模式
            // $options['path_view_override'] = '';

            // 【共享】关闭  View 视图的 notice 警告，以避免麻烦的处理。
            // $options['skip_view_notice_error'] = true;

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

            // 【共享】用于覆盖的路径——用于插件模式
            // $options['path_view_override'] = '';

            // 【共享】关闭  View 视图的 notice 警告，以避免麻烦的处理。
            // $options['skip_view_notice_error'] = true;

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
        $options['ext']['DuckPhp\\Ext\\Misc'] = true;
            // 是否扩充 Misc 类方法至助手类
            $options['misc_auto_method_extend'] = true;

            // 【共享】工程路径
            // $options['path'] = '';

            // 导入的 Import 库目录路径
            $options['path_lib'] = 'lib';

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisCache'] = true;
            // Redis Cache 的 key 前缀
            $options['redis_cache_prefix'] = '';

            // 跳过默认 cache 替换
            $options['redis_cache_skip_replace'] = false;

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisManager'] = true;
            // redis 设置
            $options['redis'] = NULL;

            // 自动增加Reis扩展方法到助手方法
            $options['redis_auto_extend_method'] = true;

            // redis 列表
            $options['redis_list'] = NULL;

            // 是否从设置里再入 redis 设置
            $options['redis_list_reload_by_setting'] = true;

            // redis 设置是否同时支持单个和多个
            $options['redis_list_try_single'] = true;

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
        $options['ext']['DuckPhp\\Ext\\RouteHookRewrite'] = true;
            // 路由重写，自动扩展方法
            $options['rewrite_auto_extend_method'] = true;

            // 路由重写，重写映射表
            $options['rewrite_map'] = array ( );

        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\StrictCheck'] = true;
            // 【共享】控制器基类
            // $options['controller_base_class'] = NULL;

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
        
        $this->options = array_replace_recursive($this->options, $options);
    }
}
