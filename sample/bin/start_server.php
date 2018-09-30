<?php
require(__DIR__.'/../DNMVCS/DNMVCS/DNMVCS.php');

$server=null;
//$server=new swoole_http_server('0.0.0.0', 9528);
$path=realpath(__DIR__.'/../').'/';
$swoole_options=[
	//'document_root'=>$path.'static',
    //'enable_static_handler' => true,
	//'worker_num'=>1,
];
//$server->set($swoole_options);
$server_options=[
	'port'=>9528,
	'swoole_server'=>null,
	'swoole_server_options'=>$swoole_options,
	//'http_handler_root'=>$path.'www/',
	'http_handler_file'=>$path.'www/index.php',

];
$dn_options=[
	'path'=>$path,
];
\DNMVCS\DNMVCS::RunAsServer($server_options,$dn_options,$server);

return;
