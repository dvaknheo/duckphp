#!/usr/bin/env php
<?php
require_once(__DIR__.'/../autoload.php'); // @DUCKPHP_HEADFILE
// delete this file if you don't need
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
    'path'=>$path,
    
    //'host'=>'127.0.0.1',    // default is 127.0.0.1 uncomment or --host to override
    //'port'=>'8080',         // default is 8080 uncomment or  --port to override
    // 'dnmvcs'=>$dn_options,    // decraped. for swoole only.
];

DuckPhp\HttpServer::RunQuickly($options);