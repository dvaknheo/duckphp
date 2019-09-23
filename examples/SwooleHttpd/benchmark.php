<?php
require(__DIR__.'/../../autoload.php');
use DNMVCS\SwooleHttpd\SwooleHttpd;

function hello()
{
    echo DATE(DATE_ATOM);
    return true;
}

$options=[
    'port'=>9528,
    'http_handler'=>'hello',
];
SwooleHttpd::RunQuickly($options);
