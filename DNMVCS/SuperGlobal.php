<?php
namespace DNMVCS;

class SuperGlobal
{
	use DNSingleton;
	
	public static function GET($k)
	{
		return  SuperGlobalGET::Get($k);
	}
	public static function POST($k)
	{
		return  SuperGlobalPOST::Get($k);
	}
	public static function REQUEST($k)
	{
		return  SuperGlobalREQUEST::Get($k);
	}
	public static function COOKIE($k)
	{
		return  SuperGlobalCOOKIE::Get($k);
	}
	public static function SERVER($k)
	{
		return  SuperGlobalSERVER::Get($k);
	}
	public static function ENV($k)
	{
		return  SuperGlobalENV::Get($k);
	}
	public static function SESSION($k)
	{
		return  SuperGlobalSESSION::Get($k);
	}
///////////////////////////////
	public static function StartSession()
	{
		return SuperGlobalSESSION::Start();
	}
	public static function DestroySession()
	{
		return SuperGlobalSESSION::Destroy();
	}
}
class SuperGlobalBase
{

	use DNSingleton;
	public $data=[];
	
	public function __construct()
	{
		$this->init();
	}
	public function init()
	{
		throw \Exception("Impelement Me!");
	}
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

class SuperGlobalGET extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_GET??[];
	}
}
class SuperGlobalPOST extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_POST??[];
	}
}
class SuperGlobalCOOKIE extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_GET??[];
	}
}
class SuperGlobalREQUEST extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_REQUEST??[];
	}
}
class SuperGlobalSERVER extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_SERVER;
	}
}
class SuperGlobalENV extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_ENV;
	}
}
class SuperGlobalSESSION extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_SESSION??[];
	}
	public static function Start()
	{
		return static::G()->_Start();
	}
	public static function Destroy()
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