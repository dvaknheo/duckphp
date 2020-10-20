<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE
$project_root = realpath(__DIR__).'/SimpleBlog/';

//require_once $project_root . '/Base/App.php';

$options = [
    'path' => $project_root,
    'path_namespace' => $project_root,
];
//\SimpleBlog\Base\App::RunQuickly($options);

$options['override_class'] = \SimpleBlog\Base\App::class;
\DuckPhp\DuckPhp::RunQuickly($options);
