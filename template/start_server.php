#!/usr/bin/env php
<?php
require_once(__DIR__.'/../autoload.php'); // @DUCKPHP_HEADFILE
// delete this file if you don't need

$path = realpath(__DIR__.'/');

$options = [
    'path' => $path,
    
    //'host'=>'127.0.0.1',    // default is 127.0.0.1 uncomment or --host to override
    //'port'=>'8080',         // default is 8080 uncomment or  --port to override
];

DuckPhp\HttpServer\HttpServer::RunQuickly($options);
