<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require(__DIR__.'/../../../../../autoload.php');  // @DUCKPHP_HEADFILE

$options = [
    'path' => __DIR__,
    'path_view' => 'app/view',
    'path_config' => 'app/config',
    'path_lib' => 'app/lib',
/*
    'ext'=>[
        'key_for_action'=>null,
        'mode_dir'=>true,
        'mode_dir_basepath'=>__DIR__,
        'mode_dir_use_path_info'=>true,
        'mode_dir_key_for_module'=>'',
        'mode_dir_key_for_action'=>'',
    ],
*/
];
\DuckPhp\App::RunQuickly($options);
