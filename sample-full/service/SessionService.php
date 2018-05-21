<?php
class SessionService extends DnService
{
	// 注意这里是有状态的，和其他 Service 不同。
	// 属于特殊的 Service
	public function __construct()
	{
		session_name('dnfull');
		session_start();
		
	
	}
	public function getCurrentUser()
	{
		$user=isset($_SESSION['user'])?$_SESSION['user']:array();
		return $user;
	}
	public function setCurrentUser($user)
	{
		$_SESSION['user']=$user;
	}
	public function logout()
	{
		//unset($_SESSION);
		session_destroy();
	}
}