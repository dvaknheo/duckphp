<?php
$_SERVER['PATH_INFO']=$_SERVER['REQUEST_URI'];
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');
DNMVCS::G()->init($path);

DNMVCS::G()->run();
var_dump(DATE(DATE_ATOM));