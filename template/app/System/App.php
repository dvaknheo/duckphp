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
        //'skip_setting_file' => true,
        'skip_setting_file' => true, // @DUCKPHP_DELETE
        
        //'is_debug' => true,
        'is_debug' => true, // @DUCKPHP_DELETE
        
        //'platform' => true,
        'platform' => 'platform', // @DUCKPHP_DELETE
        
        
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
        'error_debug' => '_sys/error_debug',
    ];
    public function __construct()
    {
        parent::__construct();
        $options = [];

        // deafalt options;
        

        // $options['all_config'] = array ( );
            // 所有配置 (DuckPhp\Core\Configer)
        // $options['autoload_cache_in_cli'] = false;
            // 在 cli 下开启缓存模式 (DuckPhp\Core\AutoLoader)
        // $options['autoload_path_namespace_map'] = array ( );
            // 自动加载的目录和命名空间映射 (DuckPhp\Core\AutoLoader)
        // $options['close_resource_at_output'] = true;
            // 在输出前关闭资源（DB,Redis） (DuckPhp\DuckPhp)
        // $options['config_ext_files'] = array ( );
            // 额外的配置文件数组 (DuckPhp\Core\Configer)
        // $options['controller_base_class'] = NULL;
            // 控制器基类 (DuckPhp\Core\Route, DuckPhp\Ext\StrictCheck)
        // $options['controller_class_postfix'] = '';
            // 控制器类名后缀 (DuckPhp\Core\Route)
        // $options['controller_enable_slash'] = false;
            // 激活兼容后缀的 /  (DuckPhp\Core\Route)
        // $options['controller_hide_boot_class'] = false;
            // 控制器标记，隐藏特别的入口 (DuckPhp\Core\Route)
        // $options['controller_methtod_for_miss'] = '_missing';
            // 控制器，缺失方法的调用方法 (DuckPhp\Core\Route)
        // $options['controller_path_ext'] = '';
            // 扩展名，比如你要 .html (DuckPhp\Core\Route)
        // $options['controller_prefix_post'] = 'do_';
            // 控制器，POST 方法前缀 (DuckPhp\Core\Route)
        // $options['controller_welcome_class'] = 'Main';
            // 控制器默认欢迎方法 (DuckPhp\Core\Route)
        // $options['database'] = NULL;
            // 单一数据库配置 (DuckPhp\Ext\DbManager)
        // $options['database_list'] = NULL;
            // 数据库列表 (DuckPhp\Ext\DbManager)
        // $options['database_list_reload_by_setting'] = true;
            // 从设置里读取数据库列表 (DuckPhp\Ext\DbManager)
        // $options['database_list_try_single'] = true;
            // 尝试使用单一数据配置 (DuckPhp\Ext\DbManager)
        // $options['database_log_sql_level'] = 'debug';
            // 记录sql 错误等级 (DuckPhp\Ext\DbManager)
        // $options['database_log_sql_query'] = false;
            // 记录sql 查询 (DuckPhp\Ext\DbManager)
        // $options['default_exception_do_log'] = true;
            // 错误的时候打开日志 (DuckPhp\DuckPhp)
        // $options['default_exception_self_display'] = true;
            // 错误的时候打开日志 (DuckPhp\DuckPhp)
        // $options['error_404'] = NULL;
            // 404 页面 (DuckPhp\DuckPhp)
        // $options['error_500'] = NULL;
            // 500 页面 (DuckPhp\DuckPhp)
        // $options['error_debug'] = NULL;
            // 错误调试页面 (DuckPhp\DuckPhp)
        // $options['ext'] = array ( );
            // 默认开启的扩展 (DuckPhp\DuckPhp)
        // $options['is_debug'] = false;
            // 是否调试状态 (DuckPhp\DuckPhp, DuckPhp\Ext\StrictCheck)
        // $options['key_for_action'] = '_r';
            // GET 方法名的 key (DuckPhp\Ext\RouteHookPathInfoByGet)
        // $options['key_for_module'] = '';
            // GET 模式 类名的 key (DuckPhp\Ext\RouteHookPathInfoByGet)
        // $options['log_file_template'] = 'log_%Y-%m-%d_%H_%i.log';
            // 日志文件名模板 (DuckPhp\Core\Logger)
        // $options['log_prefix'] = 'DuckPhpLog';
            // 日志前缀 (DuckPhp\Core\Logger)
        // $options['namespace'] = 'LazyToChange';
            // 命名空间 (DuckPhp\DuckPhp, DuckPhp\Core\AutoLoader, DuckPhp\Core\Route, DuckPhp\Ext\StrictCheck)
        // $options['namespace_controller'] = 'Controller';
            // 控制器的命名空间 (DuckPhp\Core\Route, DuckPhp\Ext\StrictCheck)
        // $options['path'] = '';
            // 基础目录 (DuckPhp\DuckPhp, DuckPhp\Core\AutoLoader, DuckPhp\Core\Configer, DuckPhp\Core\Logger, DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView, DuckPhp\Ext\Misc)
        // $options['path_config'] = 'config';
            // 配置目录 (DuckPhp\Core\Configer)
        // $options['path_log'] = 'logs';
            // 日志目录 (DuckPhp\Core\Logger)
        // $options['path_view'] = 'view';
            // 视图目录 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView)
        // $options['path_view_override'] = '';
            // 覆盖视图目录 (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView)
        // $options['platform'] = '';
            // 平台 (DuckPhp\DuckPhp)
        // $options['route_map'] = array ( );
            // 路由映射 (DuckPhp\Ext\RouteHookRouteMap)
        // $options['route_map_by_config_name'] = '';
            // 路由配置名，使用配置模式用路由 (DuckPhp\Ext\RouteHookRouteMap)
        // $options['route_map_important'] = array ( );
            // 重要路由映射 (DuckPhp\Ext\RouteHookRouteMap)
        // $options['setting'] = array ( );
            // 设置，预先载入的设置 (DuckPhp\Core\Configer)
        // $options['setting_file'] = 'setting';
            // 设置文件 (DuckPhp\Core\Configer)
        // $options['skip_404_handler'] = false;
            // 跳过404处理 (DuckPhp\DuckPhp)
        // $options['skip_app_autoload'] = false;
            // 跳过 自动加载 (DuckPhp\Core\AutoLoader)
        // $options['skip_env_file'] = true;
            // 跳过 .env 文件 (DuckPhp\Core\Configer)
        // $options['skip_exception_check'] = false;
            // 跳过异常检查 (DuckPhp\DuckPhp)
        // $options['skip_fix_path_info'] = false;
            // 跳过 PATH_INFO 修复 (DuckPhp\DuckPhp)
        // $options['skip_setting_file'] = false;
            // 跳过设置文件 (DuckPhp\Core\Configer)
        // $options['skip_view_notice_error'] = true;
            // 跳过 View 视图的 notice (DuckPhp\Core\View, DuckPhp\Ext\CallableView, DuckPhp\Ext\EmptyView)
        // $options['use_flag_by_setting'] = true;
            // 从设置文件里再入is_debug,platform.  (DuckPhp\DuckPhp)
        // $options['use_output_buffer'] = false;
            // 使用 OB 函数缓冲数据 (DuckPhp\Core\RuntimeState)
        // $options['use_path_info_by_get'] = false;
            // 使用 _GET 模拟无 PathInfo 配置 (DuckPhp\Ext\RouteHookPathInfoByGet)
        // $options['use_short_functions'] = true;
            // 使用短函数， \_\_url, \_\_h 等 ，详见 Core\Functions.php (DuckPhp\DuckPhp)
        // $options['use_super_global'] = true;
            // 使用super_global 类。关闭以节约性能 (DuckPhp\DuckPhp)

 // 下面是默认没开的扩展 
        /*
        $options['ext']['DuckPhp\\Ext\\CallableView'] = true;
            $options['callable_view_class'] = NULL;
                // callableview 视图类
            $options['callable_view_foot'] = NULL;
                // callableview 页脚
            $options['callable_view_head'] = NULL;
                // callableview 页眉
            $options['callable_view_prefix'] = NULL;
                // callableview 视图函数模板
            $options['callable_view_skip_replace'] = false;
                // callableview 可调用视图跳过默认视图替换
            // $options['path'] = '';
                // 【共享】基础目录
            // $options['path_view'] = 'view';
                // 【共享】视图目录
            // $options['path_view_override'] = '';
                // 【共享】覆盖视图目录
            // $options['skip_view_notice_error'] = true;
                // 【共享】跳过 View 视图的 notice
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\EmptyView'] = true;
            $options['empty_view_key_view'] = 'view';
                // 给View 的key
            $options['empty_view_key_wellcome_class'] = 'Main/';
                // 默认的 Main
            $options['empty_view_skip_replace'] = false;
                // 跳过默认的view
            $options['empty_view_trim_view_wellcome'] = true;
                // 跳过 Main/
            // $options['path'] = '';
                // 【共享】基础目录
            // $options['path_view'] = 'view';
                // 【共享】视图目录
            // $options['path_view_override'] = '';
                // 【共享】覆盖视图目录
            // $options['skip_view_notice_error'] = true;
                // 【共享】跳过 View 视图的 notice
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\FacadesAutoLoader'] = true;
            $options['facades_enable_autoload'] = true;
                // 使用 facdes 的 autoload
            $options['facades_map'] = array ( );
                // facade 映射
            $options['facades_namespace'] = 'Facades';
                // facades 开始的namespace
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\JsonRpcExt'] = true;
            $options['jsonrpc_backend'] = 'https://127.0.0.1';
                // json 的后端
            $options['jsonrpc_check_token_handler'] = NULL;
                // 设置 token 检查回调
            $options['jsonrpc_enable_autoload'] = true;
                // json 启用 autoload
            $options['jsonrpc_is_debug'] = false;
                // jsonrpc 是否开启 debug 模式
            $options['jsonrpc_namespace'] = 'JsonRpc';
                // jsonrpc 默认的命名空间
            $options['jsonrpc_service_interface'] = '';
                // json 服务接口
            $options['jsonrpc_service_namespace'] = '';
                // json 命名空间
            $options['jsonrpc_wrap_auto_adjust'] = true;
                // jsonrpc 自动调整 wrap
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\Misc'] = true;
            // $options['path'] = '';
                // 【共享】基础目录
            $options['path_lib'] = 'lib';
                // 库目录
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisCache'] = true;
            $options['redis_cache_prefix'] = '';
                //  redis cache 缓存前缀
            $options['redis_cache_skip_replace'] = false;
                // redis cache 跳过 默认 cache替换
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisManager'] = true;
            $options['redis'] = NULL;
                // 单一Redisc配置
            $options['redis_list'] = NULL;
                //  redis 配置列表
            $options['redis_list_reload_by_setting'] = true;
                //  redis 使用 settting 文件
            $options['redis_list_try_single'] = true;
                // 尝试使用单一Redis配置
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookApiServer'] = true;
            $options['api_class_base'] = 'BaseApi';
                // api 服务接口
            $options['api_class_prefix'] = 'Api_';
                // api类的前缀
            $options['api_config_file'] = '';
                // api配置文件
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookDirectoryMode'] = true;
            $options['mode_dir_basepath'] = '';
                // 目录模式的基类
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookRewrite'] = true;
            $options['rewrite_map'] = array ( );
                // 目录重写映射
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\StrictCheck'] = true;
            // $options['controller_base_class'] = NULL;
                // 【共享】控制器基类
            // $options['is_debug'] = false;
                // 【共享】是否调试状态
            // $options['namespace'] = 'LazyToChange';
                // 【共享】命名空间
            $options['namespace_business'] = '';
                // strict_check 的business目录
            // $options['namespace_controller'] = 'Controller';
                // 【共享】控制器的命名空间
            $options['namespace_model'] = '';
                // strict_check 的model 目录
            $options['postfix_batch_business'] = 'BatchBusiness';
                // batchbusiness
            $options['postfix_business_lib'] = 'Lib';
                //  businesslib
            $options['postfix_ex_model'] = 'ExModel';
                // ExModel
            $options['postfix_model'] = 'Model';
                // model
            $options['strict_check_context_class'] = NULL;
                // 不用传输过来的 app类，而是特别指定类
            $options['strict_check_enable'] = true;
                // 是否开启 strict chck
        //*/

        $this->options = array_replace_recursive($this->options, $options);
    }
    //@override
    protected function onPrepare()
    {
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
}
