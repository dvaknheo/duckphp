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
		return $this->obj->write($str);
	}
	public static function init()
	{
		ob_start(function($str){
			SwooleHttpResponse::G()->write($str);
		});
	}
	public static function cleanUp()
	{
		ob_end_flush();
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
	public $onDoRequest=null;
	
	public function onHttpRequestRun()
	{
		DNMVCS::G()->run();
	}
	public function onHttpRequestException($ex)
	{
		DNMVCS::G()->onException($ex);
	}
	public function onHttpRequestCleanUp()
	{
		DNMVCS::G()->cleanUp();
	}
	protected function doRequestCleanUp()
	{
		$this->onHttpRequestCleanUp();
		
		SwooleHttpRequest::G()->cleanUp();
		SwooleHttpResponse::G()->cleanUp();
		
		SwooleHttpRequest::G(SwooleHttpRequest::W(new \stdClass())); // cleanup
		SwooleHttpResponse::G(SwooleHttpResponse::W(new \stdClass())); //cleanup
	}
	public function onRequest($req,$res)
	{
		try{
			SwooleHttpResponse::G(SwooleHttpResponse::W($res))->init();
			SwooleHttpRequest::G(SwooleHttpRequest::W($req))->init();
			if($this->onDoRequest){
				$ret=($this->onDoRequest)($req,$res);
				$this->doRequestCleanUp();
				return $ret;
			}
			
			$this->onHttpRequestRun();
			$this->doRequestCleanUp();
		}catch(\Throwable $ex){
			$this->onHttpRequestException($ex);
			$this->doRequestCleanUp();
		}
	}
	public static function BindSwooleHttpServer($server,$options)
	{
		DNMVCS::G()->init($options);
		DNMVCS::G()->addRouteHook([ReuseRouteHook::class,'hook'],true);
		$server->on('request',[self::G(),'onRequest']);
	}
	public static function RunSwooleQuickly($server,$options,$file='')
	{
		self::BindSwooleHttpServer($server,$options);
		if($file!=''){
			self::G()->onDoRequest=function($req,$res)use($file){
				include($file);
			};
		}
		SwooleServer::G(SwooleServer::W($server));
		
		$server->on('open', function ( $server, $request) {
			echo "server: open handshake success with fd{$request->fd}\n";
		});
		
		$server->on('message', function ( $server, $frame) {
			self::G()->onMessage($server,$frame);
		});

		$server->on('close', function ($ser, $fd) {
			echo "client {$fd} closed\n";
		});
		
		$server->start();
	}
/////////////////////////////////
	public function onWebSocketMessageRun()
	{
		$data=SwooleWebSocketSession::G()->getData();
	}
	public function onWebSocketMessageException($ex)
	{
		echo $ex;
	}
	public function onWebSocketMessageCleanUp()
	{
	}
	public function onMessage($server,$frame)
	{		
		try{
			SwooleWebSocketSession::G()->init($server,$frame);
			$this->onWebSocketMessageRun($server,$frame);
			$this->onWebSocketMessageCleanUp();
			SwooleWebSocketSession::G()->cleanUp();
		}catch(\Throwable $ex){
			$this->onWebSocketMessageException($ex);
			SwooleWebSocketSession::G()->cleanUp();
			
		}
	}
}
