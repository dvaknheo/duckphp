<?php
namespace DNMVCS;

class SwooleSuperGlobal extends SuperGlobal
{

}

class SwooleSuperGlobalGET extends SuperGlobalBase
{
	public function init()
	{
		$this->data=DNSwooleHttpServer::Request()->get??[];
	}
}
class SwooleSuperGlobalPost extends SuperGlobalBase
{
	public function init()
	{
		$this->data=DNSwooleHttpServer::Request()->post??[];
	}
}
class SwooleSuperGlobalCOOKIE extends SuperGlobalBase
{
	public function init()
	{
		$this->data=DNSwooleHttpServer::Request()->cookie??[];
	}
}
class SwooleSuperGlobalREQUEST extends SuperGlobalBase
{
	public function init()
	{
		$request=DNSwooleHttpServer::Request();
		$this->data=array_merge($request->get??[],$request->post??[]);
	}
}
class SwooleSuperGlobalSERVER extends SuperGlobalBase
{
	public function init()
	{
		$request=DNSwooleHttpServer::Request();
		foreach($request->header as $k=>$v){
			$k='HTTP_'.str_replace('-','_',strtoupper($k));
			$this->data[$k]=$v;
		}
		foreach($request->server as $k=>$v){
			$this->data[strtoupper($k)]=$v;
		}
	}
}
class SwooleSuperGlobalENV extends SuperGlobalBase
{
	public function init()
	{
		$this->data=$_ENV;
	}
}

class SwooleSuperGlobalSession extends SuperGlobalBase
{
	use DNSingleton;

	protected $handler=null;
	protected $session_id='';
	protected $is_started=false;
	public $data;
	
	public function init()
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
		
		DNSwooleHttpServer::register_shutdown_function([$this,'writeClose']);
		$session_name=session_name();
		$session_save_path=session_save_path();
		
		$session_id=SwooleSuperGlobalCOOKIE::Get($session_name);
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
}