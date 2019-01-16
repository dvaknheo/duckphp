<?php
namespace DNMVCS;
if(!trait_exists('DNMVCS\DNSingleton',false)){
trait DNSingleton
{
	protected static $_instances=[];
	public static function G($object=null)
	{
		if(defined('DNMVCS_DNSINGLETON_REPALACER')){
			$callback=DNMVCS_DNSINGLETON_REPALACER;
			return ($callback)(static::class,$object);
		}
		if($object){
			self::$_instances[static::class]=$object;
			return $object;
		}
		$me=self::$_instances[static::class]??null;
		if(null===$me){
			$me=new static();
			self::$_instances[static::class]=$me;
		}
		return $me;
	}
}
}
if(!trait_exists('DNMVCS\DNThrowQuickly',false)){
trait DNThrowQuickly
{
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		$class=static::class;
		throw new $class($message,$code);
	}
}
}
class SwooleCoroutineSingleton
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
				throw new \ErrorException("SwooleCoroutineSingleton fail:: $class use CreateInstance instead");
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
		$ret="==== SwooleCoroutineSingleton List Current cid [{$cid}] ==== ;\n";
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
class SwooleException extends \Exception
{
	use DNThrowQuickly;
}
trait SwooleHttpServer_Static
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
		return SwooleCoroutineSingleton::CloneInstance($class);
	}
}
trait SwooleHttpServer_GlobalFunc
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
trait SwooleHttpServer_SimpleHttpd
{
	
	protected function onHttpRun($request,$response){throw new SwooleException("Impelement Me");}
	protected function onHttpException($ex){throw new SwooleException("Impelement Me");}
	protected function onHttpClean(){throw new SwooleException("Impelement Me");}
	
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
		
		\defer(function()use($InitObLevel,$response){
			SwooleContext::G()->onShutdown();
			for($i=ob_get_level();$i>$InitObLevel;$i--){
				ob_end_flush();
			}
			$this->onHttpClean();
			
			$response->end();
		});
	}
}
trait SwooleHttpServer_WebSocket
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
class SwooleHttpServer
{
	use DNSingleton;
	use SwooleHttpServer_Static;
	use SwooleHttpServer_SimpleHttpd;
	use SwooleHttpServer_WebSocket;
	use SwooleHttpServer_GlobalFunc;
	
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
	public $server=null;
	
	public $http_handler=null;
	public $http_exception_handler=null;
	
	protected $static_root=null;
	protected function onHttpRun($request,$response)
	{
		SwooleContext::G()->initHttp($request,$response);
		SwooleCoroutineSingleton::CloneInstance(SwooleSuperGlobal::class);
		SwooleSuperGlobal::G()->init();
		
		if($this->http_handler){
			$this->runHttpHandler();
			return;
		}
		if($this->options['http_handler_file']){
			$path_info=SwooleSuperGlobal::G()->_SERVER['REQUEST_URI'];
			$file=$this->options['http_handler_basepath'].$this->options['http_handler_file'];
			$document_root=dirname($file);
			$this->includeHttpPhpFile($file,$document_root,$path_info);
			return;
		}
		if($this->options['http_handler_root']){
			$http_handler_root=$this->options['http_handler_basepath'].$this->options['http_handler_root'];
			$http_handler_root=rtrim($http_handler_root,'/').'/';
			$document_root=$this->static_root?:rtrim($http_handler_root,'/');
			
			
			$request_uri=SwooleSuperGlobal::G()->_SERVER['REQUEST_URI'];
			$path=parse_url($request_uri,PHP_URL_PATH);
			$flag=$this->runHttpFile($path,$document_root);
			if(!$flag){
				throw new SwooleException("404 Not Found!",404);
			}
			return;
		}
		$this->includeHttpPhpFile($file,$document_root,$path_info);
	}
	protected function runHttpFile($path,$document_root)
	{
	
		if(strpos($path,'/../')!==false || strpos($path,'/./')!==false){
			return false;
		}
		
		$full_file=$document_root.$path;
		if($path==='/'){
			$this->includeHttpPhpFile($document_root.'/index.php',$document_root,'');
			return true;
		}
		if(is_file($full_file)){
			$this->includeHttpFullFile($full_file,$document_root,'');
			return true;
		}
		
		$max=1000;
		$offset=0;
		for($i=0;$i<$max;$i++){
			$offset=strpos($path,'.php/',$offset);
			if(false===$offset){break;}
			$file=substr($path,0,$offset).'.php';
			$path_info=substr($path,$offset+strlen('.php'));
			$file=$document_root.$file;
			if(is_file($file)){
				$this->includeHttpPhpFile($file,$document_root,$path_info);
				return true;
			}
			
			$offset++;
		}
		
		$dirs=explode('/',$path);
		$prefix='';
		foreach($dirs as $block){
			$prefix.=$block.'/';
			$file=$document_root.$prefix.'index.php';
			if(is_file($file)){
				$path_info=substr($path,strlen($prefix)-1);
				$this->includeHttpPhpFile($file,$document_root,$path_info);
				return true;
			}
		}
		return false;
	}
	protected function includeHttpFullFile($full_file,$document_root,$path_info='')
	{
		$ext=pathinfo($full_file,PATHINFO_EXTENSION);
		if($ext==='php'){
			$this->includeHttpPhpFile($full_file,$document_root,$path_info);
			return;
		}
		$mime=mime_content_type($full_file);
		SwooleContext::G()->response->header('Content-Type',$mime);
		SwooleContext::G()->response->sendfile($full_file);
		return;
	}
	protected function includeHttpPhpFile($file,$document_root,$path_info)
	{
		SwooleSuperGlobal::G()->_SERVER['PATH_INFO']=$path_info;
		SwooleSuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=$document_root;
		SwooleSuperGlobal::G()->_SERVER['SCRIPT_FILENAME']=$file;
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
		SwooleContext::G()->response->status(500);
		if($this->http_exception_handler){
			($this->http_exception_handler)($ex);
		}else{
			echo "DNMVCS swoole mode: Server Error: \n";
			echo $ex;
		}
			
	}
	protected function onHttpClean()
	{
		SwooleContext::G()->cleanUp();
		SwooleCoroutineSingleton::CleanUp();
	}
	protected function check_swoole()
	{
		if(!function_exists('swoole_version')){
			echo 'DNMVCS swoole mode: PHP Extension swoole needed;';
			exit;
		}
		if (version_compare(swoole_version(), '4.2.0', '<')) {
			echo 'DNMVCS swoole mode: swoole >=4.2.0 needed;';
			exit;
		}
	}
	/////////////////////////
	public function init($options,$server=null)
	{
		if(!defined('DN_SWOOLE_SERVER_INIT')){define('DN_SWOOLE_SERVER_INIT',true);}
		$this->options=array_merge(self::DEFAULT_OPTIONS,$options);
		$options=$this->options;
		
		$this->http_handler=$options['http_handler'];
		$this->http_exception_handler=$options['http_exception_handler'];
		
		$this->server=$server?:$options['swoole_server'];
	
		if(!$this->server){
			$this->check_swoole();
			
			if(!$options['port']){
				echo 'DNMVCS swoole mode: No port ,set the port';
				exit;
			}
			if(!$options['websocket_handler']){
				$this->server=new \swoole_http_server($options['host'], $options['port']);
			}else{
				$this->server=new \swoole_websocket_server($options['host'], $options['port']);
			}
			//if(start server failed);
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
		
		\Swoole\Runtime::enableCoroutine();
		
		SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler();
		SwooleSuperGlobal::G();
		if(!defined('DNMVCS_DNSUPERGLOBAL_REPALACER')){
			define('DNMVCS_DNSUPERGLOBAL_REPALACER',SwooleSuperGlobal::class);
		}
		return $this;
	}
	public function run()
	{
		if(!defined('DN_SWOOLE_SERVER_RUNNING')){ define('DN_SWOOLE_SERVER_RUNNING',true); }
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		$t=$this->server->start();
		fwrite(STDOUT,get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
	}
}

class SwooleSuperGlobal
{
	use DNSingleton;
	
	public $_GET;
	public $_POST;
	public $_REQUEST;
	public $_SERVER;
	public $_ENV;
	public $_COOKIE;
	public $_SESSION;
	
	public function init()
	{
		$cid = \Swoole\Coroutine::getuid();
		if(!$cid){ return; }
		$request=SwooleHttpServer::Request();
		if(!$request){ return; }
		
		$this->_GET=$request->get??[];
		$this->_POST=$request->post??[];
		$this->_COOKIE=$request->cookie??[];
		$this->_REQUEST=array_merge($request->get??[],$request->post??[]);
		$this->_ENV=&$_ENV;
		
		$this->_SERVER=$_SERVER;
		if(isset($this->_SERVER['argv'])){
			$this->_SERVER['cli_argv']=$this->_SERVER['argv'];
			unset($this->_SERVER['argv']);
		}
		if(isset($this->_SERVER['argc'])){
			$this->_SERVER['cli_argc']=$this->_SERVER['argc'];
			unset($this->_SERVER['argc']);
		}
		foreach($request->header as $k=>$v){
			$k='HTTP_'.str_replace('-','_',strtoupper($k));
			$this->_SERVER[$k]=$v;
		}
		foreach($request->server as $k=>$v){
			$this->_SERVER[strtoupper($k)]=$v;
		}
		return $this;
	}
	public function _StartSession(array $options=[])
	{
		$t=SwooleSESSION::G();
		$t->_Start($options);
		static::G()->_SESSION=&$t->data;
	}
	public function _DestroySession()
	{
		SwooleSESSION::G()->_Destroy();
		static::G()->_SESSION=[];
	}
	public function _SetSessionHandler($handler)
	{
		SwooleSESSION::G()->setHandler($handler);
	}
}
class SwooleSESSION
{
	use DNSingleton;

	protected $handler=null;
	protected $session_id='';
	protected $options;
	protected $session_name;
	
	protected $is_started=false;
	public $data;
	
	public function setHandler(\SessionHandlerInterface $handler)
	{
		$this->handler=$handler;
	}
	protected function getOption($key)
	{
		return $this->options[$key]??ini_get('session.'.$key);
	}
	public function _Start(array $options=[])
	{
		if(!$this->handler){
			$this->handler=new SwooleSessionHandler();
		}
		
		$this->is_started=true;
		
		SwooleHttpServer::register_shutdown_function([$this,'writeClose']);
		
		$this->options=$options;
		
		$session_name=$this->getOption('name');
		$session_save_path=session_save_path();
		
		$cookies=SwooleHttpServer::Request()->cookie??[];
		$session_id=$cookies[$session_name]??null;
		if($session_id===null || ! preg_match('/[a-zA-Z0-9,-]+/',$session_id)){
			$session_id=$this->create_sid();
		}
		$this->session_id=$session_id;
		
		SwooleHttpServer::setcookie($session_name,$this->session_id
			,$this->getOption('cookie_lifetime')?time()+$this->getOption('cookie_lifetime'):0
			,$this->getOption('cookie_path')
			,$this->getOption('cookie_domain')
			,$this->getOption('cookie_secure')
			,$this->getOption('cookie_httponly')
		);
		
		if($this->getOption('gc_probability') > mt_rand(0,$this->getOption('gc_divisor'))){
			$this->handler->gc($this->getOption('gc_maxlifetime'));
		}
		$this->handler->open($session_save_path,$session_name);
		$raw=$this->handler->read($this->session_id);
		$this->data=unserialize($raw);
		if(!$this->data){$this->data=[];}
	}
	public function _Destroy()
	{
		$session_name=$this->getOption('name');
		$this->handler->destroy($this->session_id);
		$this->data=[];
		SwooleHttpServer::setcookie($session_name,'');
		$this->is_started=false;
	}
	public function writeClose()
	{
		if(!$this->is_started){return;}
		$this->handler->write($this->session_id,serialize($this->data));
		$this->data=[];
	}
	protected function create_sid()
	{
		return md5(microtime().mt_rand());
	}
}

class SwooleSessionHandler implements \SessionHandlerInterface
{
	use DNSingleton;
	
	private $savePath;

	public function open($savePath, $sessionName)
	{
		$this->savePath = $savePath;
		if (!is_dir($this->savePath)) {
			mkdir($this->savePath, 0777);
		}

		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($id)
	{
		return (string)@file_get_contents("$this->savePath/sess_$id");
	}

	public function write($id, $data)
	{
		return file_put_contents("$this->savePath/sess_$id", $data,LOCK_EX) === false ? false : true;
	}

	public function destroy($id)
	{
		$file = "$this->savePath/sess_$id";
		if (file_exists($file)) {
			unlink($file);
		}

		return true;
	}

	public function gc($maxlifetime)
	{
		foreach (glob("$this->savePath/sess_*") as $file) {
			if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
				unlink($file);
			}
		}

		return true;
	}
}
