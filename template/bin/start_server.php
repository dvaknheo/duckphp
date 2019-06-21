#!/usr/bin/env php
<?php
require(__DIR__.'/../headfile/headfile.php');

$host='0.0.0.0';
$port='8080';
$path=realpath(__DIR__.'/../').'/';

DNMVCS\InnerHttpServer::RunQuickly($host,$port,$path);
