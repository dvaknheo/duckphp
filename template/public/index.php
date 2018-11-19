<?php
$IN_COMPOSER=false;

$project_root=realpath(__DIR__.'/..');

if($IN_COMPOSER){
	require($project_root.'/vendor/autoload.php');
}else{
	require($project_root.'/../DNMVCS.php');
}


$options=[
	'path'=>$project_root,
];
\DNMVCS\DNMVCS::RunQuickly($options);
//$path=realpath('../');
//\DNMVCS\DNMVCS::G()->init(['path'=>$path])->run();