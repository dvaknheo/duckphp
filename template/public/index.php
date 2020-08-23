<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

$namespace = 'MY';                              // @DUCKPHP_NAMESPACE
$path = realpath(__DIR__.'/..');

$options = [
//    'use_autoloader' => true,
//    'skip_plugin_mode_check' => false,
//    'handle_all_dev_error' => true,
//    'handle_all_exception' => true,
//    'override_class' => 'Base\App',
//    'path_namespace'
];
$options['path'] = $path;
$options['namespace'] = $namespace;
// $options['path_namespace'] = 'app';

// $options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode']=true;
$options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode']=true; //@DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
echo "<div>不建议直接运行这文件，建议用安装模式 </div>\n"; //@DUCKPHP_DELETE

\DuckPhp\DuckPhp::RunQuickly($options);
