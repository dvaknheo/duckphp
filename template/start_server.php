#!/usr/bin/env php
<?php
require_once(__DIR__.'/../autoload.php'); // @DUCKPHP_HEADFILE
/*
$dn_option=[
	'swoole'=>[
		'port'=>9528,
		'with_http_handler_root'=>true,
		'http_handler_basepath'=>realpath(dirname(__DIR__)).'/',
		'http_handler_root'=>'public',
	],
];
//*/

$path=realpath(__DIR__.'/');

$options=[
    'host'=>'127.0.0.1',    //  --host to override
    'port'=>'8080',         // --port to override
    'path'=>$path,
    // 'dnmvcs'=>$dn_options,    // decraped. for swoole only.
];

DuckPhp\HttpServer::RunQuickly($options);