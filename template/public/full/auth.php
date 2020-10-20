<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once __DIR__.'/../../../autoload.php';  // @DUCKPHP_HEADFILE
$project_root = realpath(__DIR__).'/SimpleAuth/';
require_once $project_root . 'Base/App.php';

$options = [
    'path' => $project_root,
    'path_namespace' => $project_root,
];

\SimpleAuth\Base\App::RunQuickly($options);
