<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

trait DNSingleton
{
	public static function G($object=null)
	{
		if(DNSingletonStaticClass::$Replacer!==null){
			return  (DNSingletonStaticClass::$Replacer)(static::class,$object);
		}
		return DNSingletonStaticClass::GetInstance(static::class,$object);
	}
}
final class DNSingletonStaticClass
{
	public static $Replacer=null;
	public static $_instances=[];
	public static function GetInstance($class,$object)
	{
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
	public static function DeleteInstance($class)
	{
		unset(self::$_instances[$class]);
	}
}
class DNAutoLoader
{
	use DNSingleton;
	const DEFAULT_OPTIONS=[
			'namespace'=>'MY',
			
			'path_namespace'=>'app',
			'path_autoload'=>'classes',
			
			'with_no_namespace_mode'=>true,
			'path_no_namespace_mode'=>'app',
		];

	public $path;
	public $options=[];
	protected $namespace;
	protected $is_loaded=false;
	protected $is_inited=false;
	
	protected $path_namespace;
	protected $path_autoload;
	protected $path_no_namespace_mode;
	protected $path_project_share_common;
	protected $with_no_namespace_mode=true;
	

	public function init($options=[])
	{
		if($this->is_inited){return $this;}
		$this->is_inited=true;
		
		$options=array_merge(self::DEFAULT_OPTIONS,$options);
		$this->options=$options;
		
		if(!isset($options['path']) || !$options['path']){
			$path=realpath(getcwd().'/../');
			$options['path']=rtrim($path,'/').'/';
		}
		$this->options['path']=$options['path'];
		$this->path=$this->options['path'];
		
		$this->namespace=$options['namespace'];
		$this->path_namespace=$this->path.rtrim($options['path_namespace'],'/').'/';
		$this->path_autoload=$this->path.rtrim($options['path_autoload'],'/').'/';
		$this->path_no_namespace_mode=$this->path.rtrim($options['path_no_namespace_mode'],'/').'/';
		
		$this->with_no_namespace_mode=$options['with_no_namespace_mode'];
		
		return $this;
	}
	public function run()
	{
		if($this->is_loaded){return;}
		$this->is_loaded=true;
		spl_autoload_register([$this,'autoload']);
	}
	public function autoload($class)
	{
		$this->regist_psr4($class);
		$this->regist_simple_mode($class);
		$this->regist_system($class);
		$this->regist_classes($class);
	}
	protected function regist_psr4($class)
	{
		$prefix = $this->namespace.'\\';
		$base_dir = $this->path_namespace;
		
		if (strncmp($prefix, $class, strlen($prefix)) !== 0) { return; }
		$relative_class = substr($class, strlen($prefix));
		$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
		if (!file_exists($file)) { return; }
		require $file;
	}
	protected function regist_simple_mode($class)
	{
		if(!$this->with_no_namespace_mode){return;}
		if(strpos($class,'\\')!==false){ return; }
		$path_simple=$this->path_no_namespace_mode;
		
		$flag=preg_match('/(Service|Model)$/',$class,$m);
		if(!$flag){return;}
		$file=$path_simple.$m[1].'/'.$class.'.php';
		if (!$file || !file_exists($file)) {return;}
		require $file;
	}
	protected function regist_system($class)
	{
		if(substr($class,0,strlen('DNMVCS\\'))!=='DNMVCS\\'){ return; }
		
		$it = new \DirectoryIterator(__DIR__);
		foreach($it as $v){
			if($v->getExtension()==='php'){
				if($v->getFilename()==='DNMedoo.php'){ continue; }
				require_once($v->getPathname());
			}
		}
	}
	protected function regist_classes($class)
	{
		if(strpos($class,'\\')!==false){ return; }
		$path_autoload=$this->path_autoload;
		$file=$this->path_autoload .$class.'.php';
		if (!file_exists($file)) { return; }
		require $file;
	}
}

class DNRoute
{
	use DNSingleton;
	
	const DEFAULT_OPTIONS=[
			'enable_paramters'=>false,
			'with_no_namespace_mode'=>true,
			
			'path_controller'=>'app/Controller',
			'namespace_controller'=>'MY\Controller',
			'default_controller_class'=>'DNController',
			
			'enable_post_prefix'=>true,
			'disable_default_class_outside'=>false,
		];
	
	public $the404Handler=null;
	public $parameters=[];
	public $options;
	
	protected $namespaceController='';
	
	protected $default_controller_class='DNController';
	
	protected $welcome_controller='Main';
	protected $default_method='index';
	
	public $enable_paramters=false;
	public $with_no_namespace_mode=true;
	
	public $calling_path='';
	public $calling_class='';
	public $calling_method='';
	
	public $path_info='';
	public $request_method='';
	public $script_filename='';
	public $doucment_root='';

	protected $enable_post_prefix=true;
	protected $disable_default_class_outside=false;
	
	public $routeHooks=[];
	public $callback=null;
	
	public $urlHandler=null;
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
		$options=array_merge(self::DEFAULT_OPTIONS,$options);
		$this->options=$options;
		
		$this->path=$options['path'].$options['path_controller'].'/';
		$this->namespaceController=$options['namespace_controller'];
		$this->enable_paramters=$options['enable_paramters'];
		$this->with_no_namespace_mode=$options['with_no_namespace_mode'];
		
		$this->default_controller_class=$options['default_controller_class'];
		
		$this->enable_post_prefix=$options['enable_post_prefix'];
		$this->disable_default_class_outside=$options['disable_default_class_outside'];

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
		$this->path_info=ltrim($this->path_info,'/');
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
	public function addRouteHook($hook,$prepend=false)
	{
		if(!$prepend){
			array_push($this->routeHooks,$hook);
		}else{
			array_unshift($this->routeHooks,$hook);
		}
	}
	public function run()
	{
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
			echo "404 File Not Found.\n";
			echo "DNRoute Notice: 404  You need set 404 Handler";
			exit;
		}
		($this->the404Handler)();
		return false;
	}
	

	protected function getRouteHandlerByFile()
	{
		$path_info=$this->path_info;
		$blocks=explode('/',$path_info);
		//array_shift($blocks);
		$prefix=$this->path;
		$l=count($blocks);
		$current_class='';
		$method='';
		
		for($i=0;$i<$l;$i++){
			$v=$blocks[$i];
			$method=$v;
			if(''===$v){break;}
			if('.'===$v){ return null;}
			if('..'===$v){ return null;}
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
		if($this->enable_paramters){
			$param=array_slice($blocks,count(explode('/',$current_class))+($current_class?1:0));
			if($param==array(0=>'')){$param=[];}
			$this->parameters=$param;
			
			$this->calling_path=ltrim($current_class.'/'.$method,'/');
		}else{
			$this->calling_path=trim($current_class.'/'.$method,'/');
			$x_path_info=trim($path_info,'/');
			if($x_path_info!=$this->calling_path){
				return null;
			}
		}
		
		if($this->disable_default_class_outside && $current_class===$this->welcome_controller && $method===$this->default_method){
			return null;
		}
		$method=$method?:$this->default_method;
		$current_class=$current_class?:$this->welcome_controller;
		
		$this->calling_method=$method;
		
		$file=$this->path.$current_class.'.php';
		$this->includeControllerFile($file);
		$obj=$this->getObecjectToCall($current_class);
		//$this->calling_class=$current_class;  in $obj

		if(null==$obj){return null;}
		
		return $this->getMethodToCall($obj,$method);
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
			$fullclass=str_replace('/','__',$class_name);
			$flag=class_exists($fullclass,false);
			if(!$flag){
				$fullclass=str_replace('/','__',$this->default_controller_class);
			}
			$flag=class_exists($fullclass,false);
			if($flag){
				$this->calling_class=$fullclass;
				$obj=new $fullclass();
				return $obj;
			}
		}
		$fullclass=$this->namespaceController.'\\'.str_replace('/','\\',$class_name);
		$flag=class_exists($fullclass,false);
		if(!$flag){
			$fullclass=$this->namespaceController.'\\'.str_replace('/','\\',$this->default_controller_class);
		}
		$this->calling_class=$fullclass;
		$obj=new $fullclass();
		return $obj;
	}
	protected function getMethodToCall($obj,$method)
	{
		if(substr($method,0,2)=='__'){return null;}
		if($this->request_method==='POST'){
			if($this->enable_post_prefix &&method_exists ($obj,'do_'.$method)){
				$method='do_'.$method;
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
	
	public $error_reporting_old;
	protected $temp_view_file;
	protected $before_show_handler=null;
	protected $header_handler=null;
	public function _ExitJson($ret)
	{
		$this->header('content-type:text/json');
		if($this->before_show_handler){
			($this->before_show_handler)($data,$this->view);
		}
		echo json_encode($ret,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
		exit;
	}
	public function _ExitRedirect($url,$only_in_site=true)
	{
		if($only_in_site && parse_url($url,PHP_URL_HOST)){
			//something  wrong
			exit;
		}
		$this->header('location: '.$url);
		if($this->before_show_handler){
			($this->before_show_handler)($data,$this->view);
		}
		exit;
	}
	public function header($output)
	{
		if($this->header_handler){
			return ($this->header_handler)($output);
		}
		if(PHP_SAPI==='cli'){ return; }
		if(headers_sent()){ return; }
		return header($output);
	}
	public function _Show($data=[],$view)
	{
		$this->view=$view;
		if(isset($this->before_show_handler)){
			($this->before_show_handler)($data,$this->view);
		}
		$this->data=array_merge($this->data,$data);
		$this->view_file=$this->path.rtrim($this->view,'.php').'.php';
		
		$this->error_reporting_old=error_reporting();
		error_reporting(error_reporting() & ~E_NOTICE);
		$this->includeShowFiles();
		error_reporting($this->error_reporting_old);
	}
	
	protected function includeShowFiles()
	{
		extract($this->data);
		if( $this->head_file){
			$this->head_file=rtrim($this->head_file,'.php').'.php';
			include($this->path.$this->head_file);
		}
		include($this->view_file);
		
		if( $this->foot_file){
			$this->foot_file=rtrim($this->foot_file,'.php').'.php';
			include($this->path.$this->foot_file);
		}
	}
	public function init($path)
	{
		$this->path=$path;
	}
	public function onBeforeShow($callback)
	{
		$this->before_show_handler=$callback;
	}
	public function setHeaderHandler($header_handler)
	{
		$this->header_handler=$header_handler;
	}
	public function setViewWrapper($head_file,$foot_file)
	{
		$this->head_file=$head_file;
		$this->foot_file=$foot_file;
	}
	public function showBlock($view,$data)
	{
		$this->temp_view_file=$this->path.$view.'.php';
		$this->error_reporting_old=error_reporting();
		error_reporting($this->error_reporting_old & ~E_NOTICE);
		extract($data);
		include($this->temp_view_file);
		error_reporting($this->error_reporting_old);
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
		if(!is_file($this->path.$basename.'.php')){
			echo '<h1>'.'DNMVCS Fatal: no setting file!,change '.$basename.'.sample.php to '.$basename.'.php !'.'</h1>';
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
class DNDB
{
	public $pdo;
	public $config;
	protected $rowCount;
	
	public function init($config)
	{
		$this->config=$config;
	}
	public static function CreateDBInstance($db_config)
	{
		$class=get_called_class();
		$db=new $class();
		$db->init($db_config);
		return $db;
	}
	public static function CloseDBInstance($db)
	{
		$db->close();
	}
	protected function check_connect()
	{
		if($this->pdo){return;}
		$config=$this->config;
		$this->pdo=new \PDO($config['dsn'], $config['username'], $config['password'],array(\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_ASSOC));
	}

	public function close()
	{
		$this->rowCount=0;
		$this->pdo=null;
	}
	public function quote($string)
	{
		if(is_array($string)){
			array_walk($string,function(&$v,$k){
				$v=is_string($v)?$this->quote($v):(string)$v;
			});
		}
		if(!is_string($string)){return $string;}
		$this->check_connect();
		return $this->pdo->quote($string);
	}
	public function in($array)
	{
		$this->check_connect();
		if(empty($array)){return 'NULL';}
		array_walk($array,function(&$v,$k){
			$v=is_string($v)?$this->quote($v):(string)$v;
		});
		return implode(',',$array);
	}
	public function fetchAll($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		$this->check_connect();
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		
		$ret=$sth->fetchAll();
		return $ret;
	}
	public function fetch($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		$this->check_connect();
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		$ret=$sth->fetch();
		return $ret;
	}
	public function fetchColumn($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		$this->check_connect();
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		$ret=$sth->fetchColumn();
		return $ret;
	}
	public function execQuick($sql,...$args)
	{
		if(count($args)===1 &&is_array($args[0])){$args=$args[0];}
		$this->check_connect();
		
		$sth = $this->pdo->prepare($sql);
		$ret=$sth->execute($args);
		
		$this->rowCount=$sth->rowCount();
		return $ret;
	}
	public function rowCount()
	{
		return $this->rowCount;
	}
}


class DNDBManager
{
	use DNSingleton;
	
	public $db=null;
	public $db_r=null;
	public $db_config=[];
	public $db_r_config=[];
	public $db_create_handler=null;
	public $db_close_handler=null;
	public $use_only_one_db=false;
	public function init($db_config,$db_r_config,$db_create_handler,$db_close_handler)
	{

		$this->db_config=$db_config;
		$this->db_r_config=$db_r_config;
		$this->db_create_handler=$db_create_handler;
		$this->db_close_handler=$db_close_handler;
	}
	public function setDBHandler($db_create_handler,$db_close_handler=null)
	{
		$this->db_create_handler=$db_create_handler;
		$this->db_close_handler=$db_close_handler;
	}
	public function _DB()
	{
		if($this->db){return $this->db;}
		
		$this->db=($this->db_create_handler)($this->db_config,'write');
		return $this->db;
	}
	public function _DB_W()
	{
		return $this->_DB();
	}
	public function _DB_R()
	{
		if($this->db_r){return $this->db_r;}
		
		if(!$this->db_r_config){
			$this->use_only_one_db=true;
			return $this->_DB();
		}
		
		$this->db_r=($this->db_create_handler)($this->db_r_config,'read');
		return $this->db_r;
	}
	public function closeAllDB()
	{
		if($this->db!==null && $this->db_close_handler){
			($this->db_close_handler)($this->db,'write');
			$this->db=null;
		}
		if($this->use_only_one_db){
			$this->db_r=null;
			return;
		}
		if($this->db_r!==null && $this->db_close_handler){
			($this->db_close_handler)($this->db,'read');
			$this->db_r=null;
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
	public function checkAndInstallDefaultRouteHooks($force_install=false)
	{
		// as protected ,but use outside
		if($force_install ||$this->options['rewrite_list'] || $this->options['route_list'] ){
			DNRouteAdvance::G()->install();
		}
	}
	public function assignRewrite($key,$value=null)
	{
		$this->checkAndInstallDefaultRouteHooks();
		if(is_array($key)&& $value===null){
			$this->options['rewrite_list']=array_merge($this->options['rewrite_list'],$key);
		}else{
			$this->options['rewrite_list'][$key]=$value;
		}
	}
	public function assignRoute($key,$value=null)
	{
		$this->checkAndInstallDefaultRouteHooks();
		if(is_array($key)&& $value===null){
			$this->options['route_list']=array_merge($this->options['route_list'],$key);
		}else{
			$this->options['route_list'][$key]=$value;
		}
	}
	public function addRouteHook($hook,$prepend=false)
	{
		return DNRoute::G()->addRouteHook($hook,$prepend);
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
		return DNView::G()->_ExitJson($ret);
	}
	public static function ExitRedirect($url,$only_in_site=true)
	{
		return DNView::G()->_ExitRedirect($url,$only_in_site);
	}
	public static function ExitRouteTo($url)
	{
		return DNView::G()->_ExitRedirect(self::URL($url),true);
	}
	public function setViewWrapper($head_file=null,$foot_file=null)
	{
		return DNView::G()->setViewWrapper($head_file,$foot_file);
	}
	public function showBlock($view,$data)
	{
		return DNView::G()->showBlock($view,$data);
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
	public function setDefaultExceptionHandler($callback)
	{
		return DNExceptionManager::G()->setDefaultExceptionHandler($callback);
	}
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		throw new DNException($message,$code);
	}

	public static function DB()
	{
		return DNDBManager::G()->_DB();
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
		return self::G()->_Import($file);
	}
	public static function DI($name,$object=null)
	{
		return self::G()->_DI($name,$object);
	}
	protected $container;
	public function _DI($name,$object=null)
	{
		if(null===$object){
			return $this->container[$name];
		}
		$this->container[$name]=$object;
		return $object;
	}
}
trait DNMVCS_Misc
{
	public static function H($str)
	{
		return self::G()->_H($str);
	}
	public function _Import($file)
	{
		$file=rtrim($file,'.php').'.php';
		require_once($this->path_lib.$file);
	}
	public static function ImportSys($file=null)
	{
		$file=$file??'DNMVCSExt';
		$file=rtrim($file,'.php').'.php';
		require_once(__DIR__.'/'.$file);
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
		return self::G()->_RecordsetUrl($data,$cols_map);
	}
	public function _RecordsetUrl(&$data,$cols_map=[])
	{
		//need more quickly;
		if($data===[]){return $data;}
		if($cols_map===[]){return $data;}
		$keys=array_keys($data[0]);
		array_walk($keys,function(&$val,$k){$val='{'.$val.'}';});
		foreach($data as &$v){
			foreach($cols_map as $k=>$r){
				$values=array_values($v);
				$v[$k]=self::URL(str_replace($keys,$values,$r));
				
			}
		}
		unset($v);
		return $data;
	}
	public static function RecordsetH(&$data,$cols=[])
	{
		return self::G()->_RecordsetH($data,$cols);
	}
	public static function _RecordsetH(&$data,$cols=[])
	{
		if($data===[]){return $data;}
		$cols=is_array($cols)?$cols:array($cols);
		if($cols===[]){
			$cols=array_keys($data[0]);
		}
		foreach($data as &$v){
			foreach($cols as $k){
				$v[$k]=self::H( $v[$k], ENT_QUOTES );
			}
		}
		return $data;
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
	public function onBeforeShow($data,$view)
	{
		if($view===null){
			DNView::G()->view=DNRoute::G()->getRouteCallingPath();
		}
		
		DNDBManager::G()->closeAllDB();
	}
	
	protected function checkAndRunDefaultErrorHandler($error_view,$data)
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
		
		$view=DNView::G();
		$view->header("HTTP/1.1 404 Not Found");
		
		$flag=$this->checkAndRunDefaultErrorHandler($error_404,[]);
		if(!$flag){
			echo "File Not Found\n<!--DNMVCS -->\n";
			return;
		}
		if(!is_string($error_404)){ return; }
		$view->setViewWrapper(null,null);
		$view->_Show([],$error_404);
	}
	
	public function onException($ex)
	{
		$flag=DNExceptionManager::G()->checkAndRunErrorHandlers($ex,true);
		if($flag){return;}
		
		$view=DNView::G();
		$view->header("HTTP/1.1 500 Internal Error");
		
		$data=[];
		$data['ex']=$ex;
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['trace']=$ex->getTraceAsString();
		
		$is_error=is_a($ex,'Error') || is_a($ex,'ErrorException')?true:false;		
		$error_view=$is_error?$this->options['error_500']:$this->options['error_exception'];
		$this->checkAndRunDefaultErrorHandler($error_view,$data);
		if(!$is_error){
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
		$flag=$this->checkAndRunDefaultErrorHandler($error_view,$data);
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
		DNView::G()->showBlock($error_view,$data);
	}
}
class DNMVCS
{
	use DNSingleton;
	use DNMVCS_Glue;
	use DNMVCS_Handler;
	use DNMVCS_Misc;
	
	const DEFAULT_OPTIONS=[
			'base_class'=>'MY\Base\App',
			'path_view'=>'view',
			'path_config'=>'config',
			'path_lib'=>'lib',
			'use_ext'=>false,
			'is_dev'=>false,
			'all_config'=>[],
			'setting'=>[],
			'setting_file_basename'=>'setting',
			'db_create_handler'=>'',
			'db_close_handler'=>'',
			
			'rewrite_list'=>[],
			'route_list'=>[],
			
			'swoole_mode'=>false,
			'swoole_db_reuse_size'=>0,
			'swoole_db_reuse_timeout'=>5,
			
			'error_404'=>'_sys/error-404',
			'error_500'=>'_sys/error-500',
			'error_exception'=>'_sys/error-exception',
			'error_debug'=>'_sys/error_debug',
		];
	public $options=[];
	public $isDev=false;
	public $before_run_handler=null;
	
	protected $path=null;
	protected $path_lib;
	
	protected $initObLevel=0;
	public static function RunQuickly($options=[])
	{
		return self::G()->init($options)->run();
	}
	public static function RunWithoutPathInfo($options=[])
	{
		$default_options=[
			'ext'=>[
				'key_for_simple_route'=>'_r',
			],
			//'path_view'=>'',
		];
		$options=array_merge_recursive($default_options,$options);
		return self::RunQuickly($options);
	}
	public static function RunOneFileMode($options=[])
	{
		$default_options=[
			'setting_file_basename'=>'',
			'base_class'=>'',
			'ext'=>[
				'key_for_simple_route'=>'act',
				'use_function_dispatch'=>true,
				'use_function_view'=>true,
				
			]
		];
		$options=array_merge_recursive($default_options,$options);
		return self::RunQuickly($options);
	}
	public static function RunAsServer($server_options,$dn_options,$server=null)
	{
		self::ImportSys('DNSwooleHttpServer');
		DNSwooleHttpServer:: RunWithServer($server_options,$dn_options,$server);
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
	
	protected function initExceptionManager()
	{
		DNExceptionManager::G()->init([$this,'onException'],[$this,'onDevErrorHandler']);
	}
	protected function checkOverride($options)
	{
		$self=get_called_class();
		if($self!==self::class){return null;}
		
		$base_class=isset($options['base_class'])?$options['base_class']:self::DEFAULT_OPTIONS['base_class'];
		if(!class_exists($base_class)){return null;}
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
		$this->initExceptionManager();
		
		$this->initConfiger(DNConfiger::G());
		$this->initView(DNView::G());
		$this->initRoute(DNRoute::G());
		$this->initDBManager(DNDBManager::G());
		$this->initMisc();
		
		return $this;
	}
	public function initConfiger($configer)
	{
		$path=$this->path.rtrim($this->options['path_config'],'/').'/';
		$configer->init($path,$this->options);
	}
	public function initView($view)
	{
		$path_view=$this->path.rtrim($this->options['path_view'],'/').'/';
		$view->init($path_view);
		$view->onBeforeShow([$this,'onBeforeShow']);
	}
	public function initRoute($route)
	{
		$route->init($this->options);
		$route->set404([$this,'onShow404']);
	}
	public function initDBManager($dbm)
	{
		$configer=DNConfiger::G();
		$db_config=$configer->_Setting('db');
		$db_r_config=$configer->_Setting('db_r');
		
		$db_create_handler=$this->options['db_create_handler']?:[DNDB::class,'CreateDBInstance'];
		$db_close_handler=$this->options['db_close_handler']?:[DNDB::class,'CloseDBInstance'];
		
		$dbm->init($db_config,$db_r_config,$db_create_handler,$db_close_handler);
	}
	protected function initMisc()
	{
		$this->isDev=DNConfiger::G()->_Setting('is_dev')??$this->isDev;		
		if($this->options['swoole_mode']??false){
			$this->options['ext']['use_super_global']=true;
		}
		
		if(defined('DN_SWOOLE_SERVER_RUNNING')){
			$this->options['ext']['use_super_global']=true;
		}
		
		if(!empty($this->options['ext'])){
			//self::ImportSys();
			AppExt::G()->afterInit($this);
		}
	}
	public function isDev()
	{
		return $this->isDev;
	}
	
	public function onBeforeRun($before_run_handler)
	{
		$this->before_run_handler=$before_run_handler;
	}
	public function run()
	{
		if($this->before_run_handler){
			($this->before_run_handler)();
		}
		
		$ret=DNRoute::G()->run();
		return $ret;
	}
}
/////////////////////////
trait DNThrowQuickly
{
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		$class=get_called_class();
		throw new $class($message,$code);
	}
}
class DNException extends \Exception
{
	use DNThrowQuickly;
}
class DNService
{
	use DNSingleton;
}
class DNModel
{
	use DNSingleton;
}
