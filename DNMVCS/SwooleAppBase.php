<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;
use \DNMVCS\SuperGlobal\SuperGlobalBase;
use \DNMVCS\SuperGlobal;

class RouteWithSuperGlobal extends DNRoute
{
	public function _SERVER($key)
	{
		return  SuperGlobal\SERVER::Get($key);
	}
	public function _GET($key)
	{
		return  SuperGlobal\GET::Get($key);
	}
	public function _POST($key)
	{
		return  SuperGlobal\POST::Get($key);
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
class RouteRewriteHookWithSuperGlobal extends RouteRewriteHook
{
	use DNSingleton;
	protected $rewriteMap=[];
	protected function mergeHttpGet($get)
	{
		//$_GET=array_merge($get,$_GET??[]);
		$data=array_merge($get, SuperGlobal\GET::All());
		foreach($data as $k=>$v){
			SuperGlobal\GET::Set($k,$v);
		}
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
	public static function GetInstance($class,$object)
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){
			return DNSingletonStaticClass::GetInstance($class,$object);
		}
		
		$key="cid=$cid";
		DNSingletonStaticClass::$_instances[$key]=DNSingletonStaticClass::$_instances[$key]??[];
		
		if($object===null){
			$me=DNSingletonStaticClass::$_instances[$key][$class]??null;
			if($me!==null){return $me;}
			
			$me=DNSingletonStaticClass::$_instances[$class]??null;
			if($me!==null){return $me;}
			
			$me=new $class();
			DNSingletonStaticClass::$_instances[$key][$class]=$me;
			return $me;
		}else{
		
		
			$master=DNSingletonStaticClass::$_instances[$class]??null;
			if($master){
				throw new \ErrorException("CoroutineSingleton fail:: $class use CreateInstance instead");
			}
			DNSingletonStaticClass::$_instances[$key][$class]=$object;
			return $object;
		}
	}
	public static function CreateInstance($class,$object=null)
	{
		$cid = \Swoole\Coroutine::getuid();
		$key="cid=$cid";
		$me=$object??new $class();
		DNSingletonStaticClass::$_instances[$key]=DNSingletonStaticClass::$_instances[$key]??[];
		DNSingletonStaticClass::$_instances[$key][$class]=$me;
		return $me;
	}
	public static function DeleteInstance($class)
	{
		unset(DNSingletonStaticClass::$_instances[$key][$class]);
	}
	public static function ReplaceDefaultSingletonHandel()
	{
		DNSingletonStaticClass::$Replacer=[CoroutineSingleton::class,'GetInstance'];
	}
	public static function Dump()
	{
		$cid = \Swoole\Coroutine::getuid();
fwrite(STDERR,"====CoroutineSingletonList cid-{$cid}====".";\n");
		$t=DNSingletonStaticClass::$_instances;
		foreach($t as $class=>$v){
			if(!is_array($v)){
fwrite(STDERR,"+ ".$class.";\n");
			}else{
				foreach($v as $class2=>$vv){
fwrite(STDERR,"-- $class ~ ".$class2.";\n");
				}
			}
		}
	}
	public static function CleanUp()
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return;}
		$key="cid-$cid";
		DNSingletonStaticClass::$_instances[$key]=[];
	}
}
class SwooleAppBase extends DNMVCS
{
	protected $rewriteMap=[];
	protected $RouteMap=[];
	public function assignRewrite($key,$value=null)
	{
		RouteRewriteHook::G()->assignRewrite($key,$value);
	}
	protected $inited_routehook=false;
	public function assignRoute($key,$value=null)
	{
		
		RouteMapHook::G()->assignRoute($key,$value);
	}
	
	public function init($options=[])
	{
		$options['default_controller_reuse']=false;
		parent::init($options);
		
		DNSingletonStaticClass::DeleteInstance(DNRoute::class);
		DNSingletonStaticClass::DeleteInstance(RouteRewriteHookWithSuperGlobal::class);
		DNSingletonStaticClass::DeleteInstance(RouteRewriteHook::class);
		
		return $this;
	}
	public function run()
	{
		$request=$this->request;
		
		DNRoute::G(RouteWithSuperGlobal::G());
		//RouteRewriteHook::G(RouteRewriteHookWithSuperGlobal::G());
		
		$this->initRoute(DNRoute::G());
		$this->initView(DNView::G());
		
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		SuperGlobal\SERVER::Set('DOCUMENT_ROOT',rtrim($this->options['path'],'/'));
		SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$this->options['path'].'index.php');
		
		//reuse;
		$route=DNRoute::G();
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
//fwrite(STDERR,var_export($route,true).";\n");

		return $route->run();
	}
	protected function initExceptionManager()
	{
	}
	public static function OnRequest($request,$response)
	{
		$isInHttpException=false;
		ob_start(function($str) use($response){
			if(''===$str){return;}
			$response->write($str);
		});
		try{
			DN::G()->request=$request;
			DN::G()->run();
		}catch(\Throwable $ex){
			$isInHttpException=true;
			if($ex instanceof  \Swoole\ExitException){
			}else{
				DN::G()->onException($ex);
			}
		}
		DN::G()->request=null;
		ob_end_flush();
		if(!$isInHttpException){ 
			$response->end();
		}
		CoroutineSingleton::CleanUp();
		//$response=null;
	}
	public static $_options;
	public static function RunWithServer($server,$options)
	{
		DN::G(DN::G()->init($options));
		//Swoole\Runtime::enableCoroutine();
		CoroutineSingleton::ReplaceDefaultSingletonHandel();
		$server->on('request',[static::class,'OnRequest']);
		$server->start();
	}
}