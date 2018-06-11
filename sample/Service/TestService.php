<?php
class TestService extends DNService
{
	public function foo()
	{
		return TestModel::G()->foo();
	}
	public function insert()
	{
		$date=DATE('Y-m-d H:i:s');
		$name='a1';
		$password='123456';
		$password=password_hash($password);
		$data=array(
			'name'=>$name,
			'password'=>$password,
			'ctime'=>$date,
		);
		$ret=DNDB::G()->insert('users',$data);
		return $ret;
	}
}