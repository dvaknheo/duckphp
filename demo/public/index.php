<?php
require(__DIR__.'/../headfile/headfile.php');  // @HEADFILE , will change in install;

$project_root=realpath(__DIR__.'/..');
$options=[
	'path'=>$project_root,
    'error_404'=>'_sys/error_404',
    'error_500'=>'_sys/error_500',
    'error_exception'=>'_sys/error_exception',
];
\DuckPhp\App::RunQuickly($options);