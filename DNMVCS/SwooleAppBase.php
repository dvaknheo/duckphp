<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;
use \DNMVCS\SuperGlobal\SuperGlobalBase;
use \DNMVCS\SuperGlobal;

class RouteWithSuperGlobal extends DNRoute
{
	public function init($options)
	{
		parent::init($options);
		$this->path_info=$this->_SERVER('PATH_INFO')??'';
		$this->request_method=$this->_SERVER('REQUEST_METHOD')??'';
		$this->path_info=ltrim($this->path_info,'/');
		return $this;
	}
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
	public $request=null;
	public $response=null;
	protected $inited_routehooks=[];
	
	public function init($options=[])
	{
		$options['default_controller_reuse']=false;
		parent::init($options);
		
		$this->inited_routehooks=DNRoute::G()->routeHooks;
		
		DNSingletonStaticClass::DeleteInstance(DNView::class);
		DNSingletonStaticClass::DeleteInstance(DNRoute::class);
		
		return $this;
	}
	public function run()
	{
		$request=$this->request;
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		
		SuperGlobal\SERVER::Set('DOCUMENT_ROOT',rtrim($this->options['path'],'/'));
		SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$this->options['path'].'index.php');
		
		$this->initView(DNView::G());
		$this->initRoute(DNRoute::G(RouteWithSuperGlobal::G()));
		
		DNRoute::G()->routeHooks=$this->inited_routehooks;
		
		
		
		return parent::run();
	}
	protected function initExceptionManager()
	{
	}
	public static function OnRequest($request,$response)
	{
		$isInHttpException=false;
		
		DN::G()->request=$request;
		DN::G()->response=$response;
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
		DN::G()->response=null;
		ob_end_flush();
		if(!$isInHttpException){ 
			$response->end();
		}
		CoroutineSingleton::CleanUp();
		//$response=null;
	}
	public static function RunWithServer($server,$options)
	{
		DN::G(DN::G()->init($options));
		//Swoole\Runtime::enableCoroutine();
		CoroutineSingleton::ReplaceDefaultSingletonHandel();
		$server->on('request',[static::class,'OnRequest']);
		
		$server->start();
	}
}