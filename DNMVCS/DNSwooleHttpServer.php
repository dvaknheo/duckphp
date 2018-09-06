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
		
		//OK,other as document_root,php_root
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
class SwooleHttpContext
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
interface IWebSocketRunner
{
	public function onWebSoketRun($request,$response);
	public function onWebSoketException($ex);
	public function onWebSoketCleanUp();
}
class DNSwooleHttpServer
{
	use DNSingleton;
	
	public $server=null;
	public $httpRunner=null;
	public $webSocketRunner=null;

	public $exception_handler=null;
	public $shutdown_function_array=[];
	
	public static function Server()
	{
		return self::G()->server;
	}
	public static function Request()
	{
		return SwooleHttpContext::G()->request;
	}
	public static function Response()
	{
		return SwooleHttpContext::G()->response;
	}
	public static function Context()
	{
		return SwooleHttpContext::G();
	}
	public function set_exception_handler(callable $exception_handler)
	{
		$this->exception_handler=$exception_handler;
	}
	public function register_shutdown_function(callable $callback,...$args)
	{
		$this->shutdownFunctions[]=func_get_args();
	}
	
	public function onHttpRun($request,$response)
	{
		SwooleHttpContext::Init($request,$response);
		
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		// not env msession
		$this->runHttpHandeler();
	}
	protected function runHttpHandeler()
	{
		if(!$this->http_handler){return;}
		($this->http_handler)();
		
	}
	public function onHttpException($ex)
	{
		if( !($ex instanceof \Swoole\ExitException) ){
			if($this->exception_handler){
				($this->exception_handler)($ex);
			}else{
				echo "DNSwooleServer Error ";
				echo $ex;
			}
		}else{
			foreach($shutdown_function_array as $v){
				$func=array_shift($v);
				$func($v);
			}
		}
	}
	public function onHttpClean()
	{
		SwooleHttpContext::CleanUp();
		CoroutineSingleton::CleanUp();
	}
	public function onRequest($request,$response)
	{
		$InitObLevel=ob_get_level();
		ob_start(function($str) use($response){
			if(''===$str){return;}
			$response->write($str);
		});
		try{
			$this->onHttpRun($request,$response);
		}catch(\Throwable $ex){
			$this->onHttpException($ex);
		}
		for($i=ob_get_level();$i>$InitObLevel;$i--){
			ob_end_flush();
		}
		$response->end();
		//response 被使用到，而且出错就要手动 end  还是 OB 层级问题？
		//onHttpRun(null,null) 则不需要用
	}
	public function onMessage($server,$frame)
	{
		$fd=$frame->fd;
		ob_start(function($str)use($server,$fd){
			if(''===$str){return;}
			$server->push($fd,$str);
		});
		try{
			$this->onWebSoketRun($server,$frame);
		}catch(\Throwable $ex){
			if( !($ex instanceof  \Swoole\ExitException) ){
				$this->onWebSoketException($ex);
			}
		}
		$this->onWebSoketCleanUp();
		for($i=ob_get_level();$i>$InitObLevel;$i--){
			ob_end_flush();
		}
	}
	
	public function init($server_or_options,$options=[])
	{
		$server=$server_or_options;
		if(!is_object($server)){
			$server=new \swoole_http_server($server_or_options['Host'], $server_or_options['Port']);
			unset($server_or_options['Host']);
			unset($server_or_options['Port']);
			$server->set($server_or_options);
		}
		
		$this->server=$server;
		$this->server->on('request',[$this,'onRequest']);
		
		$this->http_handler=$options['http_handler']??null;
		$this->exception_handler=$options['exception_handler']??null;
		
		$this->webSocketRunner=$options['websocket_runner']??null;
		if($this->webSocketRunner){
			$this->server->on('message',[$this,'onMessage']);
		}
		
		if(is_callable('\Swoole\Runtime::enableCoroutine')){
			\Swoole\Runtime::enableCoroutine();
		}
		CoroutineSingleton::ReplaceDefaultSingletonHandel();
		
		return $this;
	}
	public function run()
	{
		define('DN_SWOOLE_SERVER_RUNNING',true);
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		$this->server->start();
	}
	
	public static function RunWithServer($swoole_erver_or_options,$dn_options=[],$server_options=[])
	{
		DNMVCS::G()->init($dn_options);
		SwooleMainAppHook::G()->installHook(DNMVCS::G());
		
		$server_options=[];
		$server_options['http_handler']=[DNMVCS::G(),'run'];
		$server_options['exception_handler']=[DNMVCS::G(),'onException'];
		
		self::G()->init($swoole_erver_or_options,$server_options)->run();
	}
}
class SwooleBasicServerDefaultHandel
{
	use DNSingleton;
	const DEFAULT_OPTIONS=[
			'doucment_root'=>null,
			'php_root'=>null,
			'php_file'=>null,
		];
	
	protected $doucment_root;
	protected $php_root;
	protected $php_file;
	public function init($options)
	{
		$options=array_merge(self::DEFAULT_OPTIONS,$options);
		
		$this->doucment_root=$options['doucment_root'];
		$this->php_root=$options['php_root'];
		$this->php_file=$options['php_file'];

		return $this;
	}
	
}

class SwooleMainAppHook
{
	use DNSingleton;
	
	public function installHook()
	{
		DNRoute::G()->onServerArray=[SuperGlobal\SERVER::class,'Get'];
		DNMVCS::G()->useRouteAdvance();
		DNMVCS::G()->addAppHook([$this,'hook']);
		return $this;
	}
	public function hook()
	{
		CoroutineSingleton::CloneInstance(DNView::class);
		CoroutineSingleton::CloneInstance(DNRoute::class);
		
		$path=DN::G()->options['path'];
		SuperGlobal\SERVER::Set('DOCUMENT_ROOT',rtrim($path,'/www/'));
		SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$path.'index.php');
		
		
		$route=DNRoute::G();
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
	}
}
/*
SwooleServer::G()->init($server,$http_options)->bindApp($option)->run();
//*/