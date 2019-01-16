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
if(!trait_exists('DNMVCS\DNDI',false)){
trait DNDI
{
	protected $_di_container;
	public static function DI($name,$object=null)
	{
		return static::G()->_DI($name,$object);
	}
	public function _DI($name,$object=null)
	{
		if(null===$object){
			return $this->_di_container[$name];
		}
		$this->_di_container[$name]=$object;
		return $object;
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
			'path_namespace'=>'app',
			
			'with_no_namespace_mode'=>true,
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
		spl_autoload_register(function($class){
			$flag=$this->loadByPath($class);
			if($flag){return;}
			$flag=$this->loadWithNoNameSpace($class);
			if($flag){return;}
		});
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
}

class DNRoute
{
	use DNSingleton;
	
	const DEFAULT_OPTIONS=[
			'path'=>null,
			'enable_paramters'=>false,
			'with_no_namespace_mode'=>true,
			'prefix_no_namespace_mode'=>'',
			'path_controller'=>'app/Controller',
			'namespace'=>'MY',
			'namespace_controller'=>'Controller',
			'default_controller_class'=>'DNController',
			
			'enable_post_prefix'=>true,
			'prefix_post'=>'do_',
			'disable_default_class_outside'=>false,
		];
	
	public $options=[];
	public $parameters=[];
	public $the404Handler=null;
	public $urlHandler=null;
	
	
	
	protected $welcome_controller='Main';
	protected $default_method='index';
	
	protected $enable_paramters=false;
	protected $with_no_namespace_mode=true;
	protected $prefix_no_namespace_mode='';
	protected $namespace_controller='';
	protected $default_controller_class='DNController';
	protected $enable_post_prefix=true;
	protected $disable_default_class_outside=false;
	protected $prefix_post='do_';
	
	protected $path;
	
	public $calling_path='';
	public $calling_class='';
	public $calling_method='';
	
	public $path_info='';
	public $request_method='';
	public $script_filename='';
	public $doucment_root='';

	
	public $routeHooks=[];
	public $callback=null;
	public $is_server_data_load=false;
	
	public function _URL($url=null)
	{
		if($this->urlHandler){return ($this->urlHandler)($url);}
		return $this->defaultURLHandler($url);
	}
	public function defaultURLHandler($url=null)
	{
		$basepath=substr(rtrim(str_replace('\\','/',$this->script_filename),'/').'/',strlen($this->document_root));

		if($basepath=='/index.php'){$basepath='/';}
		if($basepath=='/index.php/'){$basepath='/';}
		
		if(''===$url){return $basepath;}

		if('/'==$url{0}){ return $url;};
		
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
		
		$this->default_controller_class=$options['default_controller_class'];
		
		$this->enable_post_prefix=$options['enable_post_prefix'];
		$this->prefix_post=$options['prefix_post'];
		$this->disable_default_class_outside=$options['disable_default_class_outside'];


		
		$namespace=$options['namespace'];
		$namespace_controller=$options['namespace_controller'];
		if(substr($namespace_controller,0,1)!=='\\'){
			$namespace_controller=$namespace.'\\'.$namespace_controller;
		}
		$namespace_controller=ltrim($namespace_controller,'\\');
		$this->namespace_controller=$namespace_controller;
		
		$this->is_server_data_load=false;
	}
	public function loadServerData()
	{
		$this->script_filename=$_SERVER['SCRIPT_FILENAME']??'';
		$this->document_root=$_SERVER['DOCUMENT_ROOT']??'';
		if(PHP_SAPI==='cli'){
			$argv=$_SERVER['argv']??[];
			if(count($argv)>=2){
				$this->path_info=$argv[1];
				array_shift($argv);
				array_shift($argv);
				$this->parameters=$argv;
			}
		}else{
			$this->path_info=$_SERVER['PATH_INFO']??'';
			$this->request_method=$_SERVER['REQUEST_METHOD']??'GET';
		}
		$this->is_server_data_load=true;
	}
	public function set404($callback)
	{
		$this->the404Handler=$callback;
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
		if(!$this->is_server_data_load){
			$this->loadServerData();
		}
		foreach($this->routeHooks as $hook){
			($hook)($this);
		}
		if(null===$this->callback){
			$this->callback=$this->getRouteHandlerByFile();
		}
		if(null!==$this->callback){
			($this->callback)(...$this->parameters);
			return true;
		}
		if(!$this->the404Handler){
			header("HTTP/1.0 404 Not Found");
			echo "404 File Not Found.\n";
			echo "DNRoute Notice: 404 .  You need set 404 Handler by DNRoute->set404(\$callback).";
			exit;
		}
		($this->the404Handler)();
		return false;
	}
	public function stopRunDefaultHandler()
	{
		$this->callback=function(){};
	}
	protected function getRouteHandlerByFile()
	{
		list($current_class,$method)=$this->getCurrentClassAndMethod($this->path_info,$this->path);
		if($current_class===null and $method===null){return null;}
		
		if($this->enable_paramters){
			$blocks=explode('/',$this->path_info);
			$param=array_slice($blocks,count(explode('/',$current_class))+($current_class?1:0));
			if($param==array(0=>'')){$param=[];}
			$this->parameters=$param;
			
			$this->calling_path=ltrim($current_class.'/'.$method,'/');
		}else{
			$this->calling_path=trim($current_class.'/'.$method,'/');
			$t_path_info=trim($this->path_info,'/');
			if($t_path_info!=$this->calling_path){
				return null;
			}
		}
		
		if($this->disable_default_class_outside && $current_class===$this->welcome_controller && $method===$this->default_method){
			return null;
		}
		$method=$method?:$this->default_method;
		$current_class=$current_class?:$this->welcome_controller;
		
		$this->calling_method=$method;
		$this->calling_class=$current_class;
		
		$file=$this->path.$current_class.'.php';
		$this->includeControllerFile($file);
		$obj=$this->getObecjectToCall($current_class);

		if(null==$obj){return null;}
		
		$this->calling_method=$method;
		$this->calling_class=$this->calling_class?:get_class($obj);
		
		return $this->getMethodToCall($obj,$method);
	}
	
	protected function getCurrentClassAndMethod($path_info,$path)
	{
		$path_info=ltrim($path_info,'/');
		if(substr($path_info,0,1)==='~'){ return [null,null]; }
		
		$blocks=explode('/',$path_info);
		
		$prefix=$path;
		//array_shift($blocks);
		$l=count($blocks);
		$current_class='';
		$method='';
		
		for($i=0;$i<$l;$i++){
			$v=$blocks[$i];
			$method=$v;
			if(''  ===$v){ break;}
			if('.' ===$v){ return [null,null]; }
			if('..'===$v){ return [null,null]; }
			///if(!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',$v)){ //just for php classname;
			///	return null;
			///}
			$dir=$prefix.$v;
			$full_file=$dir.'.php';
			if(is_file($full_file)){
				$current_class=implode('/',array_slice($blocks,0,$i+1));
				$method=isset($blocks[$i+1])?$blocks[$i+1]:'';
			}
			if(is_dir($dir)){
				$prefix.=$v.'/';
				continue;
			}
			break;
		}
		return [$current_class,$method];
	}

	// You can override it; variable indived
	protected function includeControllerFile($file)
	{
		require_once($file);
	}
	// You can override it;
	protected function getObecjectToCall($class_name)
	{
		if(substr(basename($class_name),0,1)=='_'){return null;}
		if($this->with_no_namespace_mode){
			$fullclass=$this->prefix_no_namespace_mode . str_replace('/','__',$class_name);
			$flag=class_exists($fullclass,false);
			if(!$flag){
				if(!$this->default_controller_class){return null;}
				$fullclass=str_replace('/','__',$this->default_controller_class);
			}
			$flag=class_exists($fullclass,false);
			if($flag){
				$this->calling_class=$fullclass;
				$obj=new $fullclass();
				return $obj;
			}
		}
		$fullclass=$this->namespace_controller.'\\'.str_replace('/','\\',$class_name);
		$flag=class_exists($fullclass,false);
		if(!$flag){
			if(!$this->default_controller_class){return null;}
			$fullclass=$this->namespace_controller.'\\'.str_replace('/','\\',$this->default_controller_class);
		}
		$this->calling_class=$fullclass;
		$obj=new $fullclass();
		return $obj;
	}
	protected function getMethodToCall($obj,$method)
	{
		if(substr($method,0,2)=='__'){return null;}
		if($this->request_method==='POST'){
			if($this->enable_post_prefix && method_exists($obj,$this->prefix_post.$method)){
				$method=$this->prefix_post.$method;
			}else if(!method_exists($obj,$method)){
				return null;
			}
		}else{
			if(!method_exists($obj,$method)){
				return null;
			}
		}
		if(!is_callable(array($obj,$method))){
			return null;
		}
		return array($obj,$method);
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
	
	protected $temp_view_file;
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
	protected $inited=false;
	public function init($path,$options)
	{
		$this->path=$path;
		$this->setting=$options['setting']??[];
		$this->all_config=$options['all_config']??[];
		$this->setting_file_basename=$options['setting_file_basename']??'setting';
	}
	public function _Setting($key)
	{
		if($this->inited || !$this->setting_file_basename){ return $this->setting[$key]??null; }
		$basename=$this->setting_file_basename;
		$full_config_file=$this->path.$basename.'.php';
		if(!is_file($full_config_file)){
			echo '<h1>'.'DNMVCS Fatal: no setting file['.$full_config_file.']!,change '.$basename.'.sample.php to '.$basename.'.php !'.'</h1>';
			exit;
		}
		$this->setting=$this->loadFile($basename,false);
		$this->inited=true;
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
	public function setState()
	{
		$this->is_running=true;
		$this->error_reporting_old=error_reporting();
	}
	public function unsetState()
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

	public function init()
	{	
		$this->_GET		=&$_GET;
		$this->_POST	=&$_POST;
		$this->_REQUEST	=&$_REQUEST;
		$this->_SERVER	=&$_SERVER;
		$this->_ENV		=&$_ENV;
		$this->_COOKIE	=&$_COOKIE;
		$this->_SESSION	=&$_SESSION;
	}
///////////////////////////////
	public function _StartSession(array $options=[])
	{
		if(session_status() !== PHP_SESSION_ACTIVE ){ session_start($options); }
		$this->_SESSION=&$_SESSION;
	}
	public function _DestroySession()
	{
		session_destroy();
		$this->_SESSION=[];
	}
	public function _SetSessionHandler($handler)
	{
		session_set_save_handler($handler);
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
	public static function Developing()
	{
		return static::G()->isDev;
	}
	public static function InSwoole()
	{
		if(defined('DN_SWOOLE_SERVER_RUNNING')){
			return true;
		}
		if(defined('DN_SWOOLE_SERVER_INIT')){
			return true;
		}
		return false;
	}
	public static function IsRunning()
	{
		return DNRuntimeState::G()->isRunning();
	}
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	{
		return DNDBManager::G()->setDBHandler($db_create_handler,$db_close_handler);
	}
	public function setBeforeGetDBHandler($before_get_db_handler)
	{
		return DNDBManager::G()->setBeforeGetDBHandler($before_get_db_handler);
	}
	public static function SG()
	{
		return DNSuperGlobal::G();
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
	public static function HasInclude($file)
	{
		$a=get_included_files();
		return in_array($a,realpath($file))?true:false;
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
	public function init($exception_handler,$dev_error_handler)
	{
		$this->dev_error_handler=$dev_error_handler;
		$this->exception_error_handler=$exception_handler;
		$this->exception_error_handler_init=$exception_handler;
		
		set_error_handler([$this,'on_error_handler']);
		set_exception_handler([$this,'on_exception']);
	}
}
trait DNMVCS_Handler
{
	//  close database before show;
	public function onBeforeShow($data,$view=null)
	{
		if($view===null){
			DNView::G()->view=DNRoute::G()->getRouteCallingPath(); //TODO getRouteCallingClass & Method
		}
		
		DNDBManager::G()->closeAllDB();
		DNRuntimeState::G()->skipNoticeError();
	}
	
	protected function checkUseDefaultErrorView($error_view,$data)
	{
		if(!is_string($error_view) || !$error_view){
			if($error_view){
				($error_view)($data);
			}
			return true;
		}
		return false;
	}
	//@override
	public function onShow404()
	{
		$error_404=$this->options['error_404'];
		
		static::header('',true,404);

		$use_default_view=$this->checkUseDefaultErrorView($error_404,[]);
		if($use_default_view){
			echo "File Not Found\n<!--DNMVCS -->\n";
			return;
		}
		if(!is_string($error_404)){ return; }
		
		$view=DNView::G();
		$view->setViewWrapper(null,null);
		$view->_Show([],$error_404);
	}
	
	public function onException($ex)
	{
		$flag=DNExceptionManager::G()->checkAndRunErrorHandlers($ex,true);
		if($flag){return;}
		
		static::header('',true,500);
		$view=DNView::G();
		
		$data=[];
		$data['is_developing']=static::Developing();
		$data['ex']=$ex;
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['trace']=$ex->getTraceAsString();
		
		$is_error=is_a($ex,'Error') || is_a($ex,'ErrorException')?true:false;		
		$error_view=$is_error?$this->options['error_500']:$this->options['error_exception'];
		$use_default_view=$this->checkUseDefaultErrorView($error_view,$data);
		if($use_default_view){
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
		if(!is_string($error_view)){ return; }
		
		$view->setViewWrapper(null,null);
		$view->_Show($data,$error_view);
	}
	public function onDevErrorHandler($errno, $errstr, $errfile, $errline)
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
		$flag=$this->checkUseDefaultErrorView($error_view,$data);
		if(!$flag){
			extract($data);
			echo  <<<EOT
<!--DNMVCS  use view/_sys/error-debug.php to overrid me -->
<fieldset class="_DNMVC_DEBUG">
	<legend>$error_desc($errno)</legend>
<pre>
$error_shortfile:$errline
$errstr
</pre>
</fieldset>

EOT;
			return;
		}
		if(!is_string($error_view)){ return; }
		DNView::G()->_ShowBlock($error_view,$data);
	}
	//
}

trait DNMVCS_SystemWrapper
{
	
	public $header_handler=null;
	public $cookie_handler=null;
	public $exit_handler=null;

	public static function session_start(array $options=[])
	{
		return DNSuperGlobal::G()->_StartSession($options);
	}
	public static function session_destroy()
	{
		return DNSuperGlobal::G()->_DestroySession();
	}
	public static function session_set_save_handler(\SessionHandlerInterface $handler)
	{
		return DNSuperGlobal::G()->_SetSessionHandler($handler);
	}

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
}

class DNMVCS
{
	const VERSION = '1.0.9';
	
	use DNSingleton;
	
	use DNMVCS_Glue;
	use DNMVCS_Handler;
	use DNMVCS_Misc;
	use DNMVCS_SystemWrapper;
	
	const DEFAULT_OPTIONS=[
			'namespace'=>'MY',
			'base_class'=>'Base\App',
			'path_view'=>'view',
			'path_config'=>'config',
			'path_lib'=>'lib',
			'is_dev'=>false,
			'all_config'=>[],
			'setting'=>[],
			'setting_file_basename'=>'setting',
			
			'db_create_handler'=>'',
			'db_close_handler'=>'',
			'database_list'=>[],
			
			'rewrite_map'=>[],
			'route_map'=>[],
			
			'error_404'=>'_sys/error-404',
			'error_500'=>'_sys/error-500',
			'error_exception'=>'_sys/error-exception',
			'error_debug'=>'_sys/error_debug',
			
			'ext'=>[],
			'swoole'=>[],
		];
	public $options=[];
	public $isDev=false;
	public $before_run_handler=null;
	
	protected $path=null;
	protected $path_lib=null;
	
	protected $error_reporting_old;
	public static function RunQuickly($options=[])
	{
		return static::G()->init($options)->run();
	}
	public static function RunWithoutPathInfo($options=[])
	{
		$default_options=[
			'ext'=>[
				'key_for_action'=>'_r',
			],
			//'path_view'=>'',
		];
		$options=array_replace_recursive($default_options,$options);
		return static::G()->init($options)->run();
	}
	public static function RunOneFileMode($options=[],$init_function=null)
	{
		$default_options=[
			'setting_file_basename'=>'',
			'base_class'=>'',
			'ext'=>[
				'key_for_action'=>'act',
				'use_function_dispatch'=>true,
				'use_function_view'=>true,
				
				'session_auto_start'=>true,
			]
		];
		$options=array_replace_recursive($default_options,$options);
		static::G()->init($options);
		if($init_function){
			($init_function)();
		}
		static::G()->run();
	}
	public static function RunAsServer($server_options,$dn_options,$server=null)
	{
		DNAutoLoader::G()->init($dn_options)->run();
		DNSwooleHttpServer::RunWithServer($server_options,$dn_options,$server);
	}
	protected function initOptions($options=[])
	{
		$this->options=array_merge(DNAutoLoader::DEFAULT_OPTIONS,DNRoute::DEFAULT_OPTIONS,self::DEFAULT_OPTIONS,$options);
		$autoloader_options=DNAutoLoader::G()->options;
		$this->options=array_merge($this->options,$autoloader_options); 
		
		$this->path=$this->options['path'];
		$this->path_lib=$this->path.rtrim($this->options['path_lib'],'/').'/';
		$this->isDev=$this->options['is_dev'];
	}
	protected function checkOverride($options)
	{
		if(static::class!==self::class){return null;}
		
		$base_class=isset($options['base_class'])?$options['base_class']:self::DEFAULT_OPTIONS['base_class'];
		$namespace=isset($options['namespace'])?$options['namespace']:self::DEFAULT_OPTIONS['namespace'];
		
		if(substr($base_class,0,1)!=='\\'){
			$base_class=$namespace.'\\'.$base_class;
		}
		$base_class=ltrim($base_class,'\\');
		
		if(!$base_class || !class_exists($base_class)){return null;}
		return DNMVCS::G($base_class::G())->init($options);
	}
	//@override me
	public function init($options=[])
	{
		DNAutoLoader::G()->init($options)->run();
		$skip_check_override=$options['skip_check_override']??false;
		unset($options['skip_check_override']);
		if(!$skip_check_override){
			$object=$this->checkOverride($options);
			if($object){return $object;}
		}
		
		$this->initOptions($options);
		$this->initExceptionManager(DNExceptionManager::G());
		
		$this->initConfiger(DNConfiger::G());
		$this->initView(DNView::G());
		$this->initRoute(DNRoute::G());
		$this->initDBManager(DNDBManager::G());
		$this->initMisc();
		
		return $this;
	}
	public function initExceptionManager($exception_manager)
	{
		$exception_manager->init([$this,'onException'],[$this,'onDevErrorHandler']);
	}
	public function initConfiger(DNConfiger $configer)
	{
		$path=$this->path.rtrim($this->options['path_config'],'/').'/';
		$configer->init($path,$this->options);
	}
	public function initView(DNView $view)
	{
		$path_view=$this->path.rtrim($this->options['path_view'],'/').'/';
		$view->init($path_view);
		$view->setBeforeShowHandler([$this,'onBeforeShow']);
	}
	public function initRoute(DNRoute $route)
	{
		$route->init($this->options);
		$route->set404([$this,'onShow404']);
	}
	public function initDBManager(DNDBManager $dbm)
	{
		$configer=DNConfiger::G();
		$database_list=$configer->_Setting('database_list');
		$database_list=$database_list??[];
		$database_list=array_merge($this->options['database_list'],$database_list);
		
		if(empty($database_list)){return;}
		
		$dbm->init($database_list);
		
		$db_create_handler=$this->options['db_create_handler']?:[DB::class,'CreateDBInstance'];
		$db_close_handler=$this->options['db_close_handler']?:[DB::class,'CloseDBInstance'];
		$dbm->setDBHandler($db_create_handler,$db_close_handler);
	}
	protected function initMisc()
	{
		$this->isDev=DNConfiger::G()->_Setting('is_dev')??$this->isDev;
		
		if(!empty($this->options['ext'])){
			DNMVCSExt::G()->afterInit($this);
		}
		DNSuperGlobal::G()->init();
	}
	
	public function setBeforeRunHandler($before_run_handler)
	{
		$this->before_run_handler=$before_run_handler;
	}
	public function beforeRouteRun(DNRoute $route)
	{
		if(defined('DNMVCS_DNSUPERGLOBAL_REPALACER')){	
			DNSuperGlobal::G(call_user_func([DNMVCS_DNSUPERGLOBAL_REPALACER,'G']));
		}
		$route->script_filename=DNSuperGlobal::G()->_SERVER['SCRIPT_FILENAME']??'';
		$route->document_root=DNSuperGlobal::G()->_SERVER['DOCUMENT_ROOT']??'';
		$route->request_method=DNSuperGlobal::G()->_SERVER['REQUEST_METHOD']??'';
		$route->path_info=DNSuperGlobal::G()->_SERVER['PATH_INFO']??'';
		
		if(PHP_SAPI==='cli'){
			$argv=DNSuperGlobal::G()->_SERVER['argv']??[];
			if(count($argv)>=2){
				$route->path_info=$argv[1];
				array_shift($argv);
				array_shift($argv);
				$route->parameters=$argv;
			}
		}
		
		if($this->options['rewrite_map'] || $this->options['route_map'] ){
			DNMVCSExt::G()->dealMapAndRewrite($route);
		}
		
		$route->is_server_data_load=true;
	}
	public function run()
	{
		DNRuntimeState::G()->setState();
		
		if($this->before_run_handler){
			($this->before_run_handler)($this);
		}
		$this->beforeRouteRun(DNRoute::G());
		
		$ret=DNRoute::G()->run();
		DNRuntimeState::G()->unsetState();
		return $ret;
	}
}
