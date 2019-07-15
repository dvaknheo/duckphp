<?php

require(__DIR__.'/../headfile/headfile.php');

$options=[];
if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
    $options['is_dev']=true;
    $options['skip_setting_file']=true;
    echo "<div>Don't run the template file directly </div>\n";
}

$path=realpath(__DIR__.'/..');
$options['path']=$path;
$options['namespace']='MY';
\DNMVCS\DNMVCS::RunQuickly($options, function () {
});
// \DNMVCS\DNMVCS::G()->init($options)->run();
/*
var_export(\DNMVCS\DNMVCS::G()->options);
array (
  'path' => '/mnt/d/MyWork/sites/DNMVCS-FullTest/',
  'namespace' => 'MY',
  'path_namespace' => 'app',
  'skip_app_autoload' => false,
  'is_debug' => false,
  'platform' => '',
  'override_class' => 'Base\\App',
  'path_view' => 'view',
  'skip_view_notice_error' => true,
  'path_config' => 'config',
  'all_config' =>
  array (
  ),
  'setting' =>
  array (
  ),
  'setting_file' => 'setting',
  'skip_setting_file' => false,
  'reload_for_flags' => true,
  'use_inner_error_view' => false,
  'use_404_to_other_framework' => false,
  'error_404' => '_sys/error-404',
  'error_500' => '_sys/error-500',
  'error_exception' => '_sys/error-exception',
  'error_debug' => '_sys/error-debug',
  'namespace_controller' => 'Controller',
  'controller_base_class' => NULL,
  'controller_prefix_post' => 'do_',
  'controller_enable_paramters' => false,
  'controller_methtod_for_miss' => NULL,
  'controller_hide_boot_class' => false,
  'controller_welcome_class' => 'Main',
  'ext' =>
  array (
    'InnerExt\\SwooleExt' => true,
    'InnerExt\\DBManager' =>
    array (
      'use_db' => true,
      'use_strict_db' => true,
      'db_create_handler' => NULL,
      'db_close_handler' => NULL,
      'database_list' =>
      array (
      ),
    ),
    'InnerExt\\StrictCheck' => true,
    'InnerExt\\SystemWrapperExt' => true,
    'InnerExt\\RouteHookRewrite' => true,
    'InnerExt\\RouteHookRouteMap' => true,
    'InnerExt\\DIExt' => true,
    'Ext\\Lazybones' => false,
    'Ext\\DBReusePoolProxy' => false,
    'Ext\\FacadesAutoLoader' => false,
    'Ext\\FunctionView' => false,
    'Ext\\ProjectCommonAutoloader' => false,
    'Ext\\ProjectCommonConfiger' => false,
    'Ext\\RouteHookDirectoryMode' => false,
    'Ext\\RouteHookOneFileMode' => false,
  ),
  'enable_cache_classes_in_cli' => true,
  'path_lib' => 'lib',
  'db_setting_key' => 'database_list',
  'database_list' =>
  array (
  ),
  'rewrite_map' =>
  array (
  ),
  'route_map' =>
  array (
  ),
  'swoole' =>
  array (
  ),
  'on_404_handler' =>
  array (
    0 => 'MY\\Base\\App',
    1 => 'On404',
  ),
  'exception_handler' =>
  array (
    0 => 'MY\\Base\\App',
    1 => 'OnException',
  ),
  'dev_error_handler' =>
  array (
    0 => 'MY\\Base\\App',
    1 => 'OnDevErrorHandler',
  ),
  'system_exception_handler' =>
  array (
    0 => 'MY\\Base\\App',
    1 => 'set_exception_handler',
  ),
);
*/
