#!/usr/bin/env php
<?php
require_once(__DIR__.'/../headfile/headfile.php');

/*

$ret=[
	'swoole'=>[
		'port'=>9528,
		'with_http_handler_root'=>true,
		'http_handler_basepath'=>realpath(dirname(__DIR__)).'/',
		'http_handler_root'=>'public',
	],
];
$dn_options=$ret;
$dn_options=$dn_options??[];
$dn_options
$path=realpath(__DIR__.'/../').'/';
$dn_options['path']=$path;
$dn_options['is_debug']=true;

//*/
$dn_options=[];

$path=realpath(__DIR__.'/../');
$options=[
    'host'=>'127.0.0.1',
    'port'=>'8080',
    'path'=>$path,
    'dnmvcs'=>$dn_options,
];

DNMVCS\HttpServer::RunQuickly($options);
