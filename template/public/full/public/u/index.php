<?php
use DuckPhp\App;

require(__DIR__.'/../../../../../autoload.php');  // @DUCKPHP_HEADFILE

$options=[
	'path'=>__DIR__,
	'namespace'=>'UUU',
	'path_view'=>'app/view',
	'path_config'=>'app/config',
];
try{
    App::G()->init($options);
    App::G()->run();
}catch(\Throwable $ex){
    echo $ex->getMessage();
    var_dump($ex->getTraceAsString());
}
return;