<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../../autoload.php');  // @DUCKPHP_HEADFILE

$project_root = realpath(__DIR__.'/..');
$options = [
    'path' => $project_root,
    'path_namespace' => 'auth',
    'namespace' => 'UserSystemDemo',

    'is_debug' => true,
];
\DuckPhp\App::RunQuickly($options);
