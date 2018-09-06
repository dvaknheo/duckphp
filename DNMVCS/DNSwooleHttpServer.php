<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;
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
	
	const DEFAULT_OPTIONS=[
			'server'=>null,
			'host'=>null,
			'port'=>null,
			
			'static_root'=>null,
			'php_root'=>null,
			'http_handler_file'=>null,
			
			'http_handler'=>null,
			'exception_handler'=>null,
			
			'websocket_runner'=>null,
		];
	public $server=null;
	public $webSocketRunner=null;
	
	public $http_handler=null;
	public $exception_handler=null;
	public $shutdown_function_array=[];
	
	protected $static_root=null;
	protected $php_root=null;

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
		$this->shutdown_function_array[]=func_get_args();
	}
	public function header(string $string, bool $replace = true , int $http_response_code =0)
	{
		list($key,$value)=explode(':',$string);
		SwooleHttpContext::G()->response->header($key, $value);
		if($http_response_code){
			SwooleHttpContext::G()->response->status($http_status_code);
		}
	}
	public function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		return SwooleHttpContext::G()->response->cookie($key,$value,$expire,$path,$domain,$secure,$httponly );
	}
	
	public function onHttpRun($request,$response)
	{
		SwooleHttpContext::Init($request,$response);
		
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		
		if($this->http_handler){
			$this->runHttpHandeler();
			return;
		}
		if($this->options['http_handler_file']){
		
			$http_handler_file=$this->options['http_handler_file'];
			SuperGlobal\SERVER::Set("SCRIPT_FILENAME",$http_handler_file);
			$request_uri=SuperGlobal\SERVER::Get("REQUEST_URI");
			SuperGlobal\SERVER::Set("PATH_INFO",$request_uri);
			SuperGlobal\SERVER::Set("DOCUMENT_ROOT",dirname($http_handler_file));
			chdir(dirname($http_handler_file));
			(function($file){include($file);})($http_handler_file);
			return;
		}
		if($this->options['php_root']){
			$php_root=$this->options['php_root'];
			$php_root=rtrim($php_root,'/').'/';
			$request_uri=SuperGlobal\SERVER::Get("REQUEST_URI");
			$pos=strpos($request_uri,'.php');
			if($pos!==false){
				$script_name=substr($request_uri,0,$pos);
				$path_info=substr($request_uri,$pos+strlen('.php'));
				$file=$php_root.$script_name;
				if(strpos($file,'/../')!==false || strpos($file,'/./')!==false){
					echo "bad file";
					return;
				}
				if(!is_file($file)){
					echo "404";
					return;
				}
				SuperGlobal\SERVER::Set("SCRIPT_NAME",$SCRIPT_NAME);
				SuperGlobal\SERVER::Set("PATH_INFO",$path_info);
				SuperGlobal\SERVER::Set("SCRIPT_FILENAME",$file);
				
				$document_root=$this->static_root?:rtrim($php_root,'/');
				SuperGlobal\SERVER::Set("DOCUMENT_ROOT",$document_root);
				chdir(dirname($file));
				(function($file){include($file);})($file);
			}else{
				SuperGlobal\SERVER::Set("SCRIPT_NAME",'/index.php');
				SuperGlobal\SERVER::Set("PATH_INFO",$request_uri);
				$file=$php_root.'index.php';
				SuperGlobal\SERVER::Set("SCRIPT_FILENAME",$file);
				$document_root=dirname($file);
				SuperGlobal\SERVER::Set("DOCUMENT_ROOT",$document_root);
				chdir(dirname($file));
				(function($file){include($file);})($file);
			}
		}
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
			foreach($this->shutdown_function_array as $v){
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
			if(''===$str){return;} // 防止ongoing数据报 warnning;
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
	
	public function init($options=[])
	{
		require_once(__DIR__.'/SuperGlobal.php');
		require_once(__DIR__.'/SwooleSuperGlobal.php');

		$this->options=array_merge(self::DEFAULT_OPTIONS,$options);
		
		$this->http_handler=$this->options['http_handler'];
		$this->exception_handler=$this->options['exception_handler'];
		
		$server=$this->options['server'];
		if(!is_object($server)){
			$server=new \swoole_http_server($server_or_options['Host'], $server_or_options['Port']);
			unset($server_or_options['Host']);
			unset($server_or_options['Port']);
			$server->set($server_or_options);
		}
		
		$this->server=$server;
		$this->options['server']=$server->setting;
		$this->server->on('request',[$this,'onRequest']);
		if($server->setting['enable_static_handler']??false){
			$this->static_root=$server->setting['document_root'];
		}else{
			$this->static_root=$this->options['static_root'];
		}
		
		if(is_callable('\Swoole\Runtime::enableCoroutine')){
			\Swoole\Runtime::enableCoroutine();
		}
		CoroutineSingleton::ReplaceDefaultSingletonHandel();
		
		$this->webSocketRunner=$this->options['websocket_runner'];
		if($this->webSocketRunner){
			$this->server->on('message',[$this,'onMessage']);
		}
		return $this;
	}
	public function run()
	{
		define('DN_SWOOLE_SERVER_RUNNING',true);
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		$t=$this->server->start();
		fwrite(STDOUT,get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
	}
	
	public static function RunWithServer($server_options,$dn_options=[])
	{
		if($dn_options){
			DNMVCS::ImportSys('SuperGlobal');
			DNMVCS::G()->init($dn_options);
			SwooleMainAppHook::G()->installHook(DNMVCS::G());
			$server_options['http_handler']=[DNMVCS::G(),'run'];
			$server_options['exception_handler']=[DNMVCS::G(),'onException'];
		}
		self::G()->init($server_options)->run();
	}
}

class SwooleMainAppHook
{
	use DNSingleton;
	
	public function installHook($dn)
	{
		
		if($dn->options['rewrite_list']|| $dn->options['route_list']){
			$dn->useRouteAdvance();
		}
		$dn->addAppHook([$this,'beforeMainAppRun']);
		return $this;
	}
	public function beforeMainAppRun()
	{
		$path=DNMVCS::G()->options['path'];
		SuperGlobal\SERVER::Set('DOCUMENT_ROOT',$path.'/www');
		SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$path.'/www/index.php');
		
		CoroutineSingleton::CloneInstance(DNView::class);
		CoroutineSingleton::CloneInstance(DNRoute::class);
		
		$route=DNRoute::G();
		$route->path_info=$route->_SERVER('PATH_INFO')??'';
		$route->request_method=$route->_SERVER('REQUEST_METHOD')??'';
		$route->path_info=ltrim($route->path_info,'/');
	}
}
/*
SwooleServer::G()->init($server,$http_options)->bindApp($option)->run();
//*/