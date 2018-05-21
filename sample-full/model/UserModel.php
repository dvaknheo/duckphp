<?php
class UserModel extends DNModel
{
	public function getUserByName($username)
	{
		$sql="select * from Users where username=?";
		$ret=DNDB::G()->fetch($sql,$username);
		return $ret;
	}
	public function getUserDirect($id)
	{
		$sql="select * from Users where id=?";
		$ret=DNDB::G()->fetch($sql,$id);
		return $ret;
	}
	
	public function addUser($username,$pass)
	{
		$password=password_hash($pass, PASSWORD_BCRYPT);
		$date=date('Y-m-d H:i:s');
		$data=array('username'=>$username,'password'=>$password,'created_at'=>$date);
		$ret=DNDB::G()->insert('Users',$data);
		return $ret;
	}
	public function changePass($user_id,$password)
	{
		$password=password_hash($password, PASSWORD_BCRYPT);
		$data=array('password'=>$password);
		$ret=DNDB::G()->update('Users',$id,$data,'id');
		return $ret;
	}
	public function checkPass($password,$old)
	{
		return password_verify($password,$old);
	}
	
}