<?php
namespace DNMVCS;

class SwooleHttpd
{
	use DNSingleton;
	
	public $server=null;
	
	public $onInit=null;
	public $onHttpRun=null;
	public $onHttpException=null;
	public $onHttpCleanUp=null;
	public $onWebSoketRun=null;
	public $onWebSoketException=null;
	public $onWebSoketCleanUp=null;
	public $isInited=false;
	
	public $response=null;
	
	public $frame=null;
	protected $isInHttpException=false;
	
	public static function Server()
	{
		return self::G()->server;
	}
	public function onRequest($request,$response)
	{
		$this->initResponse($response);
		
		if(!$this->isInited){
			($this->onInit)();
			$this->isInited=true;
		}
		try{
			$this->isInHttpException=false;
			($this->onHttpRun)($request,$response);
		}catch(\Throwable $ex){
			($this->onHttpException)($ex);
			$this->isInHttpException=true;
		}
		($this->onHttpCleanUp)();
		
		$this->cleanUpResponse();

	}
	public function bindHttp($server,$onInit,$onHttpRun,$onHttpException,$onHttpCleanUp)
	{
		$this->server=$server;
		$this->onInit=$onInit ;
		$this->onHttpRun=$onHttpRun;
		$this->onHttpException=$onHttpException;
		$this->onHttpCleanUp=$onHttpCleanUp;

		$this->server->on('request',[$this,'onRequest']);
		return $this;
	}
	
	protected function initResponse($response)
	{
		$this->response=$response;
		ob_start(function($str) use($response){
			if(''===$str){return;}
			$response->write($str);
		});
	}
	public function cleanUpResponse()
	{
		ob_end_flush();
		if(!$this->isInHttpException){ 
			$this->response->end();
		}
		
		$this->response=null;
	}
	
	
/////////////////////////////////
	public function onMessage($server,$frame)
	{
		$this->frame=$frame;
		$fd=$frame->fd;
		ob_start(function($str)use($fd){
				$this->server->push($fd,$str);
		});
		
		try{
			($this->onWebSoketRun)($server,$frame);
		}catch(\Throwable $ex){
			($this->onWebSoketException)($ex);
		}
		($this->onWebSoketCleanUp)();
		
		
		ob_end_flush();
		$this->frame=null;
	}
	public function bindWebSocket($server,$onWebSoketRun,$onWebSoketException,$onWebSoketCleanUp)
	{
		$this->server=$server;
		$this->onWebSoketRun=$onWebSoketRun;
		$this->onWebSoketException=$onWebSoketException;
		$this->onWebSoketCleanUp=$onWebSoketCleanUp;
		
		$server->on('message',[$this,'onMessage']);
	}
	public function run()
	{
		$this->server->start();
	}
}
