<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
$path = realpath(__DIR__.'/..');
$namespace = rtrim('MY\\', '\\');                    // @DUCKPHP_NAMESPACE
////[[[[
$options =
array(
  // 'all_config' =>
  // array (
  // ),
  
  // 'before_get_db_handler' => NULL,
  
  // 'controller_base_class' => NULL,
  // 'controller_hide_boot_class' => false,
  // 'controller_methtod_for_miss' => '_missing',
  // 'controller_postfix' => '',
  // 'controller_prefix_post' => 'do_',
  // 'controller_welcome_class' => 'Main',
  // 'database_list' => NULL,
  // 'db_before_query_handler' =>
  // array (
  //   0 => 'MY\\Base\\App',
  //   1 => 'OnQuery',
  // ),
  // 'db_close_at_output' => true,
  // 'db_close_handler' => NULL,
  // 'db_create_handler' => NULL,
  // 'db_exception_handler' => NULL,
  // 'default_exception_handler' =>
  // array (
  //   0 => 'DuckPhp\\App',
  //   1 => 'OnDefaultException',
  // ),
  // 'dev_error_handler' =>
  // array (
  //   0 => 'DuckPhp\\App',
  //   1 => 'OnDevErrorHandler',
  // ),
  // 'enable_cache_classes_in_cli' => false,
  // 'error_404' => '_sys/error_404',
  // 'error_500' => '_sys/error_500',
  // 'error_debug' => '_sys/error_debug',
  // 'ext' =>
  // array (
  //   'DuckPhp\\Ext\\Misc' => true,
  //   'DuckPhp\\Ext\\SimpleLogger' => true,
  //   'DuckPhp\\Ext\\DBManager' => true,
  //   'DuckPhp\\Ext\\RouteHookRewrite' => true,
  //   'DuckPhp\\Ext\\RouteHookRouteMap' => true,
  //   'DuckPhp\\Ext\\StrictCheck' => false,
  //   'DuckPhp\\Ext\\RouteHookOneFileMode' => false,
  //   'DuckPhp\\Ext\\RouteHookDirectoryMode' => false,
  //   'DuckPhp\\Ext\\RedisManager' => false,
  //   'DuckPhp\\Ext\\RedisSimpleCache' => false,
  //   'DuckPhp\\Ext\\DBReusePoolProxy' => false,
  //   'DuckPhp\\Ext\\FacadesAutoLoader' => false,
  //   'DuckPhp\\Ext\\Lazybones' => false,
  //   'DuckPhp\\Ext\\Pager' => false,
  // ),
  // 'handle_all_dev_error' => true,
  // 'handle_all_exception' => true,
  // 'is_debug' => true,
  // 'log_file' => '',
  // 'log_prefix' => 'DuckPhpLog',
  // 'log_sql' => false,
  // 'namespace' => 'MY',
  // 'namespace_controller' => 'Controller',
  // 'override_class' => 'Base\\App',
  // 'path' => '/mnt/d/MyWork/sites/DNMVCS/template/',
  // 'path_config' => 'config',
  // 'path_lib' => 'lib',
  // 'path_namespace' => 'app',
  // 'path_view' => 'view',
  // 'path_view_override' => '',
  // 'platform' => '',
  // 'rewrite_map' =>
  // array (
  // ),
  // 'route_map' =>
  // array (
  // ),
  // 'route_map_important' =>
  // array (
  // ),
  // 'setting' =>
  // array (
  // ),
  // 'setting_file' => 'setting',
  // 'skip_404_handler' => false,
  // 'skip_app_autoload' => false,
  // 'skip_env_file' => true,
  // 'skip_exception_check' => false,
  // 'skip_fix_path_info' => false,
  // 'skip_plugin_mode_check' => false,
  // 'skip_setting_file' => true,
  // 'skip_system_autoload' => true,
  // 'skip_view_notice_error' => true,
  // 'system_exception_handler' =>
  // array (
  //   0 => 'DuckPhp\\App',
  //   1 => 'set_exception_handler',
  // ),
  // 'use_context_db_setting' => true,
  // 'use_flag_by_setting' => true,
  // 'use_short_function' => true,
  // 'use_short_functions' => true,
  // 'use_super_global' => false,
);
////]]]]
$options['path'] = $path;
$options['namespace'] = $namespace;
$options['error_404'] = '_sys/error_404';
$options['error_500'] = '_sys/error_500';
$options['error_debug'] = '_sys/error_debug';

$options['is_debug'] = true;                  // @DUCKPHP_DELETE
$options['skip_setting_file'] = true;                 // @DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE


\DuckPhp\App::RunQuickly($options, function () {
});
var_dump(\DuckPhp\App::URL(''));
