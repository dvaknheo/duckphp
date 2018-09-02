<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;
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
		foreach($request->header as $k=>$v){
			$k='HTTP_'.str_replace('-','_',strtoupper($k));
			$this->data[$k]=$v;
		}
		foreach($request->server as $k=>$v){
			$this->data[strtoupper($k)]=$v;
		}
		
	}
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
class SwooleReuseRouteHook
{
	public static function hook($route){
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
	}
}

class SwooleAppBase extends DNMVCS
{
	public static function CoroutineSingleton($object=null,$class)
	{

		$cid = \Swoole\Coroutine::getuid();
		if($cid<0){return null;}
		if($cid){
			$key="cid-$cid";
			self::$_instances[$key]=self::$_instances[$key]??[];
			
			if($object){
				self::$_instances[$key][$class]=$object;
				return $object;
			}
			$me=self::$_instances[$key][$class]??null;
			if(null===$me){
				$me=new $class();
				self::$_instances[$key][$class]=$me;
			}
			return $me;
		}else{
			if($object){
				self::$_instances[$class]=$object;
				return $object;
			}
			$me=self::$_instances[$class]??null;
			if(null===$me){
				$me=new $class();
				self::$_instances[$class]=$me;
			}
		}
	}
	public static function CleanCoroutineSingleton()
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return null;}
		$key="cid-$cid";
//fwrite(STDERR,var_export(array_keys(self::$_instances[$key]),true));
		self::$_instances[$key]=[];
	}
	public function init($options=[])
	{
		$options['default_controller_reuse']=false;
		DN::ImportSys('SuperGlobal');
		
		DNRoute::G(RouteWithSuperGlobal::G());
		RouteRewriteHook::G(RouteRewriteHookWithSuperGlobal::G());
		
		parent::init($options);
		
		$this->addRouteHook([SwooleReuseRouteHook::class,'hook']);
		
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
		
		return parent::run();
	}
	
	public function RunWithServer($server,$options)
	{
		SwooleHttpd::G()->bindHttp(
			$server,
			function(){
				global $_DNSingleton_Custumer_G;
				$_DNSingleton_Custumer_G=[static::class,'CoroutineSingleton'];
			},
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
				self::CleanCoroutineSingleton();
			}
		);
		$server->start();
	}
}