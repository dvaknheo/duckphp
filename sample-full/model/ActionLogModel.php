<?php
class ActionLogModel extends DNModel
{
	public function log($action,$type='')
	{
		DNDB::G()->add('ActionLogs',['actions'=>$action,'created_at'=>date('Y-m-d H:i:s')]);
	}
	public function get($id)
	{
		
	}
	public function getList($page=1,$page_size=10){
		$sql="SELECT SQL_CALC_FOUND_ROWS  * from ActionLogs where true limit $start,$page_size";
		$data=DNDB::G()->fetchAll($sql);
		$sql="SELECT FOUND_ROWS()";
		$total=DNDB::G()->fetchColumn($sql);
		return array($data,$total);
	}
}