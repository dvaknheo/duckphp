<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
var_dump($_SERVER);

require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE
$path = realpath(__DIR__.'/..');
$namespace = rtrim('MY\\', '\\');                    // @DUCKPHP_NAMESPACE
$options = [
    'path' => $path,
    'namespace' => $namespace,
    'error_404' => '_sys/error_404',
    'error_500' => '_sys/error_500',
    'error_exception' => '_sys/error_exception',
    'error_debug' => '_sys/error_debug',
];
$options['is_debug'] = true;                  // @DUCKPHP_DELETE
$options['skip_setting_file'] = true;                 // @DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE

\DuckPhp\App::RunQuickly($options, function () {
});
