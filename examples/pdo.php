<?php
use DNMVCS\SwooleHttpd;
require(__DIR__.'/../autoload.php');
function hello()
{
$db=new PDO("mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",'root','123456',
[
    PDO::ATTR_PERSISTENT=>true,
    PDO::ATTR_TIMEOUT=>120,
    PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8'
    ]);
    $sql=$sql="select id from Users limit 0,1";
    $stm=$db->query($sql);
    echo $stm->fetchColumn();
    //var_dump(DATE(DATE_ATOM));
    return true;
}

$options=[
    'port'=>9528,
    'http_handler'=>'hello',
];
SwooleHttpd::RunQuickly($options);
