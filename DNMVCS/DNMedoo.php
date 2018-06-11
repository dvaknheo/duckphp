<?php
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

class DNMedoo extends MedooFixed
{
	public function close()
	{
		$this->pdo=null;
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
	public function exec($sql)
	{
		$args=func_get_args();
		array_shift($args);
		
		$sth = $this->pdo->prepare($sql);
		$ret=$sth->execute($args);
		
		$this->rowCount=$sth->rowCount();
		return $ret;
	}

}
