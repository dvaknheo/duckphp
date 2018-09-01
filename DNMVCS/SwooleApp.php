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
		unset($_SERVER['argc']);
		unset($_SERVER['argv']);
		foreach($this->obj->server as $k=>$v){
			$_SERVER[strtoupper($k)]=$v;
		}
		
		$_GET=$this->obj->get??[];
		$_POST=$this->obj->post??[];
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
class SwooleHttpResponse // extends \swoole_http_response
{
	use DNSingleton;
	use DNWrapper;
	
	//remark ,must declear this
	public function write($str)
	{
		return $this->_object_wrapping->write($str);
	}
	public function end(...$args)
	{
		return $this->_object_wrapping->end(...$args);
	}
	public static function init()
	{
		$res=$this->_object_wrapping;
		ob_start(function($str) use($res){
			$res->write($str);
		});
	}
	public static function cleanUp()
	{
		ob_end_flush();
		SwooleHttpResponse::G()->end(true);
	}
}
////////////////
//WebSocket
class SwooleFrame
{
	use DNSingleton;
	use DNWrapper;
}
class SwooleWebSocketRequest // extends \swoole_http_request
{
	use DNSingleton;
	use DNWrapper;
	public function init($server,$frame)
	{
	}
	public function cleanUp()
	{

	}
}
class SwooleWebSocketSession // extends \swoole_http_response
{
	use DNSingleton;
	use DNWrapper;
	
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

	}
}
///////////
class SwooleApp
{
	use DNSingleton;
	
	public $onHttpRun=null;
	public $onHttpException=null;
	public $onHttpCleanUp=null;
	public $onWebSoketRun=null;
	public $onWebSoketException=null;
	public $onWebSoketCleanUp=null;

	public function onRequest($req,$res)
	{
		
		SwooleHttpResponse::G(SwooleHttpResponse::W($res))->init();
		SwooleHttpRequest::G(SwooleHttpRequest::W($req))->init();
		try{
			($this->onHttpRun)($req,$res);
		}catch(\Throwable $ex){
			($this->onHttpException)($ex);
		}
		($this->onHttpCleanUp)();
		
		SwooleHttpRequest::G()->cleanUp();
		SwooleHttpResponse::G()->cleanUp();

	}
	public function bindHttp($server,$onHttpRun,$onHttpException,$onHttpCleanUp)
	{
		$server->on('request',[$this,'onRequest']);
		$this->onHttpRun=$onHttpRun;
		$this->onHttpException=$onHttpException;
		$this->onHttpCleanUp=$onHttpCleanUp;
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
