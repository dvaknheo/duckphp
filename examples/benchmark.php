<?php
use DNMVCS\SwooleHttpd;
require(__DIR__.'/../autoload.php');
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
