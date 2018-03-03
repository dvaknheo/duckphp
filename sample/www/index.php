<?php
require('../../DNMVCS/DNMVCS.php');
$path=realpath('../');
DNMVCS::G()->init($path);


$key='GET ~aa/([0-9]+)b/?';
//$key='GET aa/bb';
var_dump($key);
DNRoute::G()->addDispathRoute($key,function($t=''){
	var_dump($t);
	var_dump(DATE(DATE_ATOM));
});
//*/
DNMVCS::G()->run();
