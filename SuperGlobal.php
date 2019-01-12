<?php
namespace DNMVCS;

class SuperGlobal
{
	use DNSingleton;
	
	public $_GET;
	public $_POST;
	public $_REQUEST;
	public $_SERVER;
	public $_ENV;
	public $_COOKIE;
	public $_SESSION;
	public function __construct()
	{
		$this->init();
	}
	protected function init()
	{	
		$this->_GET		=&$_GET;
		$this->_POST	=&$_POST;
		$this->_REQUEST	=&$_REQUEST;
		$this->_SERVER	=&$_SERVER;
		$this->_ENV		=&$_ENV;
		$this->_COOKIE	=&$_COOKIE;
		$this->_SESSION	=&$_SESSION;
	}
///////////////////////////////
	public static function StartSession()
	{
		return static::G()->_StartSession();
	}
	public static function DestroySession()
	{
		return static::G()->_DestroySession();
	}
	public static function SetSessionHandler($handler)
	{
		return static::G()->_SetSessionHandler($handler);
	}
	public static function SetSessionName($name)
	{
		return static::G()->_SetSessionName($name);
	}
///////////////////////////////
	public function _StartSession(array $options=[])
	{
		if(session_status() !== PHP_SESSION_ACTIVE ){ session_start($options); }
		$this->_SESSION=&$_SESSION;
	}
	public function _DestroySession()
	{
		session_destroy();
		$this->_SESSION=[];
	}

	public function _SetSessionHandler($handler)
	{
		session_set_save_handler($handler);
	}
	public function _SetSessionName($name)
	{
		return session_name($name);
	}
}