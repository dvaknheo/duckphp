<?php
namespace UUU\Service;

use UUU\Base\BaseService;
use UUU\Base\ServiceHelper;
use UUU\Base\App;

class SessionService extends BaseService
{
	// 注意这里是有状态的，和其他 Service 不同。
	// 属于特殊的 Service
	public function __construct()
	{
        //session_start();
		App::session_start();
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
	public function adminLogin()
	{
		$_SESSION['admin_logined']=true;
	}
	public function checkAdminLogin()
	{
		return isset($_SESSION['admin_logined'])?true:false;
	}
	public function adminLogout()
	{
		unset($_SESSION['admin_logined']);
	}
	public function csrf_token()
	{
		$_SESSION['_CSRF']=uniqid();
		return $_SESSION['_CSRF'];
	}
	public function csrf_check($token)
	{
		return isset($_SESSION['_CSRF']) && $_SESSION['_CSRF']===$token?true:false;
	}
}