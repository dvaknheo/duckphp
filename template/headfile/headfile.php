<?php
$IN_COMPOSER=false;

if(!$IN_COMPOSER){
	$file=realpath(__DIR__.'/../../DNMVCS.php');
	if(is_file($file)){
		define('DNMVCS_WARNING_IN_TEMPLATE',true);
		
		require($file);
		return;
	}
	$file=realpath(__DIR__.'/../../DNMVCS/DNMVCS.php');
	if(is_file($file)){
		require($file);
		return;
	}else{
		exit("Can't found DNMVCS.php -- By ".__FILE__);
	}
	return;
}
require( __DIR__ .'/../vendor/autoload.php');
