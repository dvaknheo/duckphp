<?php
declare(strict_types=1);
namespace DNMVCS 
{
	class SuperGlobal
	{
		use DNSingleton;
		public static function Init()
		{
			//Just for Load This file.
		}
		public static function GET($key)
		{
			return  SuperGlobal\GET::Get($key);
		}
		public static function POST($key)
		{
			return  SuperGlobal\POST::Get($key);
		}
		public static function REQUEST($key)
		{
			return  SuperGlobal\GET::Get($key);
		}
		public static function COOKIE($key)
		{
			return  SuperGlobal\GET::Get($key);
		}
		public static function SERVER($key)
		{
			return  SuperGlobal\SERVER::Get($key);
		}
		public static function ENV($key)
		{
			return  SuperGlobal\ENV::Get($key);
		}
		public static function SESSION($key)
		{
			return  SuperGlobal\SESSION::Get($key);
		}
		public static function SetSession($key,$value)
		{
			return  SuperGlobal\SESSION::Set($key,$value);
		}
		public static function StartSession()
		{
			return  SuperGlobal\SESSION::Start();
		}
		public static function DestroySession()
		{
			return  SuperGlobal\SESSION::Destroy();
		}
	}
}
namespace DNMVCS\SuperGlobal
{
class SuperGlobalBase
{
	use \DNMVCS\DNSingleton;
	protected $data=[];
	public static function Get(string $k)
	{
		return static::G()->_Get($k);
	}
	public static function Set(string $k,$v)
	{
		return static::G()->_Set($k,$v);
	}
	public static function Remove(string $k)
	{
		return static::G()->_Remove($k);
	}
	public static function All()
	{
		return static::G()->_All();
	}
	public function _Get(string $k)
	{
		return $this->data[$k]??null;
	}
	public function _Set(string $k,$v)
	{
		$this->data[$k]=$v;
	}
	public function _Remove(string $k)
	{
		unset($this->data[$k]);
	}
	public function _All()
	{
		return $this->data;
	}
}


class GET extends  SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_GET[$k];
	}
	public function _Set(string $k,$v)
	{
		$_GET[$k]=$v;
	}
	public function _Remove(string $k)
	{
		unset($_GET[$k]);
	}
	public function _All()
	{
		return $_GET;
	}
}
class POST extends SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_POST[$k];
	}
	public function _Set(string $k,$v)
	{
		$_POST[$k]=$v;
	}
	public function _Remove(string $k)
	{
		unset($_POST[$k]);
	}
	public function _All()
	{
		return $_POST;
	}
}
class REQUEST extends SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_REQUEST[$k];
	}
	public function _Set(string $k,$v)
	{
		$_REQUEST[$k]=$v;
	}
	public function _Remove(string $k)
	{
		unset($_REQUEST[$k]);
	}
	public function _All()
	{
		return $_REQUEST;
	}
}
class SERVER extends SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_SERVER[$k];
	}
	public function _Set(string $k,$v)
	{
		$_SERVER[$k]=$v;
	}
	public function _Remove(string $k)
	{
		unset($_SERVER[$k]);
	}
	public function _All()
	{
		return $_SERVER;
	}
}
class COOKIE extends SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_COOKIE[$k];
	}
	public function _All()
	{
		return $_COOKIE;
	}
}

class ENV extends SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_ENV[$k];
	}
	public function _All()
	{
		return $_ENV;
	}
}
class SESSION extends SuperGlobalBase
{
	public function _Get(string $k)
	{
		return $_SESSION[$k];
	}
	public function _Set(string $k,$v)
	{
		$_SESSION[$k]=$v;
	}
	public function _Remove(string $k)
	{
		unset($_SESSION[$k]);
	}
	public function _All()
	{
		return $_SESSION;
	}
	public function Start()
	{
		return static::G()->_Start();
	}
	public function Destroy()
	{
		return static::G()->_Destroy();
	}
	
	public function _Start()
	{
		if(session_status() !== PHP_SESSION_ACTIVE ){session_start();}
	}
	public function _Destroy()
	{
		session_destroy();
	}
}

}