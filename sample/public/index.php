<?php
$ROOT_DIR=realpath(__DIR__.'/../..').'/';
if(is_file($ROOT_DIR.'DNMVCS.php')){
	require($ROOT_DIR.'DNMVCS.php');
}else{
	require($ROOT_DIR.'vendor/autoload.php');
}
$project_root=realpath(__DIR__.'/..');

$options=[
	'path'=>$project_root,
];
\DNMVCS\DNMVCS::RunQuickly($options);
//$path=realpath('../');
//\DNMVCS\DNMVCS::G()->init(['path'=>$path])->run();