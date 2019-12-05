<?php
require(__DIR__.'/../headfile/headfile.php');  //headfile
$project_root=realpath(__DIR__.'/..');
$options=[
	'path'=>$project_root,
    'ext'=>[
        'DuckPhp\Ext\RouteHookOneFileMode'=>[
            'key_for_action'=>'_r',
            'key_for_module'=>'',
        ],
    ],
];
\DuckPhp\App::RunQuickly($options);