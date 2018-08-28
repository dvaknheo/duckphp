<?php
namespace DNMVCS;
/////// 用于 swoole;还要多测试。
class SwooleApp extends DNMVCS
{
	public function onShow404()
	{
		echo '404';
		//不能用 header 了。 
	}
	protected function initExceptionManager()
	{
		//关闭默认处理。stop default feature
		return;
	}
	public function onRequest($req,$res)
	{
		try{
			SwooleRoute::G(new SwooleRoute()); // 不复用
			SwooleRoute::G()->initSwoole($req,$res);
			DNRoute::G(SwooleRoute::G());
			DNMVCS::G()->initRoute(DNRoute::G());
			DNView::G(new DNView()); // 不复用
			self::G()->initView(DNView::G());

			SwooleRoute::G()->run();

		}catch(\Throwable  $ex){
			self::G()->onException($ex);
		}
	}
	public static function RunSwooleQuickly($server,$options,$file='')
	{
		DNMVCS::G(SwooleApp::G())->init($options);
		$server->on('request',
			function($req,$res)use($options,$file){
				ob_start(function($str)use($res){
					$res->write($str);
				});
				if(!$file){
					self::G()->onRequest($req,$res);
				}else{
					include($file);
				}
				ob_end_flush();
				$res->end();
			}
		);
		$server->start();
	}
	public static function RunSwooleQuicklyx($server,$options)
	{
		self::BindSwooleHttpServer($server,$options);
		$server->start();
	}

}

class SwooleRoute extends \DNMVCS\DNRoute
{
	public $req_server;
	public $req_get;
	public $req_post;
	
	public function init($options)
	{
		parent::init($options);
		$this->path_info=$this->_SERVER('PATH_INFO')??'';
		$this->request_method=$this->_SERVER('REQUEST_METHOD')??'';
		$this->path_info=ltrim($this->path_info,'/');
	}
	public function initSwoole($req,$res)
	{
		$this->req_server=$req->server;
		$this->req_get=$req->get;
		$this->req_post=$req->post;
	}
	public function _SERVER($key)
	{
		$key=strtolower($key);
		$a=$this->req_server;
		return  $a[$key]??null;
	}
	public function _GET($key)
	{
		return $this->req_get[$key]??null;
	}
	public function _POST($key)
	{
		return $this->req_post[$key]??null;
	}
	public function _REQUEST($key)
	{
		return $this->_POST($key)??$this->_GET($key);
	}
	protected function includeControllerFile($file)
	{
		//我们只包含一次。重复文件不用偷懒的  DNContorller 模式在这里不能用了
		require_once($file);
	}
}