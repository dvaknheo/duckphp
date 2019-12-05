<?php
// 而是把样例 配置写在这里。
$data=array();
$data['is_dev']=true;
$data['db']=array(
	'dsn'=>'mysql:host=???;port=???;dbname=???;charset=utf8;',
	'user'=>'???',
	'password'=>'???',
);
$data['medoo']=array(
	'database_type'=>'mysql',
	'dsn'=>"???",
	'username'=>'???',	
	'password'=>'???'
);
return $data;