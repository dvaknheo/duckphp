<?php
////[[[[
$IN_COMPOSER=false;

$project_root=realpath(__DIR__.'/..');

if($IN_COMPOSER){
	require($project_root.'/vendor/autoload.php');
}else{
	require($project_root.'/../DNMVCS.php');
}
////]]]]

$server=null;
//$server=new swoole_http_server('0.0.0.0', 9528);
$path=realpath(__DIR__.'/../').'/';
$setting_file=$path.'config/setting.php';
$setting=[];
if(is_file($setting_file)){
	$setting=include($setting_file);
}

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
	//'http_handler_file'=>$path.'www/index.php',

];

$dn_options=[
	'path'=>$path,
];
$server_options=array_merge_recursive($server_options,$setting['server_options']??[]);

\DNMVCS\DNMVCS::RunAsServer($server_options,$dn_options,$server);