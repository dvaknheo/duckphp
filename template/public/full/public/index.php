<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../../autoload.php');  // @DUCKPHP_HEADFILE

$project_root = realpath(__DIR__.'/..');
$options = [
    'path' => $project_root,
];
\DuckPhp\App::RunQuickly($options);
