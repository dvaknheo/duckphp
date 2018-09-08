<?php
use \DNMVCS\DNSwooleServer as Server;

$path="/mnt/d/MyWork/sites/";
require_once($path.'DNMVCS/DNMVCS/DNMVCS.php');
require_once($path.'DNMVCS/DNMVCS/SuperGlobal.php');
require_once($path.'DNMVCS/DNMVCS/DNSwooleHttpServer.php');

//$server=new swoole_http_server('0.0.0.0', 9528);
$path=realpath(__DIR__.'/../').'/';
$swoole_options=[
	'document_root'=>$path.'static',
    'enable_static_handler' => true,
	//'worker_num'=>1,
];
//$server->set($swoole_options);

$server_options=[
	'port'=>9528,
	'swoole_server'=>null,
	'swoole_server_options'=>$swoole_options,
	//'php_root'=>$path.'www/',
	//'static_root'=>$path.'staic/',
	//'http_handler_file'=>$path.'www/index.php',

];
$dn_options=[
	'path'=>$path,
];
\DNMVCS\DNSwooleHttpServer::RunWithServer($server_options,$dn_options);

return;
