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
class SwooleCoroutineSingleton
{
	use DNSingleton;
	protected static $_instances=[];
	
	public static function ReplaceDefaultSingletonHandler()
	{
		if(defined('DNMVCS_DNSINGLETON_REPALACER')){ return false; }
		define('DNMVCS_DNSINGLETON_REPALACER' ,self::class . '::'.'SingletonInstance');
		return true;
	}
	public static function SingletonInstance($class,$object)
	{
		$cid = \Swoole\Coroutine::getuid();
		$cid=($cid<=0)?0:$cid;
		self::$_instances[$cid]=self::$_instances[$cid]??[];
		
		if($object===null){
			$me=self::$_instances[$cid][$class]??null;
			if($me!==null){return $me;}
			if($cid!==0){
				$me=self::$_instances[0][$class]??null;
				if($me!==null){return $me;}
			}
			
			$me=new $class();
			self::$_instances[$cid][$class]=$me;
			return $me;
		}
		self::$_instances[$cid][$class]=$object;
		return $object;
	}
	///////////////
	public static function GetInstance($cid,$class)
	{
		return self::$_instances[$cid][$class]??null;
	}
	public static function SetInstance($cid,$class,$object)
	{
		self::$_instances[$cid][$class]=$object;
	}
	public static function DumpString()
	{
		return static::G()->_DumpString();
	}
	
	///////////////
	public function cleanUp()
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return;}
		unset(self::$_instances[$cid]);
	}
	
	public function forkMasterInstances($classes,$exclude_classes=[])
	{
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return;}
		
		foreach($classes as $class){
			if(!isset(self::$_instances[0][$class])){
				$real_class=$class;
				if(in_array($real_class,$exclude_classes)){
					continue;
				}
				self::$_instances[$cid][$class]=new $class();
				
				continue;
			}
			$real_class=get_class(self::$_instances[0][$class]);
			if(in_array($real_class,$exclude_classes)){
				self::$_instances[$cid][$class]=self::$_instances[$cid][$real_class];
				continue;
			}
			$object=self::$_instances[0][$real_class];
			self::$_instances[$cid][$real_class]=clone $object;
			if($class!==$real_class){
				self::$_instances[$cid][$class]=self::$_instances[$cid][$real_class];
			}
		}
	}
	
	public function forkAllMasterClasses()
	{
		$cid = \Swoole\Coroutine::getuid();
		foreach(self::$_instances[0] as $class =>$object){
			if(!isset($object)){continue;}
			self::$_instances[$cid][$class]=new $class();
		}
	}
	///////////////////////
	public function _DumpString()
	{
		$cid = \Swoole\Coroutine::getuid();
		$ret="==== SwooleCoroutineSingleton List Current cid [{$cid}] ==== ;\n";
		foreach(self::$_instances as $cid=>$v){
			foreach($v as $cid_class=>$object){
				$hash=$object?md5(spl_object_hash($object)):'';
				$class=$object?get_class($object):'';
				$class=$cid_class===$class?'':$class;
				$ret.="[$hash]$cid $cid_class($class)\n";
			}
		
		}
		return "{{$ret}}\n";
	}
	public static function Dump()
	{
		fwrite(STDERR,static::DumpString());
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
	public function regShutdown($call_data)
	{
		$this->shutdown_function_array[]=$call_data;
	}
	public function isWebSocketClosing()
	{
		return $this->frame->opcode == 0x08?true:false;
	}
	public function header(string $string, bool $replace = true , int $http_status_code =0)
	{
		if(!$this->response){return;}
		if($http_status_code){
			$this->response->status($http_status_code);
		}
		if(strpos($string,':')===false){return;} // 404,500 so on
		list($key,$value)=explode(':',$string);
		$this->response->header($key, $value);
	}
	public  function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		return $this->response->cookie($key,$value,$expire,$path,$domain,$secure,$httponly );
	}
}
class SwooleException extends \Exception
{
}
class Swoole404Exception extends SwooleException
{
	protected $code=404;
	
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
		return SwooleContext::G()->isWebSocketClosing();
	}
}
trait SwooleHttpServer_SystemWrapper
{
	public static function header(string $string, bool $replace = true , int $http_status_code =0)
	{
		return SwooleContext::G()->header($string,$replace,$http_status_code);
	}
	public static function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		return SwooleContext::G()->setcookie($key,$value,$expire,$path,$domain,$secure,$httponly);
	}
	public static function exit_system($code=0)
	{
		return SwooleHttpServer::G()->exit_request($code);
	}
	public static function set_exception_handler(callable $exception_handler)
	{
		return SwooleHttpServer::G()->set_http_exception_handler($exception_handler);
	}
	public static function register_shutdown_function(callable $callback,...$args)
	{
		return SwooleContext::G()->regShutDown(func_get_args());
	}
	
	public static function session_start(array $options=[])
	{
		return SwooleSuperGlobal::G()->session_start($options);
	}
	public static function session_destroy()
	{
		return SwooleSuperGlobal::G()->session_destroy();
	}
	public static function session_set_save_handler(\SessionHandlerInterface $handler)
	{
		return SwooleSuperGlobal::G()->session_set_save_handler($handler);
	}
	
	public static function system_wrapper_get_providers():array
	{
		$ret=[
			'header'				=>[static::class,'header'],
			'setcookie'				=>[static::class,'setcookie'],
			'exit_system'			=>[static::class,'exit_system'],
			'set_exception_handler'	=>[static::class,'set_exception_handler'],
			'register_shutdown_function' =>[static::class,'register_shutdown_function'],
			
			'super_global'			=>[SwooleSuperGlobal::class,'G'],
		];
		return $ret;
	}
}
trait SwooleHttpServer_SuperGlobal
{
	public static function SG()
	{
		return SwooleSuperGlobal::G();
	}
	public static function &GLOBALS($k,$v=null)
	{
		return SwooleSuperGlobal::G()->_GLOBALS($k,$v);
	}
	public static function &STATICS($k,$v=null)
	{
		return SwooleSuperGlobal::G()->_STATICS($k,$v,1);
	}
	public static function &CLASS_STATICS($class_name,$var_name)
	{
		return SwooleSuperGlobal::G()->_CLASS_STATICS($class_name,$var_name);
	}
}
trait SwooleHttpServer_SimpleHttpd
{
	
	protected function onHttpRun($request,$response){throw new SwooleException("Impelement Me");}
	protected function onHttpException($ex){throw new SwooleException("Impelement Me");}
	protected function onHttpClean(){throw new SwooleException("Impelement Me");}
	
	public function onRequest($request,$response)
	{
		SwooleContext::G(new SwooleContext())->initHttp($request,$response);
		$InitObLevel=ob_get_level();
		ob_start(function($str) use($response){
			if(''===$str){return;} // stop warnning;
			$response->write($str);
		});
		\defer(function()use($response,$InitObLevel){
			SwooleContext::G()->onShutdown();
			
			$this->onHttpClean();
			for($i=ob_get_level();$i>$InitObLevel;$i--){
				ob_end_flush();
			}
			SwooleContext::G()->cleanUp();
			SwooleCoroutineSingleton::G()->cleanUp();
			$response->end();
		});
		
		try{
			$this->onHttpRun($request,$response);
		}catch(\Throwable $ex){
			$this->onHttpException($ex);
		}
		
		
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
	use SwooleHttpServer_SystemWrapper;
	use SwooleHttpServer_SuperGlobal;
	
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
			'http_404_handler'=>null,
			
			'use_http_handler_root'=>false,
			'use_http_handler_file'=>false,
			
			'websocket_open_handler'=>null,
			'websocket_handler'=>null,
			'websocket_exception_handler'=>null,
			'websocket_close_handler'=>null,
			
			
		];
	public $server=null;
	
	public $http_handler=null;
	public $http_exception_handler=null;
	
	public $document_root=null;
	public $script_filename=null;
	
	protected $static_root=null;
	protected $auto_clean_autoload=true;
	protected $old_autoloads=[];
	
	public static function ReplaceDefaultSingletonHandler()
	{
		SwooleCoroutineSingleton::ReplaceDefaultSingletonHandler();
	}
	public function set_http_exception_handler($exception_handler)
	{
		$this->http_exception_handler=$exception_handler;
	}
	public function exit_request($code=0)
	{
		exit($code);
	}
	public function throw404()
	{
		throw new Swoole404Exception();
	}
	//////////
	public function getDymicClasses()
	{
		$classes=[
			SwooleSuperGlobal::class,
			SwooleContext::class,
		];
		return $classes;
	}
	public function createCoInstance($class,$object)
	{
		$cid = \Swoole\Coroutine::getuid();
		$cid=($cid<=0)?0:$cid;
		
		return SwooleCoroutineSingleton::SetInstance($cid,$class,$object);
	}
	public function forkMasterInstances($classes,$exclude_classes=[])
	{
		$exclude_classes=array_merge($exclude_classes,$this->getDymicClasses());
		return SwooleCoroutineSingleton::G()->forkMasterInstances($classes,$exclude_classes);
	}
	public function resetInstances()
	{
		$classes=$this->getDymicClasses();
		$instances=[];
		foreach($classes as $class){
			$instances[$class]=$class::G();
		}
		
		SwooleCoroutineSingleton::G()->forkAllMasterClasses();
		
		foreach($classes as $class){
			$class::G($instances[$class]);
		}
	}
	///////////
	protected function onHttpRun($request,$response)
	{
		$this->old_autoloads = spl_autoload_functions();
		
		SwooleSuperGlobal::G(new SwooleSuperGlobal())->init();
		//SwooleContext::G(new SwooleContext());
		
		if($this->http_handler){
			$this->auto_clean_autoload=false;
			if(!$this->http_handler){ throw new SwooleException("No Handler"); }
			
			if($this->options['use_http_handler_root']){
				list($path,$document_root)=$this->prepareRootMode();
				$this->document_root=$document_root;
				$this->script_filename=$this->document_root.'/'.'index.php';
			}
			
			if(isset($this->document_root)){
				SwooleSuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=$this->document_root;
			}
			if(isset($this->script_filename)){
				SwooleSuperGlobal::G()->_SERVER['SCRIPT_FILENAME']=$this->script_filename;
			}
			
			($this->http_handler)();
			
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
			list($path,$document_root)=$this->prepareRootMode();
			$flag=$this->runHttpFile($path,$document_root);
			if(!$flag){
				throw new Swoole404Exception("404 Not Found!",404);
			}
			return;
		}
		throw new Swoole404Exception("404 Not Found!",404);
		//$this->includeHttpPhpFile($file,$document_root,$path_info);
	}
	protected function prepareRootMode()
	{
		$http_handler_root=$this->options['http_handler_basepath'].$this->options['http_handler_root'];
		$http_handler_root=rtrim($http_handler_root,'/').'/';
		$document_root=$this->static_root?:rtrim($http_handler_root,'/');
		
		$path=parse_url(SwooleSuperGlobal::G()->_SERVER['REQUEST_URI'],PHP_URL_PATH);
		
		return [$path,$document_root];
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
	protected function onHttpException($ex)
	{
		if($ex instanceof \Swoole\ExitException){
			return;
		}
		if($ex instanceof Swoole404Exception){
			if($this->http_handler && $this->options['use_http_handler_root'] && !$this->auto_clean_autoload){
				$this->auto_clean_autoload=true;
				list($path,$document_root)=$this->prepareRootMode();
				try{
					$flag=$this->runHttpFile($path,$document_root);
					if($flag){ return; }
				}catch(\Throwable $ex){
					$this->onHttpException($ex);
					return;
				}
				
			}
			SwooleContext::G()->response->status(404);
			echo "DNMVCS swoole mode: 404. \n";
			echo $ex;
			return;
		}		
		if($this->http_exception_handler){
			($this->http_exception_handler)($ex);
			return;
		}
		SwooleContext::G()->response->status(500);
		echo "DNMVCS swoole mode: Server Error. \n";
		echo $ex;
	}
	protected function onHttpClean()
	{
		if(!$this->auto_clean_autoload){ return;}
		$functions = spl_autoload_functions();
		$this->old_autoloads=$this->old_autoloads?:[];
		foreach($functions as $function) {
			if(in_array($function,$this->old_autoloads)){ continue; }
			spl_autoload_unregister($function);
		}
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
		static::G($this);
		SwooleSuperGlobal::G();
		
		if(!defined('DNMVCS_SYSTEM_WRAPPER_INSTALLER')){
			define('DNMVCS_SYSTEM_WRAPPER_INSTALLER',static::class .'::' .'system_wrapper_get_providers');
		}
		return $this;
	}
	public function run()
	{
		//save classes to reset;
		if(!defined('DN_SWOOLE_SERVER_RUNNING')){ define('DN_SWOOLE_SERVER_RUNNING',true); }
		fwrite(STDOUT,get_class($this)." run at ".DATE(DATE_ATOM)." ...\n");
		
		$this->server->start();
		fwrite(STDOUT,get_class($this)." run end ".DATE(DATE_ATOM)." ...\n");
	}
}

class SwooleSuperGlobal
{
	use DNSingleton;
	
	public $_GET;
	public $_POST;
	public $_REQUEST;
	public $_SERVER=[];
	public $_ENV;
	public $_COOKIE=[];
	public $_SESSION;
	public $_FILES=[];
	
	public $GLOBALS=[];
	public $STATICS=[];
	public $CLASS_STATICS=[];

	public $is_inited=false;
	public function init()
	{
		if($this->is_inited){return $this;}
		$this->is_inited=true;
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
		$this->_SERVER['cli_script_filename']=$this->_SERVER['SCRIPT_FILENAME'];
		
		$this->_FILES=$request->files;
		return $this;
	}
	public function session_start(array $options=[])
	{
		$t=SwooleSession::G();
		$t->_Start($options);
		static::G()->_SESSION=&$t->data;
	}
	public function session_destroy()
	{
		SwooleSession::G()->_Destroy();
		static::G()->_SESSION=[];
	}
	public function session_set_save_handler($handler)
	{
		SwooleSession::G()->setHandler($handler);
	}
	//////////////
	public function &_GLOBALS($k,$v=null)
	{
		if(!isset($this->GLOBALS[$k])){ $this->GLOBALS[$k]=$v;}
		return $this->GLOBALS[$k];
	}
	public function &_STATICS($name,$value=null,$parent=0)
	{
		$t=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS,$parent+2)[$parent+1]??[]; //todo Coroutine trace ?
		$k='';
		$k.=isset($t['object'])?'object_'.spl_object_hash($t['object']):'';
		$k.=$t['class']??'';
		$k.=$t['type']??'';
		$k.=$t['function']??'';
		$k.=$k?'$':'';
		$k.=$name;
		
		if(!isset($this->STATICS[$k])){ $this->STATICS[$k]=$value;}
		return $this->STATICS[$k];
	}
	public function &_CLASS_STATICS($class_name,$var_name)
	{		
		$k=$class_name.'::$'.$var_name;
		if(!isset($this->CLASS_STATICS[$k])){
				$ref=new \ReflectionClass($class_name);
				$reflectedProperty = $ref->getProperty($var_name);
				$reflectedProperty->setAccessible(true);
				$this->CLASS_STATICS[$k]=$reflectedProperty->getValue();
		}
		return $this->CLASS_STATICS[$k];
	}
}
class SwooleSession
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
			$this->handler=SwooleSessionHandler::G();
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
