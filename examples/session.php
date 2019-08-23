<?php
use DNMVCS\SwooleHttpd;

require(__DIR__.'/../autoload.php');

$options=[
    'port'=>9528,
    'http_handler'=>'SessionTest',
];
SwooleHttpd::RunQuickly($options);

function SessionTest()
{
    SwooleHttpd::session_start();
    echo "<h1> hello Session</h1><pre>\n";
    echo "Current Session\n";
    var_dump(SG()->_SESSION);
    
    if (count(SG()->_SESSION)>=20) {
        echo "Destroy Session!\n";
        SwooleHttpd::session_destroy();
    } else {
        echo "Add a Session\n";
        SG()->_SESSION[DATE(DATE_ATOM)]=" ";//DATE(DATE_ATOM);
    }
    echo "</pre>";
    return true;
}
function SG()
{
    return SwooleHttpd::SG();
}
