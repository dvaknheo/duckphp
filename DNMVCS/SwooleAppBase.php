<?php
namespace DNMVCS;

class RouteWithSuperGlobal extends DNRoute
{
	public function _SERVER($key)
	{
		return  SERVER::Get($key);
	}
	public function _GET($key)
	{
		return  HTTP_GET::Get($key);
	}
	public function _POST($key)
	{
		return  HTTP_POST::Get($key);
	}
	public function _REQUEST($key)
	{
		return  REQUEST::Get($key);
	}
}

class RouteRewriteHookWithSuperGlobal extends RouteRewriteHook
{
	protected function mergeHttpGet($get)
	{
		foreach($get as $k=>$v){
			HTTP_GET::Set($k,$v);
		}
	}
}

class SwooleSuperGlobalServer extends SuperGlobal
{
	public function init($request)
	{
		foreach($request->server as $k=>$v){
			$this->data[strtoupper($k)]=$v;
		}
	}
	// 把 header 也引进来。
}

class SwooleSuperGlobalGet extends SuperGlobal
{
	public function init($request)
	{
		$this->data=$request->get??[];
	}
}
class SwooleSuperGlobalPost extends SuperGlobal
{
	public function init($request)
	{
		$this->data=$request->post??[];
	}
}
class SwooleSuperGlobalRequest extends SuperGlobal
{
	public function init($request)
	{
		$this->data=array_merge($request->get??[],$request->post??[]);
	}
}
class SwooleSuperGlobalCookie extends SuperGlobal
{
	public function init($request)
	{
		$this->data=$request->cookie??[];
	}
}
class SwooleAppBase extends DNMVCS
{
	public function init($options=[])
	{
		$options['default_controller_reuse']=false;
		DNMVCS::ImportSys('SuperGlobal');
		DNRoute::G(RouteWithSuperGlobal::G());
		RouteRewriteHook::G(RouteRewriteHookWithSuperGlobal::G());
		parent::init($options);
		
		return $this;
	}
	public function run()
	{
		$request=$this->options['request'];
		SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		HTTP_GET::G(SwooleSuperGlobalGet::G())->init($request);
		HTTP_POST::G(SwooleSuperGlobalPost::G())->init($request);
		HTTP_REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		SERVER::Set('DOCUMENT_ROOT',$this->options['path']);
		SERVER::Set('SCRIPT_FILENAME',$this->options['path'].'index.php');
		return parent::run();
	}
}