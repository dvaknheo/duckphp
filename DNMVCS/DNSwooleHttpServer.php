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
			if($master && !isset(DNSingletonStaticClass::$_instances[$key][$class])){
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
		
		$master= DNSingletonStaticClass::$_instances[$class]??null;
		if(!$master){return false;}
		DNSingletonStaticClass::$_instances[$key][$class]=clone $master;
		return true;
	}
	
	public static function DeleteInstance($class)
	{
		unset(DNSingletonStaticClass::$_instances[$key][$class]);
	}
	public static function ReplaceDefaultSingletonHandler()
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
class SwooleContext
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
class DNSwooleHttpServer
{
	use DNSingleton;
	
	const DEFAULT_OPTIONS=[
			'swoole_server'=>null,
			'swoole_options'=>[],
			
			'host'=>'127.0.0.1',
			'port'=>0,
			
			'static_root'=>null,
			'php_root'=>null,
			'http_handler_file'=>null,
			'http_handler'=>null,
			'http_exception_handler'=>null,
			
			'websocket_handler'=>null,
			'websocket_exception_handler'=>null,
		];
	public $server=null;
	
	public $http_handler=null;
	public $http_exception_handler=null;
	
	public $websocket_handler=null;
	public $websocket_exception_handler=null;
	
	public $shutdown_function_array=[];
	
	protected $static_root=null;
	protected $php_root=null;

	public static function Server()
	{
		return self::G()->server;
	}
	public static function Request()
	{
		return SwooleContext::G()->request;
	}
	public static function Response()
	{
		return SwooleContext::G()->response;
	}
	public static function Context()
	{
		return SwooleContext::G();
	}
	public function set_exception_handler(callable $exception_handler)
	{
		$this->http_exception_handler=$exception_handler;
	}
	public function register_shutdown_function(callable $callback,...$args)
	{
		$this->shutdown_function_array[]=func_get_args();
	}
	public function header(string $string, bool $replace = true , int $http_response_code =0)
	{
		list($key,$value)=explode(':',$string);
		SwooleContext::G()->response->header($key, $value);
		if($http_response_code){
			SwooleContext::G()->response->status($http_status_code);
		}
	}
	public function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		return SwooleContext::G()->response->cookie($key,$value,$expire,$path,$domain,$secure,$httponly );
	}
	
	public function onHttpRun($request,$response)
	{
		SwooleContext::Init($request,$response);
		CoroutineSingleton::CloneInstance(SuperGlobal\SERVER::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\GET::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\POST::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\REQUEST::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\COOKIE::class);
		
		//SuperGlobal\SERVER::G();
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		
		if($this->http_handler){
			$this->runHttpHandler();
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
	
	protected function runHttpHandler()
	{
		if(!$this->http_handler){return;}
		($this->http_handler)();
	}
	public function onHttpException($ex)
	{
		if( !($ex instanceof \Swoole\ExitException) ){
			if($this->http_exception_handler){
				($this->http_exception_handler)($ex);
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
		SwooleContext::CleanUp();
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
		$this->onHttpClean();
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
	///////////
	public function checkInclude($file)
	{
		$a=get_included_files();
		return in_array($a,realpath($file))?true:false;
	}
	/////////////////////////
	public function init($options=[])
	{
		if(class_exists('\DNMVCS\SuperGlobal\SERVER' ,false)){
			require_once(__DIR__.'/SuperGlobal.php');
		}
		if(class_exists('\DNMVCS\SwooleSuperGlobal\SERVER' ,false)){
			require_once(__DIR__.'/SwooleSuperGlobal.php');
		}
		
		$this->options=array_merge(self::DEFAULT_OPTIONS,$options);
		
		$this->http_handler=$this->options['http_handler'];
		$this->http_exception_handler=$this->options['http_exception_handler'];
		
		$this->server=$this->options[''];
	
		if(!$this->server){
			$this->server=new \swoole_http_server($this->options['host'], $options['port']);
		}
		if($this->options['swoole_server_options']){
			$this->server->set($this->options['swoole_server_options']);
		}
		
		$this->options['server']=$this->server->setting;
		$this->server->on('request',[$this,'onRequest']);
		if($this->server->setting['enable_static_handler']??false){
			$this->static_root=$this->server->setting['document_root'];
		}else{
			$this->static_root=$this->options['static_root'];
		}
		
		if(is_callable('\Swoole\Runtime::enableCoroutine')){
			\Swoole\Runtime::enableCoroutine();
		}
		CoroutineSingleton::ReplaceDefaultSingletonHandler();
		
		/*
		$this->webSocketRunner=$this->options['websocket_runner'];
		if($this->webSocketRunner){
			$this->server->on('message',[$this,'onMessage']);
		}
		//*/
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
			$server_options['http_exception_handler']=[DNMVCS::G(),'onException'];
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
		//CoroutineSingleton::CloneInstance(SuperGlobal\SERVER::class)->init($request);
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