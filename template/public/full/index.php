<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE

$project_root = realpath(__DIR__).'/FullProject/';
$options = [
    'path' => $project_root,
];
\DuckPhp\DuckPhp::RunQuickly($options);
