<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;
use \DNMVCS\SuperGlobal\SuperGlobalBase;
use \DNMVCS\SuperGlobal;

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
	public static function CloneInstance($class)
	{
		$cid = \Swoole\Coroutine::getuid();
		$key="cid=$cid";
		DNSingletonStaticClass::$_instances[$key]=DNSingletonStaticClass::$_instances[$key]??[];
		DNSingletonStaticClass::$_instances[$key][$class]=clone DNSingletonStaticClass::$_instances[$class];
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
class SwooleHttp
{
	use DNSingleton;
	public $request=null;
	public $response=null;
	public static function Request()
	{
		return self::G()->request;
	}
	
	public static function Response()
	{
		return self::G()->response;
	}
	public static function Init($request,$response)
	{
		return self::G()->_Init($request,$response);
	}
	public static function CleanUp()
	{
		return self::G()->_CleanUp();
	}
	public function _Init($request,$response)
	{
		$this->request=$request;
		$this->response=$response;
	}
	public function _CleanUp()
	{
		$this->request=null;
		$this->response=null;
	}
}

class SwooleAppBase
{
	use DNSingleton;

	protected $inited_routehooks=[];
	protected $replacedClass=[];
	public function after_init($options=[])
	{
		CoroutineSingleton::ReplaceDefaultSingletonHandel();
		$this->inited_routehooks=DNRoute::G()->routeHooks;
		
		$this->replacedClass[DNView::class]=get_class(DNView::G());
		$this->replacedClass[DNRoute::class]=get_class(DNRoute::G());
		
		//CoroutineSingleton::DeleteInstance(DNView::class);
		DNSingletonStaticClass::DeleteInstance(DNRoute::class);
	}
	public function before_run()
	{
		$request=SwooleHttp::Request();
		
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		
		$path=DN::G()->options['path'];
		SuperGlobal\SERVER::Set('DOCUMENT_ROOT',rtrim($path,'/'));
		SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$path.'index.php');
		
		CoroutineSingleton::CloneInstance(DNView::class);
		//CoroutineSingleton::CloneInstance(DNRoute::class);
		
		DN::G()->initRoute(DNRoute::G(RouteWithSuperGlobal::G()));
		DNRoute::G()->routeHooks=$this->inited_routehooks;
		
	}
	public static function OnRequest($request,$response)
	{
		$isInHttpException=false;
		SwooleHttp::Init($request,$response);

		ob_start(function($str) use($response){
			if(''===$str){return;}
			$response->write($str);
		});
		try{
			SwooleAppBase::G()->before_run();
			DN::G()->run();
		}catch(\Throwable $ex){
			$isInHttpException=true;
			if( !($ex instanceof  \Swoole\ExitException) ){
			
				DN::G()->onException($ex);
			}
		}
		SwooleHttp::CleanUp();
		
		ob_end_flush();
		if(!$isInHttpException){ 
			$response->end();
		}
		CoroutineSingleton::CleanUp();
	}
	public static function RunWithServer($server,$options)
	{
	
		$options['default_controller_reuse']=false;
		
		//DNRoute::G(RouteWithSuperGlobal::G());
		DN::G(DN::G()->init($options));
		SwooleAppBase::G()->after_init($options);
		//Swoole\Runtime::enableCoroutine();
		
		$server->on('request',[static::class,'OnRequest']);
		
		$server->start();
	}
}