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
	public $fd=-1;
	public $frame=null;
	
	public static function Request()
	{
		return self::G()->request;
	}
	
	public static function Response()
	{
		return self::G()->response;
	}

	public static function CleanUp()
	{
		return self::G()->_CleanUp();
	}
	public function initHttp($request,$response)
	{
		$this->request=$request;
		$this->response=$response;
	}

	public function initWebSocket($frame)
	{
		$this->frame=$frame;
		$this->fd=$frame->fd;
		
	}
	public function isWebSocketClosing()
	{
		return $this->frame->opcode == 0x08?true:false;
	}
	public function _CleanUp()
	{
		$this->request=null;
		$this->response=null;
		$this->fd=-1;
		$this->frame=null;
	}
}
class DNSwooleException extends \Exception
{
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		$class=get_called_class();
		throw new $class($message,$code);
	}
}

class DBConnectPoolProxy
{
	use DNSingleton;
	
	protected $db_create_handler;
	protected $db_close_handler;
	protected $db_queue_write;
	protected $db_queue_write_time;
	protected $db_queue_read;
	protected $db_queue_read_time;
	public $max_length=100;
	public $timeout=5;
	public function __construct()
	{
		$this->db_queue_write=new \SplQueue();
		$this->db_queue_write_time=new \SplQueue();
		$this->db_queue_read=new \SplQueue();
		$this->db_queue_read_time=new \SplQueue();
	}
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	{
		$this->db_create_handler=$db_create_handler;
		$this->db_close_handler=$db_close_handler;
	}
	public function getObject($queue,$queue_time,$db_config,$tag)
	{
		if($queue->isEmpty()){
			return ($this->db_create_handler)($db_config,$tag);
		}
		$db=$queue->shift();
		$time=$queue_time->shift();
		$now=time();
		$is_timeout =($now-$time)>$this->timeout?true:false;
		if($is_timeout){
			($this->db_close_handler)($db,$tag);
			return ($this->db_create_handler)($db_config,$tag);
		}
		return $db;
		
	}
	public function reuseObject($queue,$queue_time,$db)
	{
		if(count($queue)>=$this->max_length){
			($this->db_close_handler)($db,$tag);
			return;
		}
		$time=time();
		$queue->push($db);
		$queue_time->push($time);
	}
	public function onCreate($db_config,$tag)
	{
		if($tag==='write'){
			return $this->getObject($this->db_queue_write,$this->db_queue_write_time,$db_config,$tag);
		}else{
			return $this->getObject($this->db_queue_read,$this->db_queue_read_time,$db_config,$tag);
		}
	}
	public function onClose($db,$tag)
	{
		if($tag==='write'){
			return $this->reuseObject($this->db_queue_write,$this->db_queue_write_time,$db);
		}else{
			return $this->reuseObject($this->db_queue_read,$this->db_queue_read_time,$db);
		}
	}
}
trait DNSwooleHttpServer_Static
{
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
}
trait DNSwooleHttpServer_GlobalFunc
{
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
}
class DNSwooleHttpServer
{
	use DNSingleton;
	use DNSwooleHttpServer_Static;
	use DNSwooleHttpServer_GlobalFunc;
	
	const DEFAULT_OPTIONS=[
			'swoole_server'=>null,
			'swoole_options'=>[],
			
			'host'=>'127.0.0.1',
			'port'=>0,
			
			'http_handler_root'=>null,
			'http_handler_file'=>null,
			'http_handler'=>null,
			'http_exception_handler'=>null,
			
			'websocket_open_handler'=>null,
			'websocket_handler'=>null,
			'websocket_exception_handler'=>null,
			'websocket_close_handler'=>null,
		];
	public $server=null;
	
	public $http_handler=null;
	public $http_exception_handler=null;
	protected $static_root=null;  //TODO
	
	public $websocket_open_handler=null;
	public $websocket_handler=null;
	public $websocket_exception_handler=null;
	public $websocket_close_handler=null;
	
	public $shutdown_function_array=[];

	public function set_exception_handler(callable $exception_handler)
	{
		$this->http_exception_handler=$exception_handler;
	}
	public function register_shutdown_function(callable $callback,...$args)
	{
		$this->shutdown_function_array[]=func_get_args();
	}

	
	public function onHttpRun($request,$response)
	{
		SwooleContext::G()->initHttp($request,$response);
		CoroutineSingleton::CloneInstance(SuperGlobal\SERVER::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\GET::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\POST::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\REQUEST::class);
		CoroutineSingleton::CloneInstance(SuperGlobal\COOKIE::class);
		
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G())->init($request);
		SuperGlobal\GET::G(SwooleSuperGlobalGet::G())->init($request);
		SuperGlobal\POST::G(SwooleSuperGlobalPost::G())->init($request);
		SuperGlobal\REQUEST::G(SwooleSuperGlobalRequest::G())->init($request);
		SuperGlobal\COOKIE::G(SwooleSuperGlobalCookie::G())->init($request);
		//session ,event;
		
		if($this->http_handler){
			$this->runHttpHandler();
			return;
		}
		if($this->options['http_handler_file']){
			$path_info=SuperGlobal\SERVER::Get('REQUEST_URI');
			$file=$this->options['http_handler_file'];
			$document_root=dirname($file);
			$this->includeHttpFile($file,$document_root,$path_info);
			return;
		}
		if($this->options['http_handler_root']){
			//mime_content_type
			$http_handler_root=$this->options['http_handler_root'];
			$http_handler_root=rtrim($http_handler_root,'/').'/';
			
			$document_root=$this->static_root?:rtrim($http_handler_root,'/');
			
			$request_uri=SuperGlobal\SERVER::Get('REQUEST_URI');
			
			$path=parse_url($request_uri,PHP_URL_PATH);
			if(strpos($path,'/../')!==false || strpos($path,'/./')!==false){
				throw new DNSwooleException("404 Not Found",404);
			}
			
			$full_file=$document_root.$path;
			if($path==='/'){
				$this->includeHttpFile($document_root.'/index.php',$document_root,'');
				return;
			}
			if(is_file($full_file)){
				//mime_content_type
				$ext=pathinfo($full_file,PATHINFO_EXTENSION);
				if($ext==='php'){
					$file=$full_file;
					$path_info='';
					$this->includeHttpFile($file,$document_root,$path_info);
					return;
				}
				$mime=mime_content_type($fullfile);
				SwooleContext::G()->response->header('Content-Type',$mime);
				SwooleContext::G()->response->sendfile($full_file);
				return;
			}
			
			$max=20;
			$offset=0;
			for($i=0;$i<$max;$i++){
				$offset=strpos($path,'.php/',$offset);
				if(false===$offset){break;}
				$offset++;
				$file=substr($path,0,$offset).'.php';
				$path_info=substr($path,$offset+strlen('.php'));
				$file=$document_root.$file;
				if(is_file($file)){
					$this->includeHttpFile($file,$document_root,$path_info);
					return;
				}
			}
			throw new DNSwooleException("404 Not Found!",404);
			
		}
		$this->includeHttpFile($file,$document_root,$path_info);
	}
	protected function includeHttpFile($file,$document_root,$path_info)
	{
		SuperGlobal\SERVER::Set('PATH_INFO',$path_info);
		SuperGlobal\SERVER::Set('DOCUMENT_ROOT',$document_root);
		SuperGlobal\SERVER::Set('SCRIPT_FILENAME',$file);
		chdir(dirname($file));
		(function($file){include($file);})($file);
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
				echo "DNSwooleHttp Server Error: \n";
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
			if(''===$str){return;} // stop warnning;
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
	public function onOpen(swoole_websocket_server $server, swoole_http_request $request)
	{
		if(!$this->websocket_open_handler){ return; }
		SwooleContext::G()->initHttp(request,null);
		($this->websocket_open_handler)();
	}
	public function onMessage($server,$frame)
	{
		SwooleContext::G()->initWebSocket($frame);
		
		$fd=$frame->fd;
		ob_start(function($str)use($server,$fd){
			if(''===$str){return;}
			$server->push($fd,$str);
		});
		try{
			if($frame->opcode != 0x08  || !$this->websocket_close_handler) {
				($this->websocket_handler)();
			}else{
				($this->websocket_close_handler)();
			}
		}catch(\Throwable $ex){
			if( !($ex instanceof  \Swoole\ExitException) ){
				($this->websocket_exception_handler)($ex);
			}
		}
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
		require_once(__DIR__.'/SuperGlobal.php');
		require_once(__DIR__.'/SwooleSuperGlobal.php');
		
		$this->options=array_merge(self::DEFAULT_OPTIONS,$options);
		$options=$this->options;
		
		$this->http_handler=$options['http_handler'];
		$this->http_exception_handler=$options['http_exception_handler'];
		
		$this->server=$options['swoole_server'];
	
		if(!$this->server){
			if(!$options['port']){
				throw new DNSwooleException("No Port ,set the port");
			}
			if(!$options['websocket_handler']){
				$this->server=new \swoole_http_server($options['host'], $options['port']);
			}else{
				$this->server=new \swoole_websocket_server($options['host'], $options['port']);
			}
		}
		if($options['swoole_server_options']){
			$this->server->set($options['swoole_server_options']);
		}
		
		$this->options['swoole_server']=$this->server->setting;
		$this->server->on('request',[$this,'onRequest']);
		if($this->server->setting['enable_static_handler']??false){
			$this->static_root=$this->server->setting['document_root'];
		}
		
		$this->websocket_open_handler=$options['websocket_open_handler'];
		$this->websocket_handler=$options['websocket_handler'];
		$this->websocket_exception_handler=$options['websocket_exception_handler'];
		$this->websocket_close_handler=$options['websocket_close_handler'];
		
		if($this->websocket_handler){
			$this->server->set(['open_websocket_close_frame'=>true]);
			$this->server->on('mesage',[$this,'onMessage']);
			$this->server->on('open',[$this,'onOpen']);
		}
		
		if(is_callable('\Swoole\Runtime::enableCoroutine')){
			\Swoole\Runtime::enableCoroutine();
		}
		
		CoroutineSingleton::ReplaceDefaultSingletonHandler();
		
		return $this;
	}
	public function run()
	{
		define('DN_SWOOLE_SERVER_RUNNING',true);
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		$t=$this->server->start();
		fwrite(STDOUT,get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
	}
	public function afteAppInit()
	{
		
		$dbm=DNDBManager::G();
		$options=DNMVCS::G()->options;
		$db_reuse_size=$options['db_reuse_size']??0;
		if($db_reuse_size){
			$db_reuse_timeout=$options['db_reuse_timeout']??5;
			
			DBConnectPoolProxy::G()->max_length=$$db_reuse_size;
			DBConnectPoolProxy::G()->timeout=$db_reuse_timeout;
			
			DBConnectPoolProxy::G()->setDBHandler($dbm->db_create_handler,$dbm->db_close_handler);
			DNDBManager::G()->setDBHandler([DBConnectPoolProxy::G(),'onCreate'],[DBConnectPoolProxy::G(),'onClose']);
		}
		DNMVCS::G()->setBeforeRunHandler(function(){
			CoroutineSingleton::CloneInstance(DNView::class);
			CoroutineSingleton::CloneInstance(DNRoute::class);
		});
		
		require_once(__DIR__.'/SwooleSuperGlobal.php');
		SuperGlobal\SERVER::G(SwooleSuperGlobalServer::G());
	}
	public static function RunWithServer($server_options,$dn_options=[])
	{
		if($dn_options){
			$dn_options['ext']['use_super_global']=true;
			DNMVCS::G()->init($dn_options);
			self::G()->afteAppInit();
			
			$server_options['http_handler']=[DNMVCS::G(),'run'];
			$server_options['http_exception_handler']=[DNMVCS::G(),'onException'];
		}
		self::G()->init($server_options)->run();
	}
}
