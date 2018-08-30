<?php
namespace DNMVCS;
require_once(__DIR__.'/DNMVCSExt.php');

/////// 用于 swoole;还要多测试。
class SwooleRequest // extends \swoole_http_request
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
class SwooleResponse // extends \swoole_http_response
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
			SwooleResponse::G()->write($str);
		});
	}
	public static function cleanUp()
	{
		ob_end_flush();
	}
}
class SwooleApp
{
	use DNSingleton;
	
	public $onDoRequest=null;
	protected function doRequestRun($req,$res)
	{
		SwooleRequest::G(SwooleRequest::W($req));
		SwooleResponse::G(SwooleResponse::W($res));
		SwooleResponse::G()->init();
		SwooleRequest::G()->init();
		if($this->onDoRequest){
			$ret=($this->onDoRequest)($req,$res);
			$this->doRequestCleanUp();
			return $ret;
		}
		DNMVCS::G()->run();
		
		$this->doRequestCleanUp();
	}
	protected function doRequestCleanUp()
	{
		DNMVCS::G()->cleanUp();
		SwooleRequest::G()->cleanUp();
		SwooleResponse::G()->cleanUp();
		
		SwooleRequest::G(SwooleRequest::W(new \stdClass)); // cleanup
		SwooleResponse::G(SwooleResponse::W(new \stdClass)); //cleanup
	}
	protected function doRequestException(\Throwable  $ex)
	{
		DNMVCS::G()->onException($ex);
		$this->doRequestCleanUp();
	}
	public function onRequest($req,$res)
	{
		try{
			$this->doRequestRun($req,$res);
		}catch(\Throwable $ex){
			$this->doRequestException($ex);
		}
	}
	public static function BindSwooleHttpServer($server,$options)
	{
		DNMVCS::G()->init($options);
		DNMVCS::G()->addRouteHook(
			function($route){
				$route->options['default_controller_reuse']=false;
				$route->path_info=$route->_SERVER('PATH_INFO')??'';
				$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
				$route->path_info=ltrim($route->path_info,'/');
			},true);

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
		$server->start();
	}
}
