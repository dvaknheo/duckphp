#!/usr/bin/env php
<?php
require(__DIR__.'/../headfile/headfile.php');

$path=realpath(__DIR__.'/../').'';
$options=[
    'host'=>'127.0.0.1',
    'port'=>'8080',
    'path'=>$path,
    // 'path_document'=>'public',
    'dn'=>['is_debug'=>true,]
];

DNMVCS\HttpServer::RunQuickly($options);
