<?php
class UserService extends DNService
{
	public function reg($username,$password)
	{		
		$user=UserModel::G()->getUserByName($username);
		UserException::ThrowOn($user,"用户已经存在");
		$id=UserModel::G()->addUser($username,$password);
		UserException::ThrowOn(!$id,"注册失败");
		
		ActionLogModel::G()->log("$username 注册");
		
		return UserModel::G()->getUserDirect($id);
	}
	public function login($username,$password)
	{
		$user=UserModel::G()->getUserByName($username);
		UserException::ThrowOn(!$user,"用户不存在");
		
		$flag=UserModel::G()->checkPass($password,$user['password']);
		UserException::ThrowOn(!$flag,"密码错误");
		
		ActionLogModel::G()->log("$username 登录成功");
		unset($user['password']);
		return $user;
	}
	public function getUser($id)
	{
		$user=UserModel::G()->getUserDirect($id);
		unset($user['password']);
		return $user;
	}
	public function changePassword($id,$oldpass,$newpass)
	{
		$user=UserModel::G()->getUserDirect($id);
		UserException::ThrowOn(!$user,"用户不存在");
		$flag=UserModel::G()->checkPass($oldpass,$user['password']);
		UserException::ThrowOn(!$flag,"旧密码错误");
		
		UserModel::G()->changePass($user['id'],$newpass);;
		
	}
}