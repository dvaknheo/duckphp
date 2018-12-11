<?php
require(__DIR__.'/../headfile/headfile.php');

////[[[[
$path=realpath(__DIR__.'/../').'/';
$server_options=[
	//'port'=>???,
	//'http_handler_basepath'=>$path,
];

$dn_options=[
	'path'=>$path,
];

////]]]]

$setting=[];
$setting_file=$path.'config/setting.php';
if(is_file($setting_file)){
	$setting=include($setting_file);
}
$server_options=array_replace_recursive($server_options,$setting['server_options']??[]);

$server=null;
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ echo "<div>Don't run the template file directly </div>\n"; }
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $dn_options['setting_basename']=''; }
\DNMVCS\DNMVCS::RunAsServer($server_options,$dn_options,$server);