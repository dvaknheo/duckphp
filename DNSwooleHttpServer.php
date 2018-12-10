<?php
namespace DNMVCS;

class CoroutineSingleton
{
	protected static $_instances=[];
	
	public static function GetInstance($class,$object)
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){
			if($object){
				self::$_instances[$class]=$object;
				return $object;
			}
			$me=self::$_instances[$class]??null;
			if(null===$me){
				$me=new $class();
				self::$_instances[$class]=$me;
			}
			return $me;
		}
		
		$key="cid-$cid";
		self::$_instances[$key]=self::$_instances[$key]??[];
		
		if($object===null){
			$me=self::$_instances[$key][$class]??null;
			if($me!==null){return $me;}
			
			$me=self::$_instances[$class]??null;
			if($me!==null){return $me;}
			
			$me=new $class();
			self::$_instances[$key][$class]=$me;
			return $me;
		}else{
			$master=self::$_instances[$class]??null;
			if($master && !isset(self::$_instances[$key][$class])){
				throw new \ErrorException("CoroutineSingleton fail:: $class use CreateInstance instead");
			}
			self::$_instances[$key][$class]=$object;
			return $object;
		}
	}
	public static function CreateInstance($class,$object=null)
	{
		$cid = \Swoole\Coroutine::getuid();
		$key="cid-$cid";
		$me=$object??new $class();
		self::$_instances[$key]=self::$_instances[$key]??[];
		self::$_instances[$key][$class]=$me;
		return $me;
	}
	public static function CloneInstance($class)
	{
		$cid = \Swoole\Coroutine::getuid();
		$key="cid-$cid";
		self::$_instances[$key]=self::$_instances[$key]??[];
		
		$master= self::$_instances[$class]??null;
		if(!$master){return false;}
		self::$_instances[$key][$class]=clone $master;
		return true;
	}
	

	public static function ReplaceDefaultSingletonHandler()
	{
		define('DNMVCS_DNSINGLETON_REPALACER' ,self::class . '::'.'GetInstance');
	}
	
	public static function DumpString()
	{
		$cid = \Swoole\Coroutine::getuid();
		$ret="==== CoroutineSingleton List Current cid [{$cid}] ==== ;\n";
		foreach(static::$_instances as $class=>$v){
			if(!is_array($v)){
					$desc=($v?get_class($v):'null');
				if($class===$desc){
					$ret.=$class."\n";
				}else{
					$ret.=$class." ( ".$desc." )\n";
				}
			}else{
				foreach($v as $cid_class=>$cid_object){
					$desc=($cid_object?get_class($cid_object):'null');
					if($cid_class===$desc){
						$ret.="[$class]: ".$cid_class."\n";
					}else{
						$ret.="[$class]: ".$cid_class." ( ".$desc." )\n";
					}
				}
			}
		}
		return "{{$ret}}";
	}
	public static function Dump()
	{
		fwrite(STDERR,static::DumpString());
	}
	public static function CleanUp()
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return;}
		$key="cid-$cid";
		unset(self::$_instances[$key]);
	}
}
class SwooleContext
{
	use DNSingleton;
	public $request=null;
	public $response=null;
	public $fd=-1;
	public $frame=null;
	
	public $shutdown_function_array=[];
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
	public function cleanUp()
	{
		$this->request=null;
		$this->response=null;
		$this->fd=-1;
		$this->frame=null;
	}
	public function onShutdown()
	{
		$funcs=array_reverse($this->shutdown_function_array);
		foreach($funcs as $v){
			$func=array_shift($v);
			$func($v);
		}
		$this->shutdown_function_array=[];
	}
}
class DNSwooleException extends \Exception
{
	use DNThrowQuickly;
}

class DBConnectPoolProxy
{
	use DNSingleton;
	
	public $tag_write='0';
	public $tag_read='1';
	
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
	public function init($max_length=10,$timeout=5)
	{
		$this->max_length=$max_length;
		$this->timeout=$timeout;
		return $this;
	}
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	{
		$this->db_create_handler=$db_create_handler;
		$this->db_close_handler=$db_close_handler;
	}
	protected function getObject($queue,$queue_time,$db_config,$tag)
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
	protected function reuseObject($queue,$queue_time,$db)
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
		if($tag!=$this->tag_write){
			return $this->getObject($this->db_queue_write,$this->db_queue_write_time,$db_config,$tag);
		}else{
			return $this->getObject($this->db_queue_read,$this->db_queue_read_time,$db_config,$tag);
		}
	}
	public function onClose($db,$tag)
	{
		if($tag!=$this->tag_write){
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
		return static::G()->server;
	}
	public static function Request()
	{
		return SwooleContext::G()->request;
	}
	public static function Response()
	{
		return SwooleContext::G()->response;
	}
	public static function Frame()
	{
		return SwooleContext::G()->frame;
	}
	public static function FD()
	{
		return SwooleContext::G()->fd;
	}
	public static function IsClosing()
	{
		return SwooleContext::G()->frame->opcode == 0x08?true:false;
	}
	public static function CloneInstance($class)
	{
		return CoroutineSingleton::CloneInstance($class);
	}
}
trait DNSwooleHttpServer_GlobalFunc
{
	public $http_exception_handler=null;
	public static function header(string $string, bool $replace = true , int $http_status_code =0)
	{
		if($http_status_code){
			SwooleContext::G()->response->status($http_status_code);
		}
		if(strpos($string,':')===false){return;} // 404,500 so on
		list($key,$value)=explode(':',$string);
		SwooleContext::G()->response->header($key, $value);
		
	}
	public static function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		return SwooleContext::G()->response->cookie($key,$value,$expire,$path,$domain,$secure,$httponly );
	}
	public static function set_exception_handler(callable $exception_handler)
	{
		static::G()->http_exception_handler=$exception_handler;
	}
	public static function register_shutdown_function(callable $callback,...$args)
	{
		SwooleContext::G()->shutdown_function_array[]=func_get_args();
	}
}
trait DNSwooleHttpServer_SimpleHttpd
{
	
	protected function onHttpRun($request,$response){throw new DNSwooleException("Impelement Me");}
	protected function onHttpException($ex){throw new DNSwooleException("Impelement Me");}
	protected function onHttpClean(){throw new DNSwooleException("Impelement Me");}
	
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
		
		SwooleContext::G()->onShutdown();
		
		for($i=ob_get_level();$i>$InitObLevel;$i--){
			ob_end_flush();
		}
		$this->onHttpClean();
		
		$response->end();
		//response 被使用到，而且出错就要手动 end  还是 OB 层级问题？
		//onHttpRun(null,null) 则不需要用
	}
}
trait DNSwooleHttpServer_WebSocket
{
	public $websocket_open_handler=null;
	public $websocket_handler=null;
	public $websocket_exception_handler=null;
	public $websocket_close_handler=null;
	
	public function onOpen(swoole_websocket_server $server, swoole_http_request $request)
	{
		SwooleContext::G()->initHttp($request,null);
		if(!$this->websocket_open_handler){ return; }
		($this->websocket_open_handler)();
	}
	public function onMessage($server,$frame)
	{
		$InitObLevel=ob_get_level();
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
}
class DNSwooleHttpServer
{
	use DNSingleton;
	use DNSwooleHttpServer_Static;
	use DNSwooleHttpServer_SimpleHttpd;
	use DNSwooleHttpServer_WebSocket;
	use DNSwooleHttpServer_GlobalFunc;
	
	const DEFAULT_OPTIONS=[
			'swoole_server'=>null,
			'swoole_server_options'=>[],
			
			'host'=>'127.0.0.1',
			'port'=>0,
			
			'http_handler_basepath'=>'',
			'http_handler_root'=>null,
			'http_handler_file'=>null,
			'http_handler'=>null,
			'http_exception_handler'=>null,
			
			'websocket_open_handler'=>null,
			'websocket_handler'=>null,
			'websocket_exception_handler'=>null,
			'websocket_close_handler'=>null,
		];
	const DEFAULT_DN_OPTIONS=[
			'not_empty'=>true,
			'db_reuse_size'=>0,
			'db_reuse_timeout'=>5,
		];
	public $server=null;
	
	public $http_handler=null;
	public $http_exception_handler=null;
	
	protected $static_root=null;  //TODO
	protected function onHttpRun($request,$response)
	{
		SwooleContext::G()->initHttp($request,$response);
		
		CoroutineSingleton::CloneInstance(SuperGlobal::class);
		SuperGlobal::G()->run();
		
		if($this->http_handler){
			$this->runHttpHandler();
			return;
		}
		if($this->options['http_handler_file']){
			$path_info=SuperGlobal::SERVER('REQUEST_URI');
			$file=$this->options['http_handler_basepath'].$this->options['http_handler_file'];
			$document_root=dirname($file);
			$this->includeHttpFile($file,$document_root,$path_info);
			return;
		}
		if($this->options['http_handler_root']){
			$http_handler_root=$this->options['http_handler_basepath'].$this->options['http_handler_root'];
			$http_handler_root=rtrim($http_handler_root,'/').'/';
			
			$document_root=$this->static_root?:rtrim($http_handler_root,'/');
			
			$request_uri=SuperGlobal::GET('REQUEST_URI');
			
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
		SuperGlobal::SetSERVER('PATH_INFO',$path_info);
		SuperGlobal::SetSERVER('DOCUMENT_ROOT',$document_root);
		SuperGlobal::SetSERVER('SCRIPT_FILENAME',$file);
		chdir(dirname($file));
		(function($file){include($file);})($file);
	}
	
	protected function runHttpHandler()
	{
		if(!$this->http_handler){return;}
		($this->http_handler)();
	}
	protected function onHttpException($ex)
	{
		if($ex instanceof \Swoole\ExitException){
			return;
		}
		//$this->header("HTTP/1.1 500 Internal Error");
		SwooleContext::G()->response->status(500);
		if($this->http_exception_handler){
			($this->http_exception_handler)($ex);
		}else{
			echo "DNSwooleHttp Server Error: \n";
			echo $ex;
		}
			
	}
	protected function onHttpClean()
	{
		SwooleContext::G()->cleanUp();
		CoroutineSingleton::CleanUp();
	}
	/////////////////////////
	public function init($options,$server)
	{
		if(!defined('DN_SWOOLE_SERVER_INIT')){define('DN_SWOOLE_SERVER_INIT',true);}
		$this->options=array_merge(self::DEFAULT_OPTIONS,$options);
		$options=$this->options;
		
		$this->http_handler=$options['http_handler'];
		$this->http_exception_handler=$options['http_exception_handler'];
		
		$this->server=$server?:$options['swoole_server'];
	
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
		SuperGlobal::G(SwooleSuperGlobal::G());

		return $this;
	}
	public function bindDN($dn_options)
	{
		if(!$dn_options){return;}
		$dn_options['swoole']=$dn_options['swoole']??[];
		$dn_options['swoole']=array_replace_recursive(static::DEFAULT_DN_OPTIONS,$dn_options['swoole']);
		$dn_swoole_options=$dn_options['swoole'];
		$dn=DNMVCS::G()->init($dn_options);
		///////////////////////////////
		
		$this->options['http_handler']=$this->http_handler =[$dn,'run'];
		$this->options['http_exception_handler']=$this->http_exception_handler=[$dn,'onException'];
		
		$db_reuse_size=$dn_swoole_options['db_reuse_size']??0;
		if($db_reuse_size){
			$db_reuse_timeout=$dn_swoole_options['db_reuse_timeout']??5;
			$dbm=DNDBManager::G();
			DBConnectPoolProxy::G()->init($db_reuse_size,$db_reuse_timeout)->setDBHandler($dbm->db_create_handler,$dbm->db_close_handler);
			$dn->setDBHandler([DBConnectPoolProxy::G(),'onCreate'],[DBConnectPoolProxy::G(),'onClose']);
		}
		$dn->setHandlerForHeader([static::class,'header']);
		
		$dn->onBeforeRun(function(){
			CoroutineSingleton::CloneInstance(DNExceptionManager::class);
			// CoroutineSingleton::CloneInstance(DNConfig::class);
			CoroutineSingleton::CloneInstance(DNView::class);
			CoroutineSingleton::CloneInstance(DNRoute::class);
			CoroutineSingleton::CloneInstance(DNRuntimeState::class);
			//CoroutineSingleton::CloneInstance(DNDBManager::class);
		});
		
		return $this;
	}
	public function run()
	{
		define('DN_SWOOLE_SERVER_RUNNING',true);
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		$t=$this->server->start();
		fwrite(STDOUT,get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
	}
	public static function RunWithServer($server_options,$dn_options=[],$server=null)
	{
		return static::G()->init($server_options,$server)->bindDN($dn_options)->run();
	}
}
