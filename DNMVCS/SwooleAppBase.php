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
	public function afterInit($options=[])
	{
		CoroutineSingleton::ReplaceDefaultSingletonHandel();
		$this->inited_routehooks=DNRoute::G()->routeHooks;
		
		$this->replacedClass[DNView::class]=get_class(DNView::G());
		$this->replacedClass[DNRoute::class]=get_class(DNRoute::G());
		
		DNRoute::G()->onServerArray=[SuperGlobal\SERVER::class,'Get'];
	}
	public function beforeRun()
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
		CoroutineSingleton::CloneInstance(DNRoute::class);
		
		$route=DNRoute::G();
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
	}
	public static function onRequest($request,$response)
	{
		SwooleHttp::Init($request,$response);
		$InitObLevel=ob_get_level();
		ob_start(function($str) use($response){
			if(''===$str){return;}
			$response->write($str);
		});
		try{
			$this->beforeRun();
			DN::G()->run();
		}catch(\Throwable $ex){
			if( !($ex instanceof  \Swoole\ExitException) ){
				DN::G()->onException($ex);
			}
		}
		for($i=ob_get_level();$i>$InitObLevel;$i--){
			ob_end_flush();
		}
		SwooleHttp::CleanUp();
		CoroutineSingleton::CleanUp();
	}
	public function bindWithServer($server_or_options,$options)
	{
		$server=$server_or_options;
		if(!is_object($server_or_options)){
			$server=new swoole_http_server($server_or_options['Host'], $server_or_options['Port']);
			unset($server_or_options['Host']);
			unset($server_or_options['Port']);
			$server->set($server_or_options);
		}
		
		Swoole\Runtime::enableCoroutine();
		DN::G()->init($options);
		$this->afterInit($options);
		$server->on('request',[$this,'onRequest']);
		
	}
	public static function RunWithServer($server_or_options,$options)
	{
		$server=$server_or_options;
		if(!is_object($server_or_options)){
			$server=new swoole_http_server($server_or_options['Host'], $server_or_options['Port']);
			unset($server_or_options['Host']);
			unset($server_or_options['Port']);
			$server->set($server_or_options);
		}
		
		self::G()->bindWithServer($server_or_options,$options);
		$server->start();
	}
}