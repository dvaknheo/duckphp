<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Base;

use DuckPhp\App as SystemApp;

class App extends SystemApp
{
    //@override
    protected $options_project = [
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_exception',
        'error_debug' =>  '_sys/error_debug',
        
        //'is_debug' => true, 
        'is_debug' => true, // @DUCKPHP_DELETE
        //'skip_setting_file' => true,
        'skip_setting_file' => true, // @DUCKPHP_DELETE
    ];
    public function __construct()
    {
        parent::__construct();
        $options = [];

        // deafalt options;
        
        // $options['all_config'] = array ( );
        // $options['config_ext_files'] = array ( );
        // $options['controller_base_class'] = NULL;
        // $options['controller_hide_boot_class'] = false;
        // $options['controller_methtod_for_miss'] = '_missing';
        // $options['controller_postfix'] = '';
        // $options['controller_prefix_post'] = 'do_';
        // $options['controller_welcome_class'] = 'Main';
        // $options['database_list'] = NULL;
        // $options['db_before_get_object_handler'] = NULL;
        // $options['db_before_query_handler'] = array (   0 => 'DuckPhp\\App',   1 => 'OnQuery', );
        // $options['db_close_at_output'] = true;
        // $options['db_close_handler'] = NULL;
        // $options['db_create_handler'] = NULL;
        // $options['db_database_list_from_setting'] = true;
        // $options['db_exception_handler'] = NULL;
        // $options['enable_cache_classes_in_cli'] = false;
        // $options['error_404'] = NULL;
        // $options['error_500'] = NULL;
        // $options['error_debug'] = NULL;
        // $options['ext'] = array (   'DuckPhp\\Ext\\DBManager' => true,   'DuckPhp\\Ext\\RouteHookRouteMap' => true, );
        // $options['handle_all_dev_error'] = true;
        // $options['handle_all_exception'] = true;
        // $options['is_debug'] = false;
        // $options['log_errors'] = true;
        // $options['log_file_template'] = 'log_%Y-%m-%d_%H_%i.log';
        // $options['log_prefix'] = 'DuckPhpLog';
        // $options['log_sql_level'] = 'debug';
        // $options['log_sql_query'] = false;
        // $options['namespace'] = 'MY';
        // $options['namespace_controller'] = 'Controller';
        // $options['override_class'] = 'Base\\App';
        // $options['path'] = '';
        // $options['path_config'] = 'config';
        // $options['path_log'] = 'logs';
        // $options['path_namespace'] = 'app';
        // $options['path_view'] = 'view';
        // $options['path_view_override'] = '';
        // $options['platform'] = '';
        // $options['route_map'] = array ( );
        // $options['route_map_by_config_name'] = '';
        // $options['route_map_important'] = array ( );
        // $options['setting'] = array ( );
        // $options['setting_file'] = 'setting';
        // $options['skip_404_handler'] = false;
        // $options['skip_app_autoload'] = false;
        // $options['skip_env_file'] = true;
        // $options['skip_exception_check'] = false;
        // $options['skip_fix_path_info'] = false;
        // $options['skip_plugin_mode_check'] = false;
        // $options['skip_setting_file'] = false;
        // $options['skip_system_autoload'] = true;
        // $options['skip_view_notice_error'] = true;
        // $options['use_autoloader'] = true;
        // $options['use_flag_by_setting'] = true;
        // $options['use_output_buffer'] = false;
        // $options['use_short_functions'] = true;
        // $options['use_super_global'] = true;
        

        /*
        $options['ext']['DuckPhp\\Ext\\CallableView'] = true;
            $options['callable_view_class']=NULL;
            $options['callable_view_foot']=NULL;
            $options['callable_view_head']=NULL;
            $options['callable_view_prefix']=NULL;
            $options['callable_view_skip_replace']=false;
            $options['path']='';
            $options['path_view']='view';
            $options['path_view_override']='';
            $options['skip_view_notice_error']=true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\DBManager'] = true;
            $options['database_list']=NULL;
            $options['db_before_get_object_handler']=NULL;
            $options['db_close_at_output']=true;
            $options['db_close_handler']=NULL;
            $options['db_create_handler']=NULL;
            $options['db_database_list_from_setting']=true;
            $options['db_exception_handler']=NULL;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\DBReusePoolProxy'] = true;
            $options['database_list']=NULL;
            $options['db_before_get_object_handler']=NULL;
            $options['db_close_at_output']=true;
            $options['db_close_handler']=NULL;
            $options['db_create_handler']=NULL;
            $options['db_database_list_from_setting']=true;
            $options['db_exception_handler']=NULL;
            $options['db_reuse_size']=100;
            $options['db_reuse_timeout']=5;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\FacadesAutoLoader'] = true;
            $options['facades_enable_autoload']=true;
            $options['facades_map']=array ( );
            $options['facades_namespace']='Facades';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\JsonRpcExt'] = true;
            $options['jsonrpc_backend']='https://127.0.0.1';
            $options['jsonrpc_check_token_handler']=NULL;
            $options['jsonrpc_enable_autoload']=true;
            $options['jsonrpc_is_debug']=false;
            $options['jsonrpc_namespace']='JsonRpc';
            $options['jsonrpc_service_interface']='';
            $options['jsonrpc_service_namespace']='';
            $options['jsonrpc_wrap_auto_adjust']=true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\Misc'] = true;
            $options['path']='';
            $options['path_lib']='lib';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\PluginForSwooleHttpd'] = true;
            $options['swoole_ext_class']='SwooleHttpd\\SwooleExt';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisManager'] = true;
            $options['enable_simple_cache']=true;
            $options['redis_list']=NULL;
            $options['simple_cache_prefix']='';
            $options['use_context_redis_setting']=true;
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RedisSimpleCache'] = true;
            $options['redis']=NULL;
            $options['redis_cache_prefix']='';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookDirectoryMode'] = true;
            $options['mode_dir_basepath']='';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode'] = true;
            $options['key_for_action']='_r';
            $options['key_for_module']='';
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookRewrite'] = true;
            $options['rewrite_map']=array ( );
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\RouteHookRouteMap'] = true;
            $options['route_map']=array (
);
            $options['route_map_by_config_name']='';
            $options['route_map_important']=array ( );
        //*/
        /*
        $options['ext']['DuckPhp\\Ext\\StrictCheck'] = true;
            $options['controller_base_class']=NULL;
            $options['is_debug']=false;
            $options['namespace']='MY';
            $options['namespace_controller']='Controller';
            $options['namespace_model']='';
            $options['namespace_service']='';
            $options['strict_check_context_class']=NULL;
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
