<?php
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');
DNMVCS::G()->init($path);
$routes=array(
//	'test'=>'XX\C$test',
);
DNRoute::G()->mapRoutes($routes);
DNMVCS::G()->run();
