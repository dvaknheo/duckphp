<?php
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
$path=realpath(__DIR__.'/..');
$namespace= rtrim('MY\\', '\\');                    // @DUCKPHP_NAMESPACE
$options=[
    'path' => $path,
    'namespace' => $namespace,
    'error_404' => '_sys/error-404',
    'error_500' => '_sys/error-500',
    'error_exception' => '_sys/error-exception',
    'error_debug' => '_sys/error-debug',
];
if (defined('DuckPhp_WARNING_IN_TEMPLATE')) {       // @DUCKPHP_DELETE
    $options['is_debug']=true;                      // @DUCKPHP_DELETE
    $options['skip_setting_file']=true;             // @DUCKPHP_DELETE
    echo "<div>Don't run the template file directly </div>\n"; //@DUCKPHP_DELETE
}                                                   // @DUCKPHP_DELETE
\DuckPhp\App::RunQuickly($options, function () {
});
