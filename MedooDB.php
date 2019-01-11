<?php
// You need import this file manually.
namespace DNMVCS;

class MedooFixed extends \Medoo\Medoo
{
	public function exec($query, $map = [])
	{
		if(isset($map[0])){
			array_unshift($map,null);
			unset($map[0]);
		}
		return parent::exec($query, $map);
	}
}

class MedooDB extends MedooFixed
{
	use DBExt;
	
	public function close()
	{
		$this->pdo=null;
	}
	public function fetchAll($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		return $this->query($sql,$args)->fetchAll();
	}
	public function fetch($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		return $this->query($sql,$args)->fetch();
	}
	public function fetchColumn($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		return $this->query($sql,$args)->fetchColumn();
	}
	public function execQuick($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		$sth = $this->pdo->prepare($sql,...$args);
		$ret=$sth->execute($args);
		
		$this->rowCount=$sth->rowCount();
		return $ret;
	}
	//@impelement
	public static function CreateDBInstance($db_config)
	{
		$dsn=$db_config['dsn'];
		list($driver,$dsn)=explode(':',$dsn);
		$dsn=rtrim($dsn,';');
		$a=explode(';',$dsn);
		$dsn_array['driver']=$driver;
		foreach($a as $v){
			list($key,$value)=explode('=',$v);
			$dsn_array[$key]=$value;
		}
		$db_config['dsn']=$dsn_array;
		$db_config['database_type']='mysql';
		
		return new DNMedoo($db_config);
	}
	public static function CloseDBInstance($db)
	{
		$db->close();
	}

}
