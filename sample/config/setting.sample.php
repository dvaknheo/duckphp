<?php
// 你不应该把 setting.php 的敏感信息存在版本管理系统里
// 而是把样例 配置写在这里。
$data=array();
$data['is_dev']=true;
$db=array(
	'host'=>'',
	'port'=>'',
	'dbname'=>'',
	'user'=>'',
	'password'=>'',
);
$data['db']=$db;
return $data;