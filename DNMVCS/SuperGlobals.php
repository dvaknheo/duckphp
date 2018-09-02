<?php
namespace DNMVCS;
//未使用，用于不想用 PHP 的超级变量的场合
class SuperGlobal
{
	use \DNMVCS\DNSingleton;
	public static function Get($k)
	{
		return self::G()->_Get($k);
	}
	public static function Set($k,$v)
	{
		return self::G()->_Set($k,$v);
	}
	public static function Remove($k)
	{
		return self::G()->_Remove($k);
	}
	public static function All()
	{
		return self::G()->_All();
	}
	public function _Get($k)
	{
		throw new \Exception('No impelement');
	}
	public function _Set($k,$v)
	{
		throw new \Exception('No impelement');
	}
	public function _Remove($k)
	{
		throw new \Exception('No impelement');
	}
	public static function _All()
	{
		throw new \Exception('No impelement');
	}
}
class HTTP_GET extends  SuperGlobal
{
	public function _Get($k)
	{
		return $_GET[$k];
	}
	public function _Set($k,$v)
	{
		$_GET[$k]=$v;
	}
	public function _Remove($k)
	{
		unset($_GET[$k]);
	}
	public static function _All()
	{
		return $_GET;
	}
}
class HTTP_POST extends SuperGlobal
{
	public function _Get($k)
	{
		return $_POST[$k];
	}
	public function _Set($k,$v)
	{
		$_POST[$k]=$v;
	}
	public function _Remove($k)
	{
		unset($_POST[$k]);
	}
	public static function _All()
	{
		return $_POST;
	}
}
class HTTP_REQUEST extends SuperGlobal
{
	public function _Get($k)
	{
		return $_REQUEST[$k];
	}
	public function _Set($k,$v)
	{
		$_REQUEST[$k]=$v;
	}
	public function _Remove($k)
	{
		unset($_REQUEST[$k]);
	}
	public static function _All()
	{
		return $_REQUEST;
	}
}
class SERVER extends SuperGlobal
{
	public function _Get($k)
	{
		return $_SERVER[$k];
	}
	public function _Set($k,$v)
	{
		$_SERVER[$k]=$v;
	}
	public function _Remove($k)
	{
		unset($_SERVER[$k]);
	}
	public static function _All()
	{
		return $_SERVER;
	}
}
class COOKIE extends SuperGlobal
{
	public function _Get($k)
	{
		return $_COOKIE[$k];
	}
	public static function _All()
	{
		return $_COOKIE;
	}
}

class ENV exetends SuperGlobal
{
	public function _Get($k)
	{
		return $_ENV[$k];
	}
	public static function _All()
	{
		return $_ENV;
	}
}
class SESSION extends SuperGlobal
{
	public function _Get($k)
	{
		return $_SESSION[$k];
	}
	public function _Set($k,$v)
	{
		$_SESSION[$k]=$v;
	}
	public function _Remove($k)
	{
		unset($_SESSION[$k]);
	}
	public static function _All()
	{
		return $SESSION;
	}
	public function Start()
	{
		return self::G()->_Start();
	}
	public function _Start()
	{
		if(session_status() !== PHP_SESSION_ACTIVE ){session_start();}
	}
}
