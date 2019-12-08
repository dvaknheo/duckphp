<?php
use \DNMVCS\DNMVCS as DN;

require(__DIR__.'/../../headfile/headfile.php');

@session_start();
$options=[
	'path'=>__DIR__,
	'namespace'=>'UUU',
	'path_view'=>'app/view',
	'path_config'=>'app/config',
];
try{
    DN::G()->init($options);
    DN::G()->run();
}catch(\Throwable $ex){
    echo $ex->getMessage();
    var_dump($ex->getTraceAsString());
}
return;