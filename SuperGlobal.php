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
	public function  run()
	{
	}
	public static function CheckLoad()
	{
	}
	public static function GET($k)
	{
		return static::G()->_GET[$k]??null;
	}
	public static function POST($k)
	{
		return static::G()->_POST[$k]??null;
	}
	public static function REQUEST($k)
	{
		return static::G()->_REQUEST[$k]??null;
	}
	public static function SERVER($k)
	{
		return static::G()->_SERVER[$k]??null;
	}
	public static function COOKIE($k)
	{
		return static::G()->_COOKIE[$k]??null;
	}
	public static function ENV($k)
	{
		return static::G()->_ENV[$k]??null;
	}
	public static function SESSION($k)
	{
		return static::G()->_SESSION[$k]??null;
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
	public function _StartSession()
	{
		if(session_status() !== PHP_SESSION_ACTIVE ){ session_start(); }
		$this->_SESSION=&$_SESSION;
	}
	public function _DestroySession()
	{
		session_destroy();
		$this->_SESSION=[];
	}
	public static function SetSessionHandler($handler)
	{
		return static::G()->_SetSessionHandler($handler);
	}
	public function _SetSessionHandler($handler)
	{
		session_set_save_handler($handler);
	}
	public static function SetSessionName($name)
	{
		return static::G()->_SetSessionName($name);
	}
	public function _SetSessionName($name)
	{
		return session_name($name);
	}
///////////////////////////
	public static function SetGET($k,$v)
	{
		static::G()->_GET[$k]=$v;
	}
	public static function SetPOST($k,$v)
	{
		static::G()->POST[$k]=$v;
	}
	public static function SetREQUEST($k,$v)
	{
		static::G()->_REQUEST[$k]=$v;
	}
	public static function SetSERVER($k,$v)
	{
		static::G()->_SERVER[$k]=$v;
	}
	public static function SetSESSION($k,$v)
	{
		static::G()->_SESSION[$k]=$v;
	}
	public static function GetSESSION($k)
	{
		return static::G()->_SESSION[$k]??null;
	}
}