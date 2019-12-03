<?php
namespace UUU\Model;
use UUU\Base\BaseModel as Model;
use UUU\Base\ModelHelper as M;

class BaseModel extends Model
{
	public $table_name=null;
	public function getList(int $page=1,int $page_size=10){
		$start=$page-1;
		$sql="SELECT SQL_CALC_FOUND_ROWS  * from {$this->table_name} where deleted_at is null order by id desc limit $start,$page_size";
		$data=M::DB()->fetchAll($sql);
		$sql="SELECT FOUND_ROWS()";
		$total=M::DB()->fetchColumn($sql);
		return array($data,$total);
	}
	public function get($id)
	{
		$sql="select * from {$this->table_name} where id =? and deleted_at is null";
		$ret=M::DB()->fetch($sql,$id);
		return $ret;
	}
	public function add($data)
	{
		$date=date('Y-m-d H:i:s');
		$data['created_at']=$date;
		$data['updated_at']=$date;
		$ret=M::DB()->insert($this->table_name,$data);
		
		return $ret;
	}
	public function update($id,$data)
	{
		$date=date('Y-m-d H:i:s');
		$data['updated_at']=$date;
		$ret=M::DB()->update($this->table_name,$id,$data);
		
		return $ret;
	}
	public function delete($id)
	{
		$date=date('Y-m-d H:i:s');
		$sql="update $this->table_name set deleted_at=? where id=? ";
		$ret=M::DB()->execQuick($sql,$date,$id);
		return $ret;
	}
}