<?php
namespace DNMVCS;

interface DNDBBasicInterface
{
	public function close();
	public function quote($string);
	public function fetchAll($sql,...$args);
	public function fetch($sql,...$args);
	public function fetchColumn($sql,...$args);
	public function execQuick($sql,...$args);
}
class DB implements DNDBBasicInterface
{
	use DNDB_Ext;
	
	public $pdo;
	public $config;
	protected $rowCount;
	
	public function init($config)
	{
		$this->config=$config;
		$this->check_connect();
	}
	public static function CreateDBInstance($db_config)
	{
		$class=static::class;
		$db=new $class();
		$db->init($db_config);
		return $db;
	}
	public static function CloseDBInstance($db)
	{
		$db->close();
	}
	protected function check_connect()
	{
		if($this->pdo){return;}
		$config=$this->config;
		$this->pdo=new \PDO($config['dsn'], $config['username'], $config['password']
			,[
				\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC
			]
		);
	}
	public function close()
	{
		$this->rowCount=0;
		$this->pdo=null;
	}
	public function quote($string)
	{
		if(is_array($string)){
			array_walk($string,function(&$v,$k){
				$v=is_string($v)?$this->quote($v):(string)$v;
			});
		}
		if(!is_string($string)){return $string;}
		$this->check_connect();
		return $this->pdo->quote($string);
	}
	public function quoteIn($array)
	{
		$this->check_connect();
		if(empty($array)){return 'NULL';}
		array_walk($array,function(&$v,$k){
			$v=is_string($v)?$this->quote($v):(string)$v;
		});
		return implode(',',$array);
	}
	public function quoteSetArray($array)
	{
		$a=array();
		foreach($array as $k =>$v){
			$a[]=$k.'='.$this->pdo->quote($v);
		}
		return implode(',',$a);
	}
	public function qouteInsertArray($array)
	{
		// TODO
	}

	public function fetchAll($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		
		$ret=$sth->fetchAll();
		return $ret;
	}
	public function fetch($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		$ret=$sth->fetch();
		return $ret;
	}
	public function fetchColumn($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		$ret=$sth->fetchColumn();
		return $ret;
	}
	public function execQuick($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		
		$sth = $this->pdo->prepare($sql);
		$ret=$sth->execute($args);
		
		$this->rowCount=$sth->rowCount();
		return $ret;
	}
	public function rowCount()
	{
		return $this->rowCount;
	}
	
	
	
}
trait DNDB_Ext
{
	public function findData($table_name,$id,$key='id')
	{
		$sql="select {$table_name} from terms where {$key}=? limit 1";
		return $this->fetch($sql,$id);
	}
	
	public function insertData($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".$this->quoteSetArray($data);
		$ret=$this->execQuick($sql);
		if(!$return_last_id){return $ret;}
		$ret=$this->pdo->lastInsertId();
		return $ret;
	}
	public function deleteData($table,$id,$key='id',$key_delete='is_deleted')
	{
		if($key_delete){
			$sql="update {$table_name} set {$key_delete}=1 where {$key}=? limit 1";
			return $this->execQuick($sql,$id);
		}else{
			$sql="delete from {$table_name} where {$key}=? limit 1";
			return $this->execQuick($sql,$id);
		}
	}
	
	public function updateData($table_name,$id,$data,$key='id')
	{
		if($data[$key]){unset($data[$key]);}
		$frag=$this->quoteSetArray($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=$this->execQuick($sql,$id);
		return $ret;
	}
}