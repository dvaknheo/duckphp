<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;
use \DNMVCS\SuperGlobal\SuperGlobalBase;

class RouteWithSuperGlobal extends DNRoute
{
	public function _SERVER($key)
	{
		return  SuperGlobal\SERVER::Get($key);
	}
	public function _GET($key)
	{
		return  SuperGlobal\HTTP_GET::Get($key);
	}
	public function _POST($key)
	{
		return  SuperGlobal\HTTP_POST::Get($key);
	}
	public function _REQUEST($key)
	{
		return  SuperGlobal\REQUEST::Get($key);
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
class SwooleReuseRouteHook
{
	public static function hook($route){
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
	}
}

class CoroutineSingleton
{
	public static function G($object=null,$class)
	{

		$cid = \Swoole\Coroutine::getuid();
		if($cid<0){return null;}
		if($cid===0){
			if($object){
			DNSingletonStaticClass::$_instances[$class]=$object;
			return $object;
			}
			$me=DNSingletonStaticClass::$_instances[$class]??null;
			if(null===$me){
				$me=new $class();
				DNSingletonStaticClass::$_instances[$class]=$me;
			}
			return $me;
		}
		
		$key="cid-$cid";
		DNSingletonStaticClass::$_instances[$key]=self::$_instances[$key]??[];
		if($object){
			DNSingletonStaticClass::$_instances[$key][$class]=$object;
			return $object;
		}
		$me=DNSingletonStaticClass::$_instances[$key][$class]??null;
		if(null===$me){
			$object=DNSingletonStaticClass::[$class]??null;
			if(null!==$object){return $object;}
		
			$me=new $class();
			DNSingletonStaticClass::$_instances[$key][$class]=$me;
		}
		return $me;
		
	}
	public static function CleanCoroutineSingleton()
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return null;}
		$key="cid-$cid";
//fwrite(STDERR,var_export(array_keys(self::$_instances[$key]),true));
		DNSingletonStaticClass::$_instances[$key]=[];
	}
}
class SwooleAppBase extends DNMVCS
{
	public function init($options=[])
	{
		$options['default_controller_reuse']=false;
		DN::ImportSys('SuperGlobal');
		
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
		SERVER::Set('DOCUMENT_ROOT',rtrim($this->options['path'],'/'));
		SERVER::Set('SCRIPT_FILENAME',$this->options['path'].'index.php');
		
		//reuse;
		$route=DNRoute::G();
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
		
		return parent::run();
	}
	
	public function RunWithServer($server,$options)
	{
		DNSingletonStaticClass::$Replacer=[CoroutineSingleton::class,'G'];
		SwooleHttpd::G()->bindHttp(
			$server,
			function(){},
			function($request,$response)use($options){
				DN::G(DN::G()->init($options));
				
				DN::G()->options['request']=$request;
				DN::G()->options['response']=$response;
				DN::G()->run();
			},
			function($ex){
				DN::G()->onException($ex);
			},
			function(){
				DN::G()->options['request']=null;
				DN::G()->options['response']=null;
				DN::G()->cleanUp();
				CoroutineSingleton::CleanCoroutineSingleton();
			}
		);
		$server->start();
	}
}