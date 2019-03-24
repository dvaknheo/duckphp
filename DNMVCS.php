<?php
//dvaknheo@github.com
//OK，Lazy
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
//fwrite(STDOUT,"SINGLETON ". static::class ."\n");
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
if(!trait_exists('DNMVCS\DNClassExt',false)){
trait DNClassExt
{
	protected $static_methods=[];
	protected $dynamic_methods=[];
	
	public static function __callStatic($name, $arguments) 
	{
		$self=static::G();
		$class=get_class($self);
		if($class!==static::class && method_exists($class,$name)){
			return call_user_func_array([$class,$name], $arguments);
		}
		if(isset($self->static_methods[$name]) && is_callable($self->static_methods[$name])){
			return call_user_func_array($self->static_methods[$name], $arguments);
		}
		throw new \Error("Call to undefined method ".static::class ."::$name()");
	}
	public function __call($name, $arguments) 
	{
		if(isset($this->dynamic_methods[$name]) && is_callable($this->dynamic_methods[$name])){
			return call_user_func_array($this->dynamic_methods[$name], $arguments);
		}
		
		throw new \Error("Call to undefined method ".static::class ."::$name()");
	}
	public function assignStaticMethod($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->static_methods=array_merge($this->static_methods,$key);
		}else{
			$this->static_methods[$key]=$value;
		}
	}
	public function assignDynamicMethod($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->dynamic_methods=array_merge($this->dynamic_methods,$key);
		}else{
			$this->dynamic_methods[$key]=$value;
		}
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

class DNException extends \Exception
{
	use DNThrowQuickly;
}

class DNAutoLoader
{
	use DNSingleton;
	const DEFAULT_OPTIONS=[
			'path'=>null,
			
			'namespace'=>'MY',
			'with_no_namespace_mode'=>true,
			'path_namespace'=>'app',
			
			'path_no_namespace_mode'=>'app',
			
			'skip_system_autoload'=>false,
			'skip_app_autoload'=>false,
		];
	
	public $options=[];
	
	public $path;
	protected $namespace;

	
	protected $path_namespace;
	protected $path_no_namespace_mode;
	protected $with_no_namespace_mode=true;
	
	protected $is_loaded=false;
	protected $is_inited=false;
	public $namespace_paths=[];
	
	public function init($options=[])
	{
		if($this->is_inited){ return $this; }
		$this->is_inited=true;
		
		//$options=array_merge(self::DEFAULT_OPTIONS,$options);
		$options=array_intersect_key(array_merge(self::DEFAULT_OPTIONS,$options),self::DEFAULT_OPTIONS);
		$this->options=$options;
		
		if(!isset($options['path']) || !$options['path']){
			$path=realpath(getcwd().'/../');
			$options['path']=$path;
		}
		$options['path']=rtrim($options['path'],'/').'/';
		
		$this->options['path']=$options['path'];
		$this->path=$options['path'];
		
		$this->namespace=$options['namespace'];
		$this->path_namespace=$this->path.rtrim($options['path_namespace'],'/').'/';
		$this->path_no_namespace_mode=$this->path.rtrim($options['path_no_namespace_mode'],'/').'/';
		
		$this->with_no_namespace_mode=$options['with_no_namespace_mode'];
		
		if( !$options['skip_app_autoload'] ){
			$this->assignPathNamespace($this->path_namespace,$this->namespace);
		}
		$in_composer=class_exists('Composer\Autoload\ClassLoader')?true:false;
		if( !($in_composer || $options['skip_system_autoload']) ){
			$this->assignPathNamespace(__DIR__,'DNMVCS');
		}
		
		return $this;
	}
	public function run()
	{
		if($this->is_loaded){return;}
		$this->is_loaded=true;
		spl_autoload_register([$this,'_autoload']);
	}
	public function _autoload($class)
	{
		$flag=$this->loadByPath($class);
		if($flag){return;}
		$flag=$this->loadWithNoNameSpace($class);
		if($flag){return;}
	}

	protected function loadWithNoNameSpace($class)
	{
		if(!$this->with_no_namespace_mode){return;}
		if(strpos($class,'\\')!==false){ return; }
		$path_simple=$this->path_no_namespace_mode;
		
		$flag=preg_match('/(Service|Model)$/',$class,$m);
		if(!$flag){return;}
		$file=$path_simple.$m[1].'/'.$class.'.php';
		if (!$file || !file_exists($file)) {return;}
		require $file;
		return true;
	}
	protected function loadByPath($class)
	{
		foreach($this->namespace_paths as $base_dir =>$prefix){
			if($prefix!==''){ $prefix .='\\'; }
			if (strncmp($prefix, $class, strlen($prefix)) !== 0) { continue; }
			
			$relative_class = substr($class, strlen($prefix));
			$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
			
			if (!file_exists($file)) { continue; }
			require $file;
			return true;
		}
		return false;
	}
	public function assignPathNamespace($path,$namespace=null)
	{
		if(is_array($path)&& $namespace===null){
			foreach($path as $k=>$v){
				$path[$k]=($v==='')?:rtrim($path,'/').'/';
			}
			$this->namespace_paths=array_merge($this->paths,$path);
		}else{
			
			$path=($path==='')?:rtrim($path,'/').'/';
			$this->namespace_paths[$path]=$namespace;
			
		}
	}
	public function cacheClasses()
	{
		$ret=[];
		foreach($this->namespace_paths as $source=>$name){
			if($name===__NAMESPACE__){ continue;}
			$source=realpath($source);
			$directory = new \RecursiveDirectoryIterator($source,\FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS );
			$iterator = new \RecursiveIteratorIterator($directory);
			$files = \iterator_to_array($iterator,false);
			$ret+=$files;
		}
		foreach($ret as $file){
			if(opcache_is_script_cached ($file)){continue;}
			@opcache_compile_file($file);
		}
		return $ret;
	}
}

class DNRoute
{
	use DNSingleton;
	
	const DEFAULT_OPTIONS=[
			'path'=>null,
			'namespace'=>'MY',
			'with_no_namespace_mode'=>true,
			
			'namespace_controller'=>'Controller',
			'path_controller'=>'app/Controller',
			
			'enable_paramters'=>false,
			'disable_default_class_outside'=>false,
			
			'base_controller_class'=>null,
			'prefix_no_namespace_mode'=>'',
			'lazy_controller_class'=>'DNController',
			
			'enable_post_prefix'=>true,
			'prefix_post'=>'do_',
			'default_method_for_miss'=>null,
			
			'welcome_controller'=>'Main',
			'default_method'=>'index',
			
			'the_404_hanlder'=>null,
		];
	
	public $parameters=[];
	public $urlHandler=null;
	
	
	protected $welcome_controller='Main';
	protected $default_method='index';
	
	protected $enable_paramters=false;
	protected $with_no_namespace_mode=true;
	protected $prefix_no_namespace_mode='';
	protected $namespace_controller='';
	protected $lazy_controller_class='DNController';
	protected $enable_post_prefix=true;
	protected $disable_default_class_outside=false;
	protected $default_method_for_miss=null;
	protected $base_controller_class=null;
	public $the_404_hanlder=null;
	
	public $prefix_post='do_';
	
	protected $path;
	
	public $calling_path='';
	public $calling_class='';
	public $calling_method='';
	
	public $path_info='';
	public $request_method='';
	public $script_filename='';
	public $document_root='';

	
	public $routeHooks=[];
	public $callback=null;
	protected $has_bind_server_data=false;
	
	public function _URL($url=null)
	{
		if($this->urlHandler){return ($this->urlHandler)($url);}
		return $this->defaultURLHandler($url);
	}
	public function defaultURLHandler($url=null)
	{
		if(strlen($url)>0 && '/'==$url{0}){ return $url;};
		
		$basepath=substr(rtrim(str_replace('\\','/',$this->script_filename),'/').'/',strlen($this->document_root));

		if($basepath=='/index.php'){$basepath='/';}
		if($basepath=='/index.php/'){$basepath='/';}
		
		if(''===$url){return $basepath;}
		
		if('?'==$url{0}){ return $basepath.$this->path_info.$url; }
		if('#'==$url{0}){ return $basepath.$this->path_info.$url; }
		
		return $basepath.$url;
	}
	public function _Parameters()
	{
		return $this->parameters;
	}
	
	public function init($options)
	{
		//$options=array_merge(self::DEFAULT_OPTIONS,$options);
		$options=array_intersect_key(array_merge(self::DEFAULT_OPTIONS,$options),self::DEFAULT_OPTIONS);
		$this->options=$options;
		
		$this->path=$options['path'].$options['path_controller'].'/';
		
		$this->enable_paramters=$options['enable_paramters'];
		$this->with_no_namespace_mode=$options['with_no_namespace_mode'];
		$this->prefix_no_namespace_mode=$options['prefix_no_namespace_mode'];
		
		$this->lazy_controller_class=$options['lazy_controller_class'];
		
		$this->enable_post_prefix=$options['enable_post_prefix'];
		$this->prefix_post=$options['prefix_post'];
		$this->disable_default_class_outside=$options['disable_default_class_outside'];
		$this->default_method_for_miss=$options['default_method_for_miss'];
		
		$this->enable_post_prefix=$options['welcome_controller'];
		$this->default_method=$options['default_method'];
		
		$this->the_404_hanlder=$options['the_404_hanlder'];
		
		$namespace=$options['namespace'];
		$namespace_controller=$options['namespace_controller'];
		if(substr($namespace_controller,0,1)!=='\\'){
			$namespace_controller=$namespace.'\\'.$namespace_controller;
		}
		$namespace_controller=ltrim($namespace_controller,'\\');
		$this->namespace_controller=$namespace_controller;
		
		$this->base_controller_class=$options['base_controller_class'];
		if($this->base_controller_class && substr($this->base_controller_class,0,1)!=='\\'){
			$this->base_controller_class=$namespace.'\\'.$this->base_controller_class;
		}

		
		return $this;
	}
	public function bindServerData($server)
	{		
		$this->script_filename=$server['SCRIPT_FILENAME']??'';
		$this->document_root=$server['DOCUMENT_ROOT']??'';
		$this->request_method=$server['REQUEST_METHOD']??'GET';
		$this->path_info=$server['PATH_INFO']??'';
		
		$argv=$server['argv']??[];
		
		if(PHP_SAPI==='cli'){
			if(count($argv)>=2){
				$this->path_info=$argv[1];
				array_shift($argv);
				array_shift($argv);
				$this->parameters=$argv;
			}
		}
		$this->has_bind_server_data=true;
		return $this;
	}
	public function set404($callback)
	{
		$this->the_404_hanlder=$callback;
	}
	public function setURLHandler($callback)
	{
		$this->urlHandler=$callback;
	}
	public function getURLHandler()
	{
		return $this->urlHandler;
	}
	public function addRouteHook($hook,$prepend=false,$once=true)
	{
		if($once){
			foreach($this->routeHooks as $v){
				if($v==$hook){ return false;}
			}
		}
		if(!$prepend){
			array_push($this->routeHooks,$hook);
		}else{
			array_unshift($this->routeHooks,$hook);
		}
		return true;
	}
	public function run()
	{
		if(!$this->has_bind_server_data){
			$this->loadServerData($_SERVER);
		}
		$this->path_info=ltrim($this->path_info,'/');
		foreach($this->routeHooks as $hook){
			($hook)($this);
		}
		if(null===$this->callback){
			$this->callback=$this->defaultRouteHandler();
		}
		if(null!==$this->callback){
			($this->callback)(...$this->parameters);
			return true;
		}
		if(!$this->the_404_hanlder){
			header("HTTP/1.0 404 Not Found");
			echo "404 File Not Found.\n";
			echo "DNRoute Notice: 404 .  You need set 404 Handler by DNRoute->set404(\$callback).";
			exit;
		}
		($this->the_404_hanlder)();
		return false;
	}
	public function stopRunDefaultHandler()
	{
		$this->callback=function(){};
	}
	
	protected function getFullClassByAutoLoad($path_class)
	{
		$path_class=$path_class?:$this->welcome_controller;
		$class=$this->namespace_controller.'\\'.str_replace('/','\\',$path_class);
		if( class_exists($class) ){ return $class; }
		return null;
	}
	protected function getFullClassByNoNameSpace($path_class,$confirm=false)
	{
		
		$class=$this->checkLoadClass($path_class);
		if($class){
			if($confirm){return null;}
			return $class;
		}
		$file=$this->path.$path_class.'.php';
		if(!is_file($file)){
			if(!$confirm){ $this->error="no file to get class"; }
			return null;
		}
		$this->includeControllerFile($file);
		
		return $this->checkLoadClass($path_class);
	}
	protected function checkLoadClass($path_class)
	{
		$class=$this->prefix_no_namespace_mode . str_replace('/','__',$path_class);
		if(class_exists($class)){return $class; }
		$class=($this->lazy_controller_class)?$this->lazy_controller_class:'';
		if(class_exists($class)){return $class; }
		$class=($this->lazy_controller_class)?$this->namespace_controller.'\\'.$this->lazy_controller_class:'';
		if(class_exists($class)){return $class; }
		return null;
	}
	protected function getClassMethodAndParameters($blocks,$method)
	{
		$class=null;
		$paramters=[];
		$callinig_path='';
		$p=implode('/',$blocks);
		$l=count($blocks);
		for($i=0;$i<$l;$i++){
			$class_names=array_slice($blocks,0,$l-$i);
			$parameters=$i?array_slice($blocks,-$i):[];
			$calling_path=implode('/',$class_names);
			
			$class=$this->namespace_controller.'\\'.implode('\\',$class_names);
			if(class_exists($class)){ break; }
		}
		if(!$class){
			$this->error="No faill paramter not failed";
			return [null,$method,$parameters,$calling_path];
		}
		array_push($parameters,$method);
		$method=array_shift($parameters);
		$calling_path=$calling_path.'/'.$method;
		
		return [$class,$method,$parameters,$calling_path];
	}
	protected function getClassMethodAndParameters2($blocks,$method)
	{
		$class=null;
		$paramters=[];
		$callinig_path='';
		$p=implode('/',$blocks);
		$l=count($blocks);
		for($i=0;$i<$l;$i++){
			$class_names=array_slice($blocks,0,$l-$i);
			$parameters=$i?array_slice($blocks,-$i):[];
			$calling_path=implode('/',$class_names);
			
			$class=$this->getFullClassByNoNameSpace($calling_path);
			if($class){ break; }
		}
		if(!$class){
			$this->error="No faill paramter not failed";
			return [null,$method,$parameters,$calling_path];
		}
		array_push($parameters,$method);
		$method=array_shift($parameters);
		$calling_path=$calling_path.'/'.$method;
		
		return [$class,$method,$parameters,$calling_path];
	}
	public function defaultRouteHandler()
	{
		$path_info=$this->path_info;
		
		$class_blocks=explode('/',$path_info);
		$method=array_pop($class_blocks);
		$class_path=implode('/',$class_blocks);
		
		$this->calling_path=$class_path?$this->path_info:$this->welcome_controller.'/'.$method;
		
		if($this->disable_default_class_outside){
			if($class_path===$this->welcome_controller){
				$this->error="disable_default_class_outside! {$this->welcome_controller} ";
				return null;
			}
		}
		$full_class=$this->getFullClassByAutoLoad($class_path,true);
		$callback=$this->getCallback($full_class,$method);
		if($callback){
			return $callback; 
		}
		if( $this->enable_paramters ){
			list($full_class,$the_method,$parameters,$calling_path)=$this->getClassMethodAndParameters($class_blocks,$method);
			if($full_class){
				$method=$the_method;
				$this->parameters=$parameters;
				$this->calling_path=$calling_path;
				
				$callback=$this->getCallback($full_class,$method);
				if($callback){
					return $callback; 
				}
			}
		}
		///////////////////////
		
		if($this->with_no_namespace_mode){
			$full_class=$this->getFullClassByNoNameSpace($class_path);
			$callback=$this->getCallback($full_class,$method);
			if($callback){
				return $callback; 
			}
		}
		////////
		if( $this->enable_paramters ){
			list($full_class,$the_method,$parameters,$calling_path)=$this->getClassMethodAndParameters2($class_blocks,$method);
			if($full_class){
				$method=$the_method;
				$this->parameters=$parameters;
				$this->calling_path=$calling_path;
			}
		}
		if(!$full_class){
			$this->error="NoClass";
			return null;
		}
		
		return $this->getCallback($full_class,$method);
	}
	protected function getCallback($full_class,$method)
	{
		if(!$full_class){ return null; }
		$this->calling_class=$full_class;
		$this->calling_method=$method;
		
		$object=new $full_class();
		if($this->base_controller_class && !is_a($obj,$this->base_controller_class)){
			return null;
		}
		return $this->getMethodToCall($object,$method);
	}
	protected function getMethodToCall($obj,$method)
	{
		$method=$method===''?$this->default_method:$method;
		if(substr($method,0,2)=='__'){
			return null;
		}
		if($this->enable_post_prefix && $this->request_method==='POST' &&  method_exists($obj,$this->prefix_post.$method)){
			$method=$this->prefix_post.$method;
		}
		if($this->default_method_for_miss && !method_exists($obj,$method)){
			$method=$this->default_method_for_miss;
		}
		if(!is_callable([$obj,$method])){
			return null;
		}
		return [$obj,$method];
	}
	// You can override it; variable indived
	protected function includeControllerFile($file)
	{
		require_once($file);
	}
	
	public function getRouteCallingPath()
	{
		return $this->calling_path;
	}
	public function getRouteCallingClass()
	{
		return $this->calling_class;
	}
	public function getRouteCallingMethod()
	{
		return $this->calling_method;
	}
}

class DNView
{
	use DNSingleton;

	protected $head_file;
	protected $foot_file;
	protected $view_file;
	
	public $path;
	public $data=[];
	public $view=null;
	
	protected $before_show_handler=null;
	
	public function _Show($data=[],$view)
	{
		$this->view=$view;
		$this->data=array_merge($this->data,$data);
		$data=null;
		$view=null;
		extract($this->data);
		
		if(isset($this->before_show_handler)){
			($this->before_show_handler)($data,$this->view);
		}
		$this->prepareFiles();
		
		
		if($this->head_file){
			include($this->path.$this->head_file);
		}
		
		include($this->view_file);
		
		if($this->foot_file){
			include($this->path.$this->foot_file);
		}
	}
	public function _ShowBlock($view,$data=null)
	{
		$this->view_file=$this->path.rtrim($view,'.php').'.php';
		$this->data=isset($data)?$data:$this->data;
		$data=null;
		$view=null;
		extract($this->data);
		
		include($this->view_file);
	}
	protected function prepareFiles()
	{
		$this->view_file=$this->path.rtrim($this->view,'.php').'.php';
		if($this->head_file){
			$this->head_file=rtrim($this->head_file,'.php').'.php';
		}
		if($this->foot_file){
			$this->foot_file=rtrim($this->foot_file,'.php').'.php';
		}
	}
	public function init($path)
	{
		$this->path=$path;
	}
	public function setBeforeShowHandler($callback)
	{
		$this->before_show_handler=$callback;
	}
	
	public function setViewWrapper($head_file,$foot_file)
	{
		$this->head_file=$head_file;
		$this->foot_file=$foot_file;
	}
	
	public function assignViewData($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->data=array_merge($this->data,$key);
		}else{
			$this->data[$key]=$value;
		}
	}
}

class DNConfiger
{
	use DNSingleton;

	public $path;
	protected $setting_file_basename='setting';
	protected $setting=[];
	protected $all_config=[];
	protected $is_inited=false;
	public function init($path,$options)
	{
		$this->path=$path;
		$this->setting=$options['setting']??[];
		$this->all_config=$options['all_config']??[];
		$this->setting_file_basename=$options['setting_file_basename']??'setting';
	}
	public function _Setting($key)
	{
		if($this->is_inited || !$this->setting_file_basename){ return $this->setting[$key]??null; }
		$basename=$this->setting_file_basename;
		$full_config_file=$this->path.$basename.'.php';
		if(!is_file($full_config_file)){
			echo '<h1>'.'DNMVCS Fatal: no setting file['.$full_config_file.']!,change '.$basename.'.sample.php to '.$basename.'.php !'.'</h1>';
			exit;
		}
		$this->setting=$this->loadFile($basename,false);
		$this->is_inited=true;
		return $this->setting[$key]??null;
	}
	
	public function _Config($key,$file_basename='config')
	{
		$config=$this->_LoadConfig($file_basename);
		return isset($config[$key])?$config[$key]:null;
	}
	public function _LoadConfig($file_basename='config')
	{
		if(isset($this->all_config[$file_basename])){return $this->all_config[$file_basename];}
		$config=$this->loadFile($file_basename,false);
		$this->all_config[$file_basename]=$config;
		return $config;
	}
	protected function loadFile($basename,$checkfile=true)
	{	
		$file=$this->path.$basename.'.php';
		if($checkfile && !is_file($file)){return null;}
		$ret=(function($file){return include($file);})($file);
		return $ret;
	}
}
class DNDBManager
{
	use DNSingleton;
	
	public $tag_write=0;
	public $tag_read='1';
	
	protected $database_config_list=[];
	protected $databases=[];
	
	protected $db_create_handler=null;
	protected $db_close_handler=null;
	
	protected $before_get_db_handler=null;
	public function init($database_config_list=[])
	{
		$this->database_config_list=$database_config_list;
	}
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	{
		$this->db_create_handler=$db_create_handler;
		$this->db_close_handler=$db_close_handler;
	}
	public function setBeforeGetDBHandler($before_get_db_handler)
	{
		$this->before_get_db_handler=$before_get_db_handler;
	}
	public function getDBHandler()
	{
		return [$db_create_handler,$db_close_handler];
	}
	public function _DB($tag=null)
	{
		if(isset($this->before_get_db_handler)){ ($this->before_get_db_handler)($tag); }
		if(!isset($tag)){
			$t=array_keys($this->database_config_list);
			$tag=$t[0];
		}
		
		if(!isset($this->databases[$tag])){
			$db_config=$this->database_config_list[$tag]??null;
			if($db_config===null){return null;}
			$this->databases[$tag]=($this->db_create_handler)($db_config,$tag);
		}
		return $this->databases[$tag];
	}
	public function _DB_W()
	{
		return $this->_DB($this->tag_write);
	}
	public function _DB_R()
	{
		if(!isset($this->database_config_list[$this->tag_read])){
			return $this->_DB();
		}
		return $this->_DB($this->tag_read);
	}
	public function closeAllDB()
	{
		if(!$this->db_close_handler){ return; }
		foreach($this->databases as $v){
			($this->db_close_handler)($v);
		}
		$this->databases=[];
	}
}
class DNRuntimeState
{
	use DNSingleton;
	protected $is_running=false;
	protected $error_reporting_old;
	public function isRunning()
	{
		return $this->is_running;
	}
	public function begin()
	{
		$this->is_running=true;
		$this->error_reporting_old=error_reporting();
	}
	public function end()
	{
		error_reporting($this->error_reporting_old);
		$this->is_running=false;
	}
	public function skipNoticeError()
	{
		$this->error_reporting_old =error_reporting();
		error_reporting($this->error_reporting_old & ~E_NOTICE);
	}
}
class DNSuperGlobal
{
	use DNSingleton;
	
	public $_GET;
	public $_POST;
	public $_REQUEST;
	public $_SERVER;
	public $_ENV;
	public $_COOKIE;
	public $_SESSION;
	public $_FILES;
	
	public $GLOBALS=[];
	public $STATICS=[];
	public $CLASS_STATICS=[];
	
	public function init()
	{	
		$this->_GET		=&$_GET;
		$this->_POST	=&$_POST;
		$this->_REQUEST	=&$_REQUEST;
		$this->_SERVER	=&$_SERVER;
		$this->_ENV		=&$_ENV;
		$this->_COOKIE	=&$_COOKIE;
		$this->_SESSION	=&$_SESSION;
		$this->_FILES	=&$_FILES;
		$this->GLOBALS	=&$GLOBALS;
	}
	///////////////////////////////
	public function session_start(array $options=[])
	{
		if(session_status() !== PHP_SESSION_ACTIVE ){ session_start($options); }
		$this->_SESSION=&$_SESSION;
	}
	public function session_destroy()
	{
		session_destroy();
		$this->_SESSION=[];
	}
	public function session_set_save_handler($handler)
	{
		session_set_save_handler($handler);
	}
	///////////////////////////////
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
class DNExceptionManager
{
	use DNSingleton;
	
	protected $errorHandlers=[];
	protected $dev_error_handler=null;
	protected $exception_error_handler_init=null;
	protected $exception_error_handler=null;
	
	public function setDefaultExceptionHandler($default_exception_handler)
	{
		return $this->exception_error_handler=$default_exception_handler;
	}
	public function assignExceptionHandler($class,$callback=null)
	{
		$class=is_string($class)?array($class=>$callback):$class;
		foreach($class as $k=>$v){
			$this->errorHandlers[$k]=$v;
		}
	}
	public function setMultiExceptionHandler(array $classes,$callback)
	{
		foreach($classes as $k){
			$this->errorHandlers[$class]=$callback;
		}
	}
	public function on_error_handler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)){
			return false;
		}
		switch ($errno) {
			case E_USER_NOTICE:
			case E_NOTICE:
			case E_STRICT:
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
				($this->dev_error_handler)($errno, $errstr, $errfile, $errline);
				break;
			default:
				throw new \ErrorException($errstr,$errno,$errno,$errfile, $errline);
				//TODO test more in swoole;
				break;
		}
		/* Don't execute PHP internal error handler */
		return true;
	}
	public function checkAndRunErrorHandlers($ex,$inDefault)
	{
		$exception_class=get_class($ex);
		foreach($this->errorHandlers as $class =>$callback){
			if($class===$exception_class){
				($callback)($ex);
				return true;
			}
		}
		if($inDefault){
			if($this->exception_error_handler != $this->exception_error_handler_init){
				($this->exception_error_handler)($ex);
				return true;
			}
			
		}
		
		return false;
	}
	public function on_exception($ex)
	{
		$flag=$this->checkAndRunErrorHandlers($ex,false);
		if($flag){return;}
		($this->exception_error_handler)($ex);
	}
	public $is_inited=false;
	public function init($exception_handler,$dev_error_handler,$system_exception_handler=null)
	{
		if($this->is_inited){ return; }
		$this->is_inited=true;
		$this->dev_error_handler=$dev_error_handler;
		$this->exception_error_handler=$exception_handler;
		$this->exception_error_handler_init=$exception_handler;
		
		set_error_handler([$this,'on_error_handler']);
		if($system_exception_handler){
			return ($system_exception_handler)($exception_handler);
		}else{
			set_exception_handler([$this,'on_exception']);
		}
		
	}
}
trait DNMVCS_Glue
{
	//route
	public static function URL($url=null)
	{
		return DNRoute::G()->_URL($url);
	}
	public static function Parameters()
	{
		return DNRoute::G()->_Parameters();
	}
	public function assignRewrite($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->options['rewrite_map']=array_merge($this->options['rewrite_map'],$key);
		}else{
			$this->options['rewrite_map'][$key]=$value;
		}
	}
	public function assignRoute($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->options['route_map']=array_merge($this->options['route_map'],$key);
		}else{
			$this->options['route_map'][$key]=$value;
		}
	}
	public function addRouteHook($hook,$prepend=false,$once=true)
	{
		return DNRoute::G()->addRouteHook($hook,$prepend,$once);
	}
	public function getRouteCallingMethod()
	{
		return DNRoute::G()->getRouteCallingMethod();
	}
	//view
	public static function Show($data=[],$view=null)
	{
		return DNView::G()->_Show($data,$view);
	}

	public static function ExitJson($ret)
	{
		return DNMVCSExt::G()->_ExitJson($ret);
	}
	public static function ExitRedirect($url,$only_in_site=true)
	{
		return DNMVCSExt::G()->_ExitRedirect($url,$only_in_site);
	}
	public static function ExitRouteTo($url)
	{
		return DNMVCSExt::G()->_ExitRedirect(static::URL($url),true);
	}
	public static function Exit404()
	{
		static::G()->onShow404();
		static::exit_system();
	}
	public function setViewWrapper($head_file=null,$foot_file=null)
	{
		return DNView::G()->setViewWrapper($head_file,$foot_file);
	}
	public static function ShowBlock($view,$data=null)
	{
		return DNView::G()->_ShowBlock($view,$data);
	}
	public function assignViewData($key,$value=null)
	{
		return DNView::G()->assignViewData($key,$value);
	}
	//config
	public static function Setting($key)
	{
		return DNConfiger::G()->_Setting($key);
	}
	public static function Config($key,$file_basename='config')
	{
		return DNConfiger::G()->_Config($key,$file_basename);
	}
	public static function LoadConfig($file_basename)
	{
		return DNConfiger::G()->_LoadConfig($file_basename);
	}
	
	//exception manager
	public function assignExceptionHandler($classes,$callback=null)
	{
		return DNExceptionManager::G()->assignExceptionHandler($classes,$callback);
	}
	public function setMultiExceptionHandler(array $classes,$callback)
	{
		return DNExceptionManager::G()->setMultiExceptionHandler($classes,$callback);
	}
	public function setDefaultExceptionHandler($callback)
	{
		return DNExceptionManager::G()->setDefaultExceptionHandler($callback);
	}
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		throw new DNException($message,$code);
	}

	public static function DB($tag=null)
	{
		return DNDBManager::G()->_DB($tag);
	}
	public static function DB_W()
	{
		return DNDBManager::G()->_DB_W();
	}
	public static function DB_R()
	{
		return DNDBManager::G()->_DB_R();
	}
	public static function Import($file)
	{
		return static::G()->_Import($file);
	}
	public static function DI($name,$object=null)
	{
		return DNMVCSExt::G()->_DI($name,$object);
	}
	public function assignPathNamespace($path,$namespace=null)
	{
		return DNAutoLoader::G()->assignPathNamespace($path,$namespace);
	}
	public static function Platform()
	{
		return static::G()->platform;
	}
	public static function Developing()
	{
		return static::G()->isDev;
	}
	public static function InSwoole()
	{
		if(PHP_SAPI!=='cli'){ return false; }
		if(!class_exists('Swoole\Coroutine')){ return false; }
		
		$cid = \Swoole\Coroutine::getuid();
		if($cid<=0){return false;}
		
		return true;
	}
	public static function IsRunning()
	{
		return DNRuntimeState::G()->isRunning();
	}
	public static function SG()
	{
		return DNSuperGlobal::G();
	}
	public static function &GLOBALS($k,$v=null)
	{
		return DNSuperGlobal::G()->_GLOBALS($k,$v);
	}
	
	public static function &STATICS($k,$v=null)
	{
		return DNSuperGlobal::G()->_STATICS($k,$v,1);
	}
	public static function &CLASS_STATICS($class_name,$var_name)
	{
		return DNSuperGlobal::G()->_CLASS_STATICS($class_name,$var_name);
	}
}
trait DNMVCS_Misc
{
	public static function H($str)
	{
		return static::G()->_H($str);
	}
	public function _Import($file)
	{
		$file=rtrim($file,'.php').'.php';
		require_once($this->path_lib.$file);
	}
	
	public function _H(&$str)
	{
		if(is_string($str)){
			$str=htmlspecialchars( $str, ENT_QUOTES );
			return $str;
		}
		if(is_array($str)){
			foreach($str as $k =>&$v){
				self::_H($v);
			}
			return $str;
		}
		
		if(is_object($str)){
			$arr=get_object_vars($str);
			foreach($arr as $k =>&$v){
				self::_H($v);
			}
			return $arr;
		}

		return $str;
	}
	public static function RecordsetUrl(&$data,$cols_map=[])
	{
		return DNMVCSExt::G()->_RecordsetUrl($data,$cols_map);
	}
	
	public static function RecordsetH(&$data,$cols=[])
	{
		return DNMVCSExt::G()->_RecordsetH($data,$cols);
	}
}
trait DNMVCS_Handler
{
	protected $stop_show_404=false;
	protected $stop_show_exception=false;
	public static function OnBeforeShow($data,$view=null)
	{
		return static::G()->onBeforeShowHandler($data,$view);
	}
	public static function On404()
	{
		return static::G()->_On404();
	}
	public static function OnException($ex)
	{
		return static::G()->_OnException($ex);
	}
	public function OnDevErrorHandler($errno, $errstr, $errfile, $errline)
	{
		return static::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
	}
	//////////////
	public function toggleStop404Handler($flag=true)
	{
		$this->stop_show_404=$flag;
	}
	public function toggleStopExceptionHandler($flag=true)
	{
		$this->stop_show_exception=$flag;
	}
	
	public function onBeforeShowHandler($data,$view=null)
	{
		if($view===null){
			DNView::G()->view=DNRoute::G()->getRouteCallingPath();
		}
		//  close database before show;
		DNDBManager::G()->closeAllDB();
		if($this->options['view_skip_notice_error']){
			DNRuntimeState::G()->skipNoticeError();
		}
	}
	public function _On404()
	{
		if($this->stop_show_404){return;}
		
		$error_view=$this->options['error_404'];
		static::header('',true,404);
		
		if( !is_string($error_view) && is_callable($error_view) ){
			($error_view)($data);
			return;
		}
		if(!$error_view){
			echo "404 File Not Found\n<!--DNMVCS -->\n";
			return;
		}
		
		$view=DNView::G();
		$view->setViewWrapper(null,null);
		$view->_Show([],$error_view);
	}
	
	public function _OnException($ex)
	{
		$flag=DNExceptionManager::G()->checkAndRunErrorHandlers($ex,true);
		if($flag){return;}
		if($this->stop_show_exception){return;}
		
		static::header('',true,500);
		$view=DNView::G();
		
		$data=[];
		$data['is_developing']=static::Developing();
		$data['ex']=$ex;
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['trace']=$ex->getTraceAsString();
		
		$is_error=is_a($ex,'Error') || is_a($ex,'ErrorException')?true:false;
		if($this->options){
			$error_view=$is_error?$this->options['error_500']:$this->options['error_exception'];
		}else{
			$error_view=null;
		}
		if( !is_string($error_view) && is_callable($error_view) ){
			($error_view)($data);
			return;
		}
		if(!$error_view){
			$desc=$is_error?'Error':'Exception';
			echo "Internal $desc \n<!--DNMVCS -->\n";
			if($this->isDev){
				echo "<hr />";
				echo "\n<pre>Debug On\n\n";
				echo $data['trace'];
				echo "\n</pre>\n";
			}
			return;
		}
		
		$view->setViewWrapper(null,null);
		$view->_Show($data,$error_view);
		DNRuntimeState::G()->end();
	}
	public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline)
	{
		//
		if(!$this->isDev){return;}
		$descs=array(
			E_USER_NOTICE=>'E_USER_NOTICE',
			E_NOTICE=>'E_NOTICE',
			E_STRICT=>'E_STRICT',
			E_DEPRECATED=>'E_DEPRECATED',
			E_USER_DEPRECATED=>'E_USER_DEPRECATED',
		);
		$error_shortfile=(substr($errfile,0,strlen($this->path))==$this->path)?substr($errfile,strlen($this->path)):$errfile;
		$data=array(
			'errno'=>$errno,
			'errstr'=>$errstr, 
			'errfile'=>$errfile, 
			'errline'=>$errline,
			'error_desc'=>$descs[$errno],
			'error_shortfile'=>$error_shortfile,
		);
		$error_view=$this->options['error_debug'];
		if( !is_string($error_view) && is_callable($error_view) ){
			($error_view)($data);
			return;
		}
		if(!$error_view){
			extract($data);
			echo  <<<EOT
<!--DNMVCS  use view/_sys/error-debug.php to override me -->
<fieldset class="_DNMVC_DEBUG">
	<legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
</pre>
</fieldset>

EOT;
			return;
		}
		DNView::G()->_ShowBlock($error_view,$data);
	}
}

trait DNMVCS_SystemWrapper
{
	public $header_handler=null;
	public $cookie_handler=null;
	public $exit_handler=null;
	public $exception_handler=null;
	public $shutdown_handler=null;

	public static function header($output ,bool $replace = true , int $http_response_code=0)
	{
		return static::G()->_header($output,$replace,$http_response_code);
	}
	public static function setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		return static::G()->_setcookie($key,$value,$expire,$path,$domain,$secure,$httponly);
	}
	public static function exit_system($code=0)
	{
		return static::G()->_exit_system($code);
	}
	
	public static function set_exception_handler(callable $exception_handler)
	{
		return static::G()->_set_exception_handler($exception_handler);
	}
	public static function register_shutdown_function(callable $callback,...$args)
	{
		return static::G()->_register_shutdown_function($callback,...$args);
	}
	
	public static function session_start(array $options=[])
	{
		return DNSuperGlobal::G()->session_start($options);
	}
	public static function session_destroy()
	{
		return DNSuperGlobal::G()->session_destroy();
	}
	public static function session_set_save_handler(\SessionHandlerInterface $handler)
	{
		return DNSuperGlobal::G()->session_set_save_handler($handler);
	}
	
	public function _header($output ,bool $replace = true , int $http_response_code=0)
	{
		if($this->header_handler){
			return ($this->header_handler)($output,$replace,$http_response_code);
		}
		if(PHP_SAPI==='cli'){ return; }
		if(headers_sent()){ return; }
		return header($output,$replace,$http_response_code);
	}
	
	public function _setcookie(string $key, string $value = '', int $expire = 0 , string $path = '/', string $domain  = '', bool $secure = false , bool $httponly = false)
	{
		if($this->cookie_handler){
			return ($this->cookie_handler)($key,$value,$expire,$path,$domain,$secure,$httponly);
		}
		return setcookie($key,$value,$expire,$path,$domain,$secure,$httponly);
	}
	public function _exit_system($code=0)
	{
		if($this->exit_handler){
			return ($this->exit_handler)($code);
		}
		exit($code);
	}
	public function _set_exception_handler(callable $exception_handler)
	{
		if($this->exception_handler){
			return ($this->exception_handler)($exception_handler);
		}
		return set_exception_handler($exception_handler);
	}
	public function _register_shutdown_function(callable $callback,...$args)
	{
		if($this->shutdown_handler){
			return ($this->shutdown_handler)($callback,...$args);
		}
		return register_shutdown_function($callback,...$args);
	}
	public function system_wrapper_replace(array $funcs=[])
	{
		if(isset($funcs['header'])){ $this->header_handler=$funcs['header']; }
		if(isset($funcs['setcookie'])){ $this->cookie_handler=$funcs['setcookie']; }
		if(isset($funcs['exit_system'])){ $this->exit_handler=$funcs['exit_system']; }
		if(isset($funcs['set_exception_handler'])){ $this->exception_handler=$funcs['set_exception_handler']; }
		if(isset($funcs['register_shutdown_function'])){ $this->shutdown_handler=$funcs['register_shutdown_function']; }
		
		return true;
	}
	public static function system_wrapper_get_providers():array
	{
		$ret=[
			'header'				=>[static::class,'header'],
			'setcookie'				=>[static::class,'setcookie'],
			'exit_system'			=>[static::class,'exit_system'],
			'set_exception_handler'	=>[static::class,'set_exception_handler'],
			'register_shutdown_function' =>[static::class,'register_shutdown_function'],
			
			'super_global' =>[DNSuperGloabl::class,'G'],
		];
		return $ret;
	}
}
trait DNMVCS_RunMode
{
	public static function RunWithoutPathInfo($options=[])
	{
		$default_options=[
			'ext'=>[
				'mode_onefile'=>true,
				'mode_onefile_key_for_action'=>'_r',
			],
		];
		$options=array_replace_recursive($default_options,$options);
		return static::G()->init($options)->run();
	}
	public static function RunOneFileMode($options=[],$init_function=null)
	{
		$path=realpath(getcwd().'/');
		$default_options=[
			'path'=>$path,
			'setting_file_basename'=>'',
			'base_class'=>'',
			'ext'=>[
				'mode_onefile'=>true,
				'mode_onefile_key_for_action'=>'act',
				
				'use_function_dispatch'=>true,
				'use_function_view'=>true,
				
				'use_session_auto_start'=>true,
			]
		];
		$options=array_replace_recursive($default_options,$options);
		static::G()->init($options);
		if($init_function){
			($init_function)();
		}
		return static::G()->run();
	}
	public static function RunAsServer($dn_options,$server=null)
	{
		$dn_options['swoole']['swoole_server']=$server;
		return static::G()->init($dn_options)->run();
	}
}
trait DNMVCS_Instance
{
	public function getBootInstances()
	{
		$ret=[
			DNAutoLoader::class => DNAutoLoader::G(),
			DNMVCS::class => DNMVCS::G(),
		];
		$ret[static::class]=$this;
		return $ret;
	}
	protected $dynamicClasses=[];
	protected $dynamicClassesInited=false;

	protected function initDynamicClasses()
	{
		$this->dynamicClasses=[
			DNRoute::class,   	// for bindServerData,and $this->path_info ,and so on
			DNView::class,   	// for assign
			DNSuperGlobal::class,
		];
	}
	public function getDynamicClasses()
	{
		if($this->dynamicClassesInited){
			$this->dynamicClassesInited=true;
			$this->initDynamicClasses();
		}
		return $this->dynamicClasses;
	}
}
class DNMVCS
{
	const VERSION = '1.0.11';
	
	use DNSingleton;
	
	use DNMVCS_Glue;
	use DNMVCS_Handler;
	use DNMVCS_Misc;
	use DNMVCS_SystemWrapper;
	use DNMVCS_RunMode;
	use DNMVCS_Instance;
	use DNClassExt;
	
	const DEFAULT_OPTIONS=[
			'namespace'=>'MY',
			'base_class'=>'Base\App',
			'path_view'=>'view',
			'path_config'=>'config',
			'path_lib'=>'lib',
			'is_dev'=>false,
			'platform'=>'',
			
			'view_skip_notice_error'=>true,
			'with_cli_cache_classes'=>true,
			
			'all_config'=>[],
			'setting'=>[],
			'setting_file_basename'=>'setting',
			
			'use_db'=>true,
			'db_create_handler'=>'',
			'db_close_handler'=>'',
			'database_list'=>[],
			
			'rewrite_map'=>[],
			'route_map'=>[],
			
			'error_404'=>'_sys/error-404',
			'error_500'=>'_sys/error-500',
			'error_exception'=>'_sys/error-exception',
			'error_debug'=>'_sys/error-debug',
			
			'ext'=>[],
			'swoole'=>[],
/*
'path'=>null,
			'path'=>null,
			
			'namespace'=>'MY',
			'with_no_namespace_mode'=>true,
			'path_namespace'=>'app',
			
			'path_no_namespace_mode'=>'app',
			
			'skip_system_autoload'=>false,
			'skip_app_autoload'=>false,
			
			'namespace'=>'MY',
			'with_no_namespace_mode'=>true,
			
			'namespace_controller'=>'Controller',
			'path_controller'=>'app/Controller',
			
			'enable_paramters'=>false,
			'disable_default_class_outside'=>false,
			
			'base_controller_class'=>null,
			'prefix_no_namespace_mode'=>'',
			'lazy_controller_class'=>'DNController',
			
			'enable_post_prefix'=>true,
			'prefix_post'=>'do_',
			'default_method_for_miss'=>null,
			
			'welcome_controller'=>'Main',
			'default_method'=>'index',
			
			'the_404_hanlder'=>null,
//*/
		];
	public $options=[];
	public $isDev=false;
	public $platform='';
	
	protected $path=null;
	protected $path_lib=null;
	
	protected $has_run_once=false;
	public $before_run_handler=null;
	
	public static function RunQuickly(array $options=[],callable $after_init=null)
	{
		if(!$after_init){
			return static::G()->init($options)->run();
		}
		static::G()->init($options);
		($after_init)();
		static::G()->run();
	}
	protected function mergeOptions($options=[])
	{
		$autoloader_options=DNAutoLoader::G()->options;
		return array_replace_recursive(DNAutoLoader::DEFAULT_OPTIONS,DNRoute::DEFAULT_OPTIONS,static::DEFAULT_OPTIONS,$options,$autoloader_options);
	}
	protected function initOptions($options)
	{
		$this->options=$this->mergeOptions($options);
		
		$this->path=$this->options['path'];
		$this->path_lib=$this->path.rtrim($this->options['path_lib'],'/').'/';
		
		$this->isDev=$this->options['is_dev'];
		$this->platform=$this->options['platform'];
	}
	protected function checkOverride($options)
	{
		$base_class=isset($options['base_class'])?$options['base_class']:self::DEFAULT_OPTIONS['base_class'];
		$namespace=isset($options['namespace'])?$options['namespace']:self::DEFAULT_OPTIONS['namespace'];
		
		if(substr($base_class,0,1)!=='\\'){
			$base_class=$namespace.'\\'.$base_class;
		}
		$base_class=ltrim($base_class,'\\');
		
		if(!$base_class || !class_exists($base_class)){
			return null;
		}
		if(static::class===$base_class){
			return null;
		}
		return static::G($base_class::G())->initAfterOverride($options);
	}
	protected function initSwoole($options)
	{
		if(empty($options['swoole'])){
			return;
		}
		static::ThrowOn(!class_exists(SwooleHttpd::class),"DNMVCS: You Need SwooleHttpd");
		DNSwooleExt::Server(SwooleHttpd::G());
		DNSwooleExt::G()->onAppBoot(self::class,$options);
		$this->toggleStop404Handler();
		
	}
	//@override me
	public function init($options=[])
	{
		DNAutoLoader::G()->init($options)->run();
		
		$object=$this->checkOverride($options);
		if($object){return $object;}
		return $this->initAfterOverride($options);
	}
	protected function initAfterOverride($options)
	{
		$this->initSwoole($options);
		
		$this->initOptions($options);
		$this->initExceptionManager(DNExceptionManager::G());
		$this->initConfiger(DNConfiger::G());
		$this->initView(DNView::G());
		$this->initRoute(DNRoute::G());
		
		$this->initDBManager(DNDBManager::G());
		$this->initSystemWrapper();
		$this->initMisc();
		
		return $this;
	}
	public function initExceptionManager($exception_manager)
	{
		$exception_manager->init([static::class,'OnException'],[static::class,'OnDevErrorHandler'],[static::class,'set_exception_handler']);
	}
	public function initConfiger($configer)
	{
		$path=$this->path.rtrim($this->options['path_config'],'/').'/';
		$configer->init($path,$this->options);
		
		$this->isDev=DNConfiger::G()->_Setting('is_dev')??$this->isDev;
		$this->platform=DNConfiger::G()->_Setting('platform')??$this->platform;
	}
	public function initView($view)
	{
		$path_view=$this->path.rtrim($this->options['path_view'],'/').'/';
		$view->init($path_view);
		$view->setBeforeShowHandler([static::class,'OnBeforeShow']);
	}
	public function initRoute(DNRoute $route)
	{
		$route->init($this->options);
		$route->set404([static::class,'On404']);
	}
	public function initDBManager($dbm)
	{
		if(!$this->options['use_db']){ return; }
		$configer=DNConfiger::G();
		$setting_key_of_database_list=$this->options['setting_key_of_database_list']??'database_list';
		$database_list=$configer->_Setting($setting_key_of_database_list);
		$database_list=$database_list??[];
		$database_list=array_merge($this->options['database_list'],$database_list);
		
		if(empty($database_list)){return;}
		
		$dbm->init($database_list);
		
		$db_create_handler=$this->options['db_create_handler']?:[DB::class,'CreateDBInstance'];
		$db_close_handler=$this->options['db_close_handler']?:[DB::class,'CloseDBInstance'];
		$dbm->setDBHandler($db_create_handler,$db_close_handler);
	}
	public function initMisc()
	{
		if($this->options['with_cli_cache_classes'] && PHP_SAPI==='cli'){
			DNAutoLoader::G()->cacheClasses();
		}
		
		if(!empty($this->options['ext'])){
			DNMVCSExt::G()->init($this);
		}
		
	}
	protected function initSystemWrapper()
	{
		if(!defined('DNMVCS_SYSTEM_WRAPPER_INSTALLER')){
			return;
		}
		$callback=DNMVCS_SYSTEM_WRAPPER_INSTALLER;
		$funcs=($callback)();
		$this->system_wrapper_replace($funcs);
		
		if(isset($funcs['set_exception_handler'])){
			static::set_exception_handler([static::class,'OnException']); //install oexcpetion again;
		}
	}
	protected function runOnce()
	{
		if( $this->options['rewrite_map'] || $this->options['route_map'] ){
			DNMVCSExt::G()->dealMapAndRewrite($this->options['rewrite_map'],$this->options['route_map']);
		}
		if(!empty($this->options['swoole'])){
			DNSwooleExt::G()->onAppBeforeRun();
		}
	}
	public function run($is_stop_404=false)
	{
		if(!$this->has_run_once){
			$this->has_run_once=true;
			$this->runOnce();
		}
		$this->toggleStop404Handler($is_stop_404);
		
		if(defined('DNMVCS_SUPER_GLOBAL_REPALACER')){
			$func=DNMVCS_SUPER_GLOBAL_REPALACER;
			DNSuperGlobal::G($func());
		}
		DNSuperGlobal::G()->init();
		
		$class=get_class(DNRuntimeState::G());  //recreate
		DNRuntimeState::G(new $class)->begin();
		
		$route=DNRoute::G();
		if(true){
			$route->bindServerData(DNSuperGlobal::G()->_SERVER);
		}
		$ret=$route->run();
		DNRuntimeState::G()->end();
		return $ret;
	}
}
