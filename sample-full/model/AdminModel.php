<?php
class x extends DnModel
{
	public function reset()
	{
		$sql="insert into Admins set password=?";
		DNDB::G()->exec($sql,$x);
	}
}