<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

$namespace = 'LazyToChange';                              // @DUCKPHP_NAMESPACE
$path = realpath(__DIR__.'/..');

$options = [
//    'use_autoloader' => true,
//    'skip_plugin_mode_check' => false,
//    'handle_all_dev_error' => true,
//    'handle_all_exception' => true,
//    'override_class' => 'System\App',
//    'path_namespace' => 'app',
];
$options['path'] = $path;
$options['namespace'] = $namespace;
$options['is_debug'] = true;
//$options['skip_setting_file'] = true;

// $options['use_path_info_by_get']=false;
$options['use_path_info_by_get']=true; //@DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\DuckPhp\DuckPhp::RunQuickly($options);
