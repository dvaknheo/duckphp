<?php
require(__DIR__.'/../../autoload.php');
use DNMVCS\SwooleHttpd\SwooleHttpd;

function hello()
{
    echo "<h1> hello ,have a good start.</h1><pre>\n";
    var_export(SwooleHttpd::SG());
    echo "</pre>";
    return true;
}

$options=[
    'port'=>9528,
    'http_handler'=>'hello',
];
SwooleHttpd::RunQuickly($options);
