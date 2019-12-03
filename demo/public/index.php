<?php
require(__DIR__.'/../headfile/headfile.php');  //headfile
$project_root=realpath(__DIR__.'/..');
$options=[
	'path'=>$project_root,
];
\DuckPhp\App::RunQuickly($options);