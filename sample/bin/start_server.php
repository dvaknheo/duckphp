<?php
require_once(__DIR__.'/../../DNMVCS/DNMVCS.php');
$path=realpath(__DIR__.'/../').'/';
$swoole_options=[
	'document_root'=>$path.'static',
    'enable_static_handler' => true,
	//'worker_num'=>1,
];
//$server=new swoole_http_server('0.0.0.0', 9528);
//$server->set($swoole_options);

$server_options=[
	//'host'=>'127.0.0.1',
	'port'=>9528,
	'swoole_server'=>null,
	'swoole_server_options'=>$swoole_options,
	
	'static_root'=>null,
	'php_root'=>null,
	'http_handler_file'=>null,
	'http_handler'=>null,
	'http_exception_handler'=>null,
	
	'websocket_handler'=>null,
	'websocket_exception_handler'=>null,

];
$dn_options=[
	'path'=>$path,
];
\DNMVCS\DNMVCS::RunAsServer($server_options,$dn_options);

return;
