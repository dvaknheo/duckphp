<?php
namespace DNMVCS;
use DNMVCS\SuperGlobal\SuperGlobalBase;

class SwooleSuperGlobal
{
	use DNSingleton;
	public static function Init()
	{
		//Just for Load This file.
	}
}

class SwooleSuperGlobalServer extends SuperGlobalBase
{
	public function init($request)
	{
		foreach($request->header as $k=>$v){
			$k='HTTP_'.str_replace('-','_',strtoupper($k));
			$this->data[$k]=$v;
		}
		foreach($request->server as $k=>$v){
			$this->data[strtoupper($k)]=$v;
		}
	}
}

class SwooleSuperGlobalGet extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=$request->get??[];
	}
}
class SwooleSuperGlobalPost extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=$request->post??[];
	}
}
class SwooleSuperGlobalRequest extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=array_merge($request->get??[],$request->post??[]);
	}
}
class SwooleSuperGlobalCookie extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=$request->cookie??[];
	}
}

class SwooleSuperGlobalSession extends SuperGlobal\SESSION
{
	protected $handler=null;
	protected $session_id='';
	protected $is_started=false;
	
	public function init($request)
	{
		//do nothing;
	}
	public function setHandler($handler)
	{
		// \SessionHandlerInterface
		$this->handler=$handler;
	}
	public function _Start()
	{
		if(!$this->handler){
			$this->handler=new SwooleSessionHandler();
		}
		$this->is_started=true;
		$session_name=session_name();
		$session_save_path=session_save_path();
		
		$session_id=SuperGlobal\COOKIE::Get($session_name);
		if($session_id===null || ! preg_match('/[a-zA-Z0-9,-]+/',$session_id)){
			$session_id=$this->create_sid();
		}
		$this->session_id=$session_id;
		
		DNSwooleHttpServer::setcookie($session_name,$this->session_id
			,ini_get('session.cookie_lifetime')?time()+ini_get('session.cookie_lifetime'):0
			,ini_get('session.cookie_path')
			,ini_get('session.cookie_domain')
			,ini_get('session.cookie_secure')
			,ini_get('session.cookie_httponly')
		);
		
		if(ini_get('session.gc_probability') > mt_rand(0,ini_get('session.gc_divisor'))){
			$this->handler->gc(ini_get('session.gc_maxlifetime'));
		}
		$this->handler->open($session_save_path,$session_name);
		$raw=$this->handler->read($this->session_id);
		$this->data=unserialize($raw);
		if(!$this->data){$this->data=[];}
	}
	public function _Destroy()
	{
		$session_name=session_name();
		$this->handler->destroy($this->session_id);
		$this->data=[];
		DNSwooleHttpServer::setcookie($session_name,'');
	}
	public function writeClose()
	{
		if(!$this->is_started){return;}
		$this->handler->write($this->session_id,serialize($this->data));
		$this->data=[];
	}
	protected function create_sid()
	{
		return md5(microtime().mt_rand());
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