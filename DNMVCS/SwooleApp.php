<?php
namespace DNMVCS;

/////// 用于 swoole;还要多测试。
//server
class SwooleServer
{
	use DNSingleton;
	use DNWrapper;
	public static function InitSingleton($server)
	{
		self::G(self::W($server));
	}
}
class SwooleHttpServer
{
	use DNSingleton;
	use DNWrapper;
	public static function InitSingleton($server)
	{
		self::G(self::W($server));
	}
}
class SwooleWebSocketServer
{
	use DNSingleton;
	use DNWrapper;
	public static function InitSingleton($server)
	{
		self::G(self::W($server));
	}
}
/////////////////////
//Http
class SwooleHttpRequest // extends \swoole_http_request
{
	use DNSingleton;
	use DNWrapper;
	public $init_server;
	public function init()
	{
		$this->init_server=$_SERVER;
		$_SERVER=[];
		foreach($this->_object_wrapping->server as $k=>$v){
			$_SERVER[strtoupper($k)]=$v;
		}
		
		$_GET=$this->_object_wrapping->get??[];
		$_POST=$this->_object_wrapping->post??[];
		$_REQUEST=array_merge($_GET,$_POST);
		
	}
	public function cleanUp()
	{
		$_SERVER=$this->init_server;
		$_GET=[];
		$_POST=[];
		$_REQUEST=[];
		//TODO cookie, session  and other super globals
	}
}
////////////////
//WebSocket
class SwooleFrame
{
	use DNSingleton;
	use DNWrapper;
}

class SwooleWebSocketSession // extends \swoole_http_response
{
	use DNSingleton;
	
	public $server;
	public $frame;

	public function init($server,$frame)
	{
		$this->server=$server;
		$this->frame=$frame;
		ob_start(function($str){
				SwooleWebSocketSession::G()->server->push(
				SwooleWebSocketSession::G()->frame->fd,$str
			);
		});
	}
	public function getFD()
	{
		return $this->frame->fd;
	}
	public function getData()
	{
		return $this->frame->data;
	}
	public static function cleanUp()
	{
		ob_end_flush();
		$this->server=null;
		$this->frame=null;
	}
}
///////////
class SwooleApp
{
	use DNSingleton;
	
	public $onInit=null;
	public $onHttpRun=null;
	public $onHttpException=null;
	public $onHttpCleanUp=null;
	public $onWebSoketRun=null;
	public $onWebSoketException=null;
	public $onWebSoketCleanUp=null;
	public $isInited=false;
	
	public $request=null;
	public $response=null;
	public function onRequest($req,$res)
	{
		$this->response=$res;
		
		ob_start(function($str) use($res){
			if(''===$str){return;}
			$res->write($str);
		});
		
		if(!$this->isInited){
			($this->onInit)();
			$this->isInited=true;
		}
		SwooleHttpRequest::G(SwooleHttpRequest::W($req))->init();
		
		$is_exception=false;
		try{
			($this->onHttpRun)($req,$res);
		}catch(\Throwable $ex){
			($this->onHttpException)($ex);
			$is_exception=true;
		}
		($this->onHttpCleanUp)();
		
		SwooleHttpRequest::G()->cleanUp();
		
		ob_end_flush();
		if(!$is_exception){ $this->response->end(); }
		
		//$this->request=null;
		$this->response=null;
	}
	public function bindHttp($server,$onInit,$onHttpRun,$onHttpException,$onHttpCleanUp)
	{
		$this->onInit=$onInit ;
		$this->onHttpRun=$onHttpRun;
		$this->onHttpException=$onHttpException;
		$this->onHttpCleanUp=$onHttpCleanUp;
		
		$server->on('request',[$this,'onRequest']);
		return $this;
	}
/////////////////////////////////
	public function onMessage($server,$frame)
	{		
		SwooleWebSocketSession::G()->init($server,$frame);
		try{
			($this->onWebSoketRun)($server,$frame);
		}catch(\Throwable $ex){
			($this->onWebSoketException)($ex);
		}
		($this->onWebSoketCleanUp)();
		SwooleWebSocketSession::G()->cleanUp();
	}
	public function bindWebSocket($server,$onWebSoketRun,$onWebSoketException,$onWebSoketCleanUp)
	{
		$server->on('message',[$this,'onMessage']);
		$this->onWebSoketRun=$onWebSoketRun;
		$this->onWebSoketException=$onWebSoketException;
		$this->onWebSoketCleanUp=$onWebSoketCleanUp;
		
	}
}
