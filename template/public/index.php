<?php
////[[[[
$IN_COMPOSER=false;

$project_root=realpath(__DIR__.'/..');

if($IN_COMPOSER){
	require($project_root.'/vendor/autoload.php');
}else{
	require($project_root.'/../DNMVCS.php');
}
////]]]]

$path=realpath(__DIR__.'/..');

$options=[
	'path'=>$path,
];
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init(['path'=>$path])->run();