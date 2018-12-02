<?php
// copy me to "setting.php"
return [
	'is_dev'=>true,
	'database_list'=>[
		[
		'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
		'username'=>'???',
		'password'=>'???',
		],
	],
	'server_options'=>[
		'port'=>9528,
		//'http_handler_root'=>$path.'www/',
		//'http_handler_file'=>$path.'www/index.php',
		'swoole_options'=>[
		//'document_root'=>'static',
		//'enable_static_handler' => true,
		//'worker_num'=>1,
		],
	],
];