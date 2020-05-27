<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
////[[[[
$options =
array(
//    'all_config' => [],
//    'before_get_db_handler' => null,
//    'config_ext_files' => [],
//    'controller_base_class' => null,
//    'controller_hide_boot_class' => false,
//    'controller_methtod_for_miss' => "_missing",
//    'controller_postfix' => "",
//    'controller_prefix_post' => "do_",
//    'controller_welcome_class' => "Main",
//    'database_list' => null,
//    'db_before_query_handler' => ["DuckPhp\\App","OnQuery"],
//    'db_close_at_output' => true,
//    'db_close_handler' => null,
//    'db_create_handler' => null,
//    'db_exception_handler' => null,
//    'default_exception_handler' => null,
//    'dev_error_handler' => null,
//    'enable_cache_classes_in_cli' => false,
//    'error_404' => null,
//    'error_500' => null,
//    'error_debug' => null,
//    'ext' => ["DuckPhp\\Ext\\DBManager"=>true,"DuckPhp\\Ext\\RouteHookRouteMap"=>true],
//    'handle_all_dev_error' => true,
//    'handle_all_exception' => true,
//    'is_debug' => false,
//    'log_errors' => true,
//    'log_file' => "",
//    'log_prefix' => "DuckPhpLog",
//    'log_sql_query' => false,
//    'log_sql_level' => 'debug',
//    'namespace' => "MY",
//    'namespace_controller' => "Controller",
//    'override_class' => "Base\\App",
//    'path' => "",
//    'path_config' => "config",
//    'path_namespace' => "app",
//    'path_view' => "view",
//    'path_view_override' => "",
//    'platform' => "",
//    'setting' => [],
//    'setting_file' => "setting",
//    'skip_404_handler' => false,
//    'skip_app_autoload' => false,
//    'skip_env_file' => true,
//    'skip_exception_check' => false,
//    'skip_fix_path_info' => false,
//    'skip_plugin_mode_check' => false,
//    'skip_setting_file' => false,
//    'skip_system_autoload' => true,
//    'skip_view_notice_error' => true,
//    'system_exception_handler' => null,
//    'use_context_db_setting' => true,
//    'use_flag_by_setting' => true,
//    'use_short_functions' => false,
//    'use_super_global' => false,
);
/*
$options['ext']['DuckPhp\\Ext\\CallableView'] = true;
    $options['callable_view_class']=NULL;
    $options['callable_view_foot']=NULL;
    $options['callable_view_head']=NULL;
    $options['callable_view_prefix']=NULL;
    $options['callable_view_skip_replace']=false;
//*/
/*
$options['ext']['DuckPhp\\Ext\\DBReusePoolProxy'] = true;
    $options['before_get_db_handler']=NULL;
    $options['database_list']=NULL;
    $options['db_close_at_output']=true;
    $options['db_close_handler']=NULL;
    $options['db_create_handler']=NULL;
    $options['db_exception_handler']=NULL;
    $options['db_reuse_size']=100;
    $options['db_reuse_timeout']=5;
    $options['use_context_db_setting']=true;
//*/
/*
$options['ext']['DuckPhp\\Ext\\FacadesAutoLoader'] = true;
    $options['facades_enable_autoload']=true;
    $options['facades_map']=array (
);
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
    $options['rewrite_map']=array (
);
//*/
/*
$options['ext']['DuckPhp\\Ext\\StrictCheck'] = true;
    $options['namespace_model']='';
    $options['namespace_service']='';
//*/

////]]]]

$path = realpath(__DIR__.'/..');
$namespace = 'MY';                              // @DUCKPHP_NAMESPACE

$options['path'] = $path;
$options['namespace'] = $namespace;

$options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode']=true; //@DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE

\DuckPhp\App::RunQuickly($options);
