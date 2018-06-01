<?php
class MedooFixed extends \Medoo\Medoo
{
	public function exec($query, $map = [])
	{
		//unset($map[0]);
		if(isset($map[0])){
			array_unshift($map,null);
			unset($map[0]);
		}
		return parent::exec($query, $map);
	}
}
//准备用这个方法来替换默认的 DNDB
class DNMedoo extends DNSingleton
{
	protected static $medoo;
	public static function ORM()
	{
		if(!self::$medoo){
			$config=DNConfig::Setting('db');
			$db = new MedooFixed();
			$db->pdo= new PDO($config['dsn'], $config['user'], $config['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
			self::$medoo=$db;
		}
		return self::$medoo;
	}
	public function fetchAll($sql)
	{
		$args=func_get_args();
		array_shift($args);
		unset($args[0]);
		return self::$medoo->query ($sql,args)->fetchAll();
	}
	public function fetch($sql)
	{
		$args=func_get_args();
		array_shift($args);
		unset($args[0]);
		return self::$medoo->query ($sql,args)->fetch();
	}
	public function fetchColumn($sql)
	{
		$args=func_get_args();
		array_shift($args);
		unset($args[0]);
		return self::$medoo->query ($sql,args)->fetchColumn();
	}

}
if(!function_exists('ORM')){
function ORM()
{
	return DNMedoo::ORM();
}
}
if(!function_exists('DB')){
function ORM()
{
	return DNMedoo::G();
}
}