<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
////[[[[
$options =
array(
/*
    'all_config' => [],
    'before_get_db_handler' => null,
    'config_ext_files' => [],
    'controller_base_class' => null,
    'controller_hide_boot_class' => false,
    'controller_methtod_for_miss' => "_missing",
    'controller_postfix' => "",
    'controller_prefix_post' => "do_",
    'controller_welcome_class' => "Main",
    'database_list' => null,
    'db_before_query_handler' => ["DuckPhp\\App","OnQuery"],
    'db_close_at_output' => true,
    'db_close_handler' => null,
    'db_create_handler' => null,
    'db_exception_handler' => null,
    'default_exception_handler' => null,
    'dev_error_handler' => null,
    'enable_cache_classes_in_cli' => false,
    'error_404' => null,
    'error_500' => null,
    'error_debug' => null,
    'ext' => {"DuckPhp\\Ext\\DBManager":true,"DuckPhp\\Ext\\RouteHookRouteMap":true},
    'handle_all_dev_error' => true,
    'handle_all_exception' => true,
    'is_debug' => false,
    'log_errors' => true,
    'log_file' => "",
    'log_prefix' => "DuckPhpLog",
    'log_sql' => false,
    'namespace' => "MY",
    'namespace_controller' => "Controller",
    'override_class' => "Base\\App",
    'path' => "",
    'path_config' => "config",
    'path_namespace' => "app",
    'path_view' => "view",
    'path_view_override' => "",
    'platform' => "",
    'setting' => [],
    'setting_file' => "setting",
    'skip_404_handler' => false,
    'skip_app_autoload' => false,
    'skip_env_file' => true,
    'skip_exception_check' => false,
    'skip_fix_path_info' => false,
    'skip_plugin_mode_check' => false,
    'skip_setting_file' => false,
    'skip_system_autoload' => true,
    'skip_view_notice_error' => true,
    'system_exception_handler' => null,
    'use_context_db_setting' => true,
    'use_flag_by_setting' => true,
    'use_short_functions' => false,
    'use_super_global' => false,
//*/
);
/*
$options['ext']['aa']=true;

*/

////]]]]

$path = realpath(__DIR__.'/..');
$namespace = 'MY';                    // @DUCKPHP_NAMESPACE

$options['path'] = $path;
$options['namespace'] = $namespace;
$options['error_404'] = '_sys/error_404';
$options['error_500'] = '_sys/error_500';
$options['error_debug'] = '_sys/error_debug';

$options['is_debug'] = true;                                            // @DUCKPHP_DELETE
$options['skip_setting_file'] = true;                                   // @DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE

//$options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode']=true;

\DuckPhp\App::RunQuickly($options, function () {
});