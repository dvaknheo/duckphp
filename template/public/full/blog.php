<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE
$project_root = realpath(__DIR__).'/SimpleBlog/';

$options = [
    'path' => $project_root,
    'path_namespace' => $project_root,
    'override_class' => \SimpleBlog\System\App::class,
];
\DuckPhp\DuckPhp::RunQuickly($options);