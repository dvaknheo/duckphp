<?php 
$data=array();
$data['is_debug']=true;
$data['platform']='unkown';
$data['database_list']=[[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
]];

return $data;