<?php

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
class DNMedoo extends MedooFixed
{
	protected $medoo;
	public static function ORM()
	{
		if(!$this){
			$config=DNConfig::Setting('db');
			$db = new MedooFixed();
			$db->pdo= new PDO($config['dsn'], $config['user'], $config['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
			$this=$db;
		}
		return $this;
	}
	protected function check_connect()
	{
		if($this->pdo){return;}
		if(empty($this->config)){
			throw new Exception('DNMVCS Notice: database not setting!');
		}
		$config=$this->config;
		$this->pdo= new PDO($config['dsn'], $config['user'], $config['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
	}
	public function init()
	{
	}
	public function getPDO()
	{
		return $this->pdo;
	}
	public function setPDO($pdo)
	{
		$this->pdo=$pdo;
	}
	public function close()
	{
	}
	//Warnning, escape the key by yourself
	public function quote_array($array)
	{
		$this->check_connect();
		$a=array();
		foreach($array as $k =>$v){
			$a[]=$k.'='.$this->pdo->quote($v);
		}
		return implode(',',$a);
	}
	public function fetchAll($sql)
	{
		$args=func_get_args();
		array_shift($args);
		unset($args[0]);
		return $this->query($sql,args)->fetchAll();
	}
	public function fetch($sql)
	{
		$args=func_get_args();
		array_shift($args);
		unset($args[0]);
		return $this->query($sql,args)->fetch();
	}
	public function fetchColumn($sql)
	{
		$args=func_get_args();
		array_shift($args);
		unset($args[0]);
		return $this->query($sql,args)->fetchColumn();
	}
	
	//!
	public function exec($sql)
	{
		$args=func_get_args();
		array_shift($args);
		
		$sth = $this->pdo->prepare($sql);
		$ret=$sth->execute($args);
		
		$this->rowCount=$sth->rowCount();
		return $ret;
	}
	public function rowCount()
	{
		//return $this->rowCount;
	}
	
	public function get($table_name,$id,$key='id')
	{
		$sql="select {$table_name} from terms where {$key}=? limit 1";
		return $this->fetch($sql,$id);
	}
	
	public function insert($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".DNDB::G()->quote_array($data);
		$ret=$this->exec($sql);
		if(!$return_last_id){return $ret;}
		$ret=DNDB::G()->lastInsertId();
		return $ret;
	}
	public function delete($table,$id,$key='id')
	{
		throw new Exception("DNMVCS Notice : override me to delete");
		$sql="delete from {$table_name} where {$key}=? limit 1";
		return $this->exec($sql,$id);
	}
	
	public function update($table_name,$id,$data,$key='id')
	{
		if(isset($data[$key])){unset($data[$key]);}
		$frag=DNDB::G()->quote_array($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=DNDB::G()->exec($sql,$id);
		return $ret;
	}

}
