<?php
require(__DIR__.'/../../headfile/headfile.php');

$options=[
	'path'=>__DIR__,
	'path_controller'=>'app/Controller',
	'path_view'=>'app/view',
	'path_config'=>'app/config',
	'path_lib'=>'app/lib',
	'ext'=>[
		'key_for_action'=>null,
		'mode_dir'=>true,
		'mode_dir_basepath'=>__DIR__,
		'mode_dir_use_path_info'=>true,
		'mode_dir_key_for_module'=>'',
		'mode_dir_key_for_action'=>'',
	],
];
\DNMVCS\DNMVCS::RunQuickly($options);

