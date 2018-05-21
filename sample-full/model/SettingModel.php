<?php
class SettingModel extends DnModel
{
	public function get($key)
	{
		$sql="select v from Settings where k=?";
		$ret=DNDB::G()->fetchColumn($sql,$key);
		return $ret;
	}
	public function set($key,$value)
	{
		$sql="update Settings set v=? where k=?";
		$ret=DNDB::G()->exec($sql,$key,$value);
		if($ret)return $ret;
		$sql="insert into Settings (k,v) values(?,?)";
		$ret=DNDB::G()->exec($sql,$key,$value);
	}
}