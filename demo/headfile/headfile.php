<?php
$IN_COMPOSER=false;
if(defined('HEAD_FILE_LOADED')){
	return;
}
define('HEAD_FILE_LOADED',true);
if($IN_COMPOSER){
	require( __DIR__ .'/../vendor/autoload.php');
	return;
}
$file=realpath(__DIR__.'/../../DNMVCS.php');
if(is_file($file)){
	define('DNMVCS_WARNING_IN_TEMPLATE',true);
	
	require($file);
	return;
}
$file=realpath(__DIR__.'/../../DNMVCS/autoload.php');
if(!is_file($file)){
	exit("Can't found DNMVCS.php -- By ".__FILE__);
}
require($file);
$file=realpath(__DIR__.'/../../SwooleHttpd/autoload.php');
if(is_file($file)){
	require($file);
}
return;

