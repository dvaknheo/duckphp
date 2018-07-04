<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;
use \PDO;
use \Exception;

trait DNSingleton
{
	protected static function _before_instance($object,$args=[])
	{
		//for override;
	}
	protected static $_instances=[];
	public static function G($object=null,$args=[])
	{
		self::_before_instance($object);
		$class=get_called_class();
		if($object){
			self::$_instances[$class]=$object;
			return $object;
		}
		$me=isset(self::$_instances[$class])?self::$_instances[$class]:null;
		if(null===$me){
			if($args===[]){
				$me=new $class();
			}else{
				$ref=new \ReflectionClass($class);
				$me=$ref->newInstanceArgs($args);
			}
			self::$_instances[$class]=$me;
		}
		return $me;
	}
	
}
class DNAutoLoad
{
	use DNSingleton;
	
	public $path;
	public $options=[];
	protected $namespace;
	protected $is_loaded=false;
	protected $is_inited=false;
	
	protected $path_namespace;
	protected $path_autoload;
	protected $path_framework_simple;
	protected $path_framework_common;
	protected $enable_simple_mode=true;
	

	public function init($options=array())
	{
		$this->is_inited=true;
		
		$default_options=array(
			'path'	=>'',
			'namespace'=>'MY',
			'enable_simple_mode'=>true,
			'path_autoload'=>'classes',
			
			'path_framework_simple'=>'app',
			'path_namespace'=>'app',
			'fullpath_framework_common'=>'',
		);
		$options=array_merge($default_options,$options);
		$this->options=$options;
		
		if(!$options['path']){
			$path=realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/../');
			$options['path']=rtrim($path,'/').'/';
		}
		$this->path=$options['path'];
		$path=$this->path;
		
		$this->namespace=$options['namespace'];
		$this->path_namespace=$path.rtrim($options['path_namespace'],'/').'/';
		$this->path_autoload=$path.rtrim($options['path_autoload'],'/').'/';
		$this->path_framework_simple=$path.rtrim($options['path_framework_simple'],'/').'/';
		
		//remark No the prefix
		$this->path_framework_common=rtrim($options['fullpath_framework_common'],'/').'/';
		
		$this->enable_simple_mode=$options['enable_simple_mode'];
		
		return $this;
	}
	public function isInited()
	{
		return $this->is_inited;
	}
	public function run()
	{
		if($this->is_loaded){return;}
		$this->is_loaded=true;
		$this->regist_psr4();
		if($this->enable_simple_mode){
			$this->regist_simple_mode();
		}
		$this->regist_classes();
	}
	protected function regist_psr4()
	{
		spl_autoload_register(function ($class) {
			$prefix = $this->namespace.'\\';
			$base_dir = $this->path_namespace;
			
			if (strncmp($prefix, $class, strlen($prefix)) !== 0) { return; }
			$relative_class = substr($class, strlen($prefix));
			$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
			if (!file_exists($file)) { return; }
			require $file;
		});
	}
	protected function regist_simple_mode()
	{
		spl_autoload_register(function($class){
			if(strpos($class,'\\')!==false){ return; }
			$path_simple=$this->path_framework_simple;
			$path_common=$this->path_framework_common;
			
			$flag=preg_match('/(Common)?(Service|Model)$/',$class,$m);
			if(!$flag){return;}
			$file='';
			if(!$m[1]){
				$file=$path_simple.$m[2].'/'.$class.'.php';
				
			}else{
				if(!$path_common){return;}
				//ref: if(!$path_common){throw new Exception('CommonService/CommonModel need path_common');} 
				$file=$path_common.strtolower($m[2]).'/'.$class.'.php';
			}
			if (!$file || !file_exists($file)) {return;}
			require $file;
		});
	}
	protected function regist_classes()
	{
		spl_autoload_register(function ($class) {
			if(strpos($class,'\\')!==false){ return; }
			$path_autoload=$this->path_autoload;
			$file=$this->path_autoload .$class.'.php';
			if (!file_exists($file)) { return; }
			require $file;
			
		});
	}
}

class DNRoute
{
	use DNSingleton;
	
	//protected $site=''; //for sites in a controller
	protected $route_handels=[];
	protected $routeMap=[];
	protected $on404Handel;
	protected $params=[];
	public $options;
	
	protected $namespace='MY';
	protected $default_class='DNController';
	
	protected $default_controller='Main';
	protected $default_method='index';
	public $enable_param=true;
	public $enable_simple_mode=true;
	
	public $calling_path='';
	public $calling_class='';
	public $calling_method='';
	
	protected $path_info='';
	protected $request_method='';
	protected $enable_post_prefix=true;
	protected $disable_default_class_outside=false;
	
	protected $simple_route_key=null;
	public function _URL($url=null)
	{
		if($this->simple_route_key!==null){
			//for simple_route_mode
			return $this->_url_simple_mode($url);
		}
		static $basepath; //TODO do not static ?
		if(null===$url){return $_SERVER['REQUEST_URI'];}
		if(''===$url){return $_SERVER['REQUEST_URI'];}
		$url=preg_replace('/^\//','',$url);
		
		if(null===$basepath){
			$basepath=substr(rtrim(str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']),'/').'/',strlen($_SERVER['DOCUMENT_ROOT']));
		}
		
		if($basepath=='/index.php'){$basepath='/';}
		if($basepath=='/index.php/'){$basepath='/';}
		
		if('/'==$url{0}){
			return $url;
		};
		if('?'==$url{0} || '#'==$url{0}){
			return $basepath.$path_info.$url;
		}
		return $basepath.$url;
	}
	protected function _url_simple_mode()
	{
		$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		if($url===null || $url==='' || $url==='/'){return $path;}
		$url='/'.ltrim($url,'/');
		$c=parse_url($url,PHP_URL_PATH);
		$q=parse_url($url,PHP_URL_QUERY);
		
		$q=$q?'&'.$q:''; //TODO if this->route_key= 
		$url=$path.'?'.$this->simple_route_key.'='.$c.$q;
		return $url;
	}
	public function _Param()
	{
		return $this->params;
	}
	public function init($options)
	{
		$default_options=array(
			//'path'=>'',
			'namespace'=>'MY',
			'enable_paramters'=>false,
			'enable_simple_mode'=>true,
			
			'path_contorller'=>'app/Controller',
			'namespace_controller'=>'Controller',
			'default_controller_class'=>'DNController',
		);

		$options=array_merge($default_options,$options);
		$this->options=$options;
		
		
		$this->path=$options['path'].$options['path_contorller'].'/';
		$this->namespace=$options['namespace'].'\\'.$options['namespace_controller'];
		$this->enable_param=$options['enable_paramters'];
		$this->enable_simple_mode=$options['enable_simple_mode'];
		
		$this->default_class=$options['default_controller_class'];
		$this->disable_default_class_outside=isset($options['disable_default_class_outside'])?$options['disable_default_class_outside']:$this->disable_default_class_outside;
		$this->enable_post_prefix=isset($options['enable_post_prefix'])?$options['enable_post_prefix']:$this->enable_post_prefix;
		$this->enable_post_prefix=isset($options['simple_route_key'])?$options['simple_route_key']:$this->simple_route_key;

		if(PHP_SAPI==='cli'){
			$argv=$_SERVER['argv'];
			if(count($argv)>=2){
				$this->path_info='/'.ltrim($argv[1],'/');
				array_shift($argv);
				array_shift($argv);
				$this->params=$argv;
			}
		}else{
			if($this->simple_route_key===null){
				$this->path_info=isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
				$this->request_method=$_SERVER['REQUEST_METHOD'];
			}else{
				$path_info=isset($_GET[$this->simple_route_key])?$_GET[$this->simple_route_key]:'';
				$path_info='/'.ltrim($path_info,'/');
				$this->path_info=$path_info;
			}
		}
		
		array_push($this->route_handels,array($this,'defaltRouteHandle'));
	}
	public function _default404()
	{
		DNSytemExcepion("DNMVCS Notice: 404 , Develop should override this");
	}
	public function set404($callback)
	{
		$this->on404Handel=$callback;
	}
	//for run ,you can 
	protected function getRouteCallback()
	{
		$callback=null;
		foreach($this->route_handels as $handel){
			$callback=$handel();
			if($callback){break;}
		}
		return $callback;
	}
	public function run()
	{
		$callback=$this->getRouteCallback();
		if(null===$callback){
			if(!$this->on404Handel){
				$this->_default404();
				return;
			}
			$t=$this->on404Handel;
			return $t();
		}
		
		return call_user_func_array($callback,$this->params);
	}

	public function defaltRouteHandle()
	{
		$path_info=$this->path_info;
		
		$blocks=explode('/',$path_info);
		array_shift($blocks);
		$prefix=$this->path;
		$l=count($blocks);
		$current_class='';
		$method='';
		
		for($i=0;$i<$l;$i++){
			$v=$blocks[$i];
			$method=$v;
			if(''==$v){break;}
			if(!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',$v)){ //just for php classname;
				return null;
			}
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
		if($this->enable_param){
			$param=array_slice($blocks,count(explode('/',$current_class))+($current_class?1:0));
			if($param==array(0=>'')){$param=array();}
			$this->params=$param;
			
			$this->calling_path=ltrim($current_class.'/'.$method,'/');
		}else{
			$this->calling_path=$path_info;
		}
		
		if($this->disable_default_class_outside && $current_class===$this->default_controller && $method===$this->default_method){
			return null;
		}
		$method=$method?$method:$this->default_method;
		$current_class=$current_class?$current_class:$this->default_controller;
		
		$this->calling_method=$method;
		$this->calling_class=$current_class;
		
		$file=$this->path.$current_class.'.php';
		$this->method_calling=$method;
		
		$this->includeControllerFile($file);
		$obj=$this->getObecjectToCall($current_class);
		
		if(null==$obj){return null;}
		
		return $this->getMethodToCall($obj,$method);
	}
	

	// You can override it; variable indived
	protected function includeControllerFile($file)
	{
		require($file);
	}
	// You can override it;
	protected function getObecjectToCall($class_name)
	{
		if(substr(basename($class_name),0,1)=='_'){return null;}
		if($this->enable_simple_mode){
			$fullclass=str_replace('/','__',$class_name);
			$flag=class_exists($fullclass,false);
			if(!$flag){
				$fullclass=str_replace('/','__',$this->default_class);
			}
			$flag=class_exists($fullclass,false);
			if($flag){
				$this->calling_class=$fullclass;
				$obj=new $fullclass();
				return $obj;
			}
		}
		$fullclass=$this->namespace.'\\'.str_replace('/','\\',$class_name);
		$flag=class_exists($fullclass,false);
		if(!$flag){
			$fullclass=$this->namespace.'\\'.str_replace('/','\\',$this->default_class);
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
	
	public function addDefaultRoute($callback)
	{
		$this->route_handels[]=$callback;
	}

	protected function match_path_info($pattern_url,$path_info)
	{
		$pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
		$flag=preg_match($pattern,$pattern_url,$m);
		if(!$flag){return false;}
		$method=$m[2];
		$is_regex=$m[3];
		$url=$m[4];
		if($method && $method!==$this->request_method){return false;}
		if(!$is_regex){
			//if(enable_param)
			$params=explode('/',$path_info);
			array_shift($params);
			$url_params=explode('/',$url);
			if(!$this->enable_param){
				return ($url_params===$params)?true:false;
			}
			if($url_params === array_slice($params,0,count($url_params))){
				$this->params=array_slice($params,0,count($url_params));
				return true;
			}else{
				return false;
			}
			
		}
		$p='/^\/'.str_replace('/','\/',$url).'/';
		$flag=preg_match($p,$path_info,$m);
		array_shift($m);
		$this->params=$m;
		
		return $flag;
	}
	public function defalt_dispath_handle()
	{
		$path_info=$this->path_info;
		$ret=null;
		foreach($this->routeMap as $pattern =>$callback){
			if($this->match_path_info($pattern,$path_info)){
				if(!is_callable($callback)){
					list($class,$method)=explode('$',$callback);
					$obj=new $class;
					$callback=array($obj,$method);
					//DN::ThrowOn(true,"...for debug");
				}
				$ret=$callback;
			}
			if($ret){break;}
			
		}
		
		return $ret;
	}
	
	public function assignRoute($key,$callback=null)
	{
		if(empty($this->routeMap)){
			array_push($this->route_handels,array($this,'defalt_dispath_handle'));
		}
		if(is_array($key)&& $callback===null){
			$this->routeMap=array_merge($this->routeMap,$key);
		}else{
			$this->routeMap[$key]=$callback;
		}
	}
}

class DNView
{
	use DNSingleton;

	protected $head_file;
	protected $foot_file;
	protected $view_file;
	public $data=array();
	public $onBeforeShow=null;
	public $path;
	public $isDev=false;
	
	public function _ExitJson($ret)
	{
		header('content-type:text/json');
		echo json_encode($ret,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
		exit;
	}
	public function _ExitRedirect($url,$only_in_site=true)
	{
		if($only_in_site && parse_url($url,PHP_URL_HOST)){
			DNSystemException::ThrowOn(true,' DnSystem safe check false');
		}
		header('location: '.$url);
		exit;
	}	
	public function _Show($data=array(),$view)
	{
		if(isset($this->onBeforeShow)){
			($this->onBeforeShow)($view,$data);
		}
		// stop notice 
		error_reporting(error_reporting() & ~E_NOTICE);
		
		$this->data=$this->data?$this->data:array();
		$this->data=array_merge($this->data,$data);
		unset($data);
		//
		$view=rtrim($view,'.php').'.php';
		$this->view_file=$this->path.$view;
		$this->show_include();
	}
	protected function show_include()
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
	public function setBeforeShow($callback)
	{
		$this->onBeforeShow=$callback;
	}
	public function setViewWrapper($head_file,$foot_file)
	{
		$this->head_file=$head_file;
		$this->foot_file=$foot_file;
	}
	public function showBlock($view,$data)
	{
		error_reporting(error_reporting() & ~E_NOTICE);
		extract($data);
		include($this->path.$view.'.php');
	}
	public function assignViewData($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->data=array_merge($this->$data,$key);
		}else{
			$this->data[$key]=$value;
		}
	}

}

class DNConfig
{
	use DNSingleton;

	protected $path;
	protected $path_common;
	
	public function init($path,$path_common=null)
	{
		$this->path=$path;
		$this->path_common=$path_common;
	}
	
	// variable indived
	protected function include_file($file)
	{
		return include($file);
	}
	public function _Setting($key)
	{
		//on file setting;
		static $setting;
		if(isset($setting[$key])){return $setting[$key];}
		if(null===$setting){
			$base_setting=array();
			if($this->path_common){
				$base_setting=$this->include_file($this->path_common.'setting.php');
				$base_setting=is_array($base_setting)?$base_setting:array();
			}
			$setting=$this->include_file($this->path.'setting.php');
			if($setting===false){
				echo '<h1>'.'DNMVCS Notice: no setting file!,change setting.sample.php to setting.php !'.'</h1>';
				exit;
				//throw new \Exception('DNMVCS Notice: no setting file!,change setting.sample.php to setting.php');
			}
			if(!is_array($setting)){
				DNSystemException::ThrowOn(true,'DNMVCS Notice: need return array !');
				exit;
			}
			$setting=array_merge($base_setting,$setting);
		}
		return isset($setting[$key])?$setting[$key]:null;
	}
	
	public function _GetConfig($key,$file_basename='config')
	{
		$config=$this->_Load($file_basename);
		return isset($config[$key])?$config[$key]:null;
	}
	
	public function _LoadConfig($file_basename='config')
	{
		//multi file?
		static $all_config=array();
		if(isset($all_config[$file_basename])){return $all_config[$file_basename];}
		$base_config=array();
		if($this->path_common){
			$base_config=$this->include_file($this->path_common.$file_basename.'.php');
			$base_config=is_array($base_config)?$base_config:array();
		}
		$config=$this->include_file($this->path.$file_basename.'.php');
		$config=array_merge($base_config,$config);
		
		$all_config[$file_basename]=$config;
		return $config;
		
	}
}
class DNDB
{
	
	public $pdo;
	protected $rowCount;
	
	protected $config;
	
	public function init($config)
	{
		$this->config=$config;
	}
	protected function check_connect()
	{
		if($this->pdo){return;}
		if(empty($this->config)){
			DNSystemException::ThrowOn(true,'DNMVCS Notice: database not setting!');
		}
		$config=$this->config;
		$this->pdo= new PDO($config['dsn'], $config['user'], $config['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
	}

	public function close()
	{
		$this->rowCount=0;
		$this->pdo=null;
	}
	public function quote($string)
	{
		$this->check_connect();
		return $this->pdo->quote($string);
	}
	//Warnning, escape the key by yourself
	protected function quote_array($array)
	{
		$this->check_connect();
		$a=array();
		foreach($array as $k =>$v){
			$a[]=$k.'='.$this->pdo->quote($v);
		}
		return implode(',',$a);
	}
	public function fetchAll($sql)
	{
		$this->check_connect();
		$args=func_get_args();
		array_shift($args);
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		
		$ret=$sth->fetchAll(PDO::FETCH_ASSOC);
		return $ret;
	}
	public function fetch($sql)
	{
		$this->check_connect();
		$args=func_get_args();
		array_shift($args);
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		$ret=$sth->fetch(PDO::FETCH_ASSOC);// todo : object mode
		return $ret;
	}
	public function fetchColumn($sql)
	{
		$this->check_connect();
		$args=func_get_args();
		array_shift($args);
		
		$sth = $this->pdo->prepare($sql);
		$sth->execute($args);
		$ret=$sth->fetchColumn();
		return $ret;
	}
	public function execQuick($sql)
	{
		$this->check_connect();
		$args=func_get_args();
		array_shift($args);
		
		$sth = $this->pdo->prepare($sql);
		$ret=$sth->execute($args);
		
		$this->rowCount=$sth->rowCount();
		return $ret;
	}
	public function rowCount()
	{
		return $this->rowCount;
	}
	
	public function insert($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".DNDB::G()->quote_array($data);
		$ret=$this->exec($sql);
		if(!$return_last_id){return $ret;}
		$ret=$this->pdo->lastInsertId();
		return $ret;
	}

}
class DNExceptionManager
{
	public static $is_handeling;
	
	public static $OnErrorException;
	public static $OnException;
	
	public static $OnError;
	public static $OnDevError;
	
	public static $SpecailExceptionMap=array();
	
	public static function HandelAllException($OnErrorException,$OnException)
	{
		self::$is_handeling=true;
		set_exception_handler(array(__CLASS__,'ManageException'));
		
		self::$OnErrorException=$OnErrorException;
		
		self::SetException($OnException);
	}
	public static function AssignExceptionHandel($class,$callback)
	{
		$class=is_string($class)?array($class=>$callback):$class;
		foreach($class as $k=>$v){
			self::$SpecailExceptionMap[$k]=$v;
		}
	}
	public static function SetException($OnException)
	{
		self::$OnException=$OnException;
	}
	public static function ManageException($ex)
	{
		if(is_a($ex,'Error')){
			return (self::$OnErrorException)($ex);
			
		}
		$class=get_class($ex);
		if(isset(self::$SpecailExceptionMap[$class])){
			return (self::$SpecailExceptionMap[$class])($ex);
		}
		return (self::$OnException)($ex);
		
		//throw $ex;
	}
	
	public static function HandelAllError($OnError,$OnDevError)
	{
		set_error_handler(array(__CLASS__,'onErrorHandler'));
		self::$OnError=$OnError;
		self::$OnDevError=$OnDevError;
	}
	public function onErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			return false;
		}
		switch ($errno) {
		case E_ERROR:
		case E_USER_ERROR:
			(self::$OnError)($errno, $errstr, $errfile, $errline);
			break;
		case E_USER_WARNING:
		case E_WARNING:
		case E_USER_NOTICE:
		case E_NOTICE:
			(self::$OnDevError)($errno, $errstr, $errfile, $errline);
			break;
		default:
			//echo "DNMVCS Notice: Unknown error type: [$errno] $errstr<br />\n";
			(self::$OnError)($errno, $errstr, $errfile, $errline);
			break;
		}
		//var_dump($errno, $errstr, $errfile, $errline);
		/* Don't execute PHP internal error handler */
		return true;
	}
}
class DNDBManager
{
	use DNSingleton;
	
	public $db=null;
	public $db_r=null;

	public function _DB()
	{
		if($this->db){return $this->db;}
		
		$db_config=DNConfig::G()->_Setting('db');
		$db=new DNDB();
		$db->init($db_config);
		$this->db=$db;
		
		return $this->db;
	}
	public function _DB_W()
	{
		return $this->_DB();
	}
	
	public function _DB_R()
	{
		if($this->db_r){return $this->db_r;}
		
		$db_config=DNConfig::G()->_Setting('db_r');
		if(!$db_config){return $this->_DB();}
		$db=new DNDB();
		$db->init($db_config);
		$this->db_r=$db;
		return $this->db_r;
	}
	
	public function closeAllDB()
	{
		if($this->db!==null){$this->db->close();$this->db=null;}
		if($this->db_r!==null){$this->db_r->close();$this->db_r=null;}
	}
}

trait DNMVCS_Glue
{
	//route
	public static function URL($url=null)
	{
		return DNRoute::G()->_URL($url);
	}
	public static function Param()
	{
		return DNRoute::G()->_Param();
	}
	public function assignRoute($key,$value=null)
	{
		return DNRoute::G()->assignRoute($key,$value);
	}
	//view
	public static function Show($data=array(),$view=null)
	{
		if($view===null){
			$view=DNRoute::G()->calling_path;
		}
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
	public static function ExitRedirectRouteTo($url)
	{
		return DNView::G()->_ExitRedirect(self::URL($url),$only_in_site=true);
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
		return DNConfig::G()->_Setting($key);
	}
	public static function GetConfig($key,$file_basename='config')
	{
		return DNConfig::G()->_GetConfig($key,$file_basename);
	}
	public static function LoadConfig($file_basename)
	{
		return DNConfig::G()->_LoadConfig($file_basename);
	}
	
	//exception manager
	public function assignExceptionHandel($classes,$callback=null)
	{
		return DNExceptionManager::AssignExceptionHandel($classes,$callback);
	}
	public function setDefaultExceptionHandel($Exception)
	{
		return DNExceptionManager::SetException($classes,$callback);
	}
	public static function ThrowOn($flag,$msg,$code=0)
	{
		return DNException::ThrowOn($flag,$msg,$code);
	}
	
	public static function H($str)
	{
		return self::G()->_H($str);
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
	
}
trait DNMVCS_Misc
{
	
	public function _Import($file)
	{
		$file=rtrim($file,'.php').'.php';
		require_once($this->path_lib.$file);
	}
	public static function ImportSys($file)
	{
		$file=rtrim($file,'.php').'.php';
		require_once(__DIR__.'/'.$file);
	}
	public function _H($str)
	{
		return htmlspecialchars( $str, ENT_QUOTES );
	}
	public function recordset_url(&$data,$cols_map)
	{
		//todo more quickly;
		if($data===[]){return $data;}
		if($cols_map===[]){return $data;}
		$keys=array_keys($data[0]);
		array_walk($keys,function(&$val,$k){$val='{'.$val.'}';});
		foreach($data as &$v){
			foreach($cols_map as $k=>$r){
				$values=array_values($v);
				$v[$k]=str_replace($keys,$values,$r);
				
			}
		}
		unset($v);
		return $data;
	}
	public function recordset_h(&$data,$cols=array())
	{
		if($data===[]){return $data;}
		$cols=is_array($cols)?$cols:array($cols);
		if($cols===[]){
			$cols=array_keys($data[0]);
		}
		foreach($data as &$v){
			foreach($cols as $k){
				$v[$k]=htmlspecialchars( $v[$k], ENT_QUOTES );
			}
		}
		return $data;
	}
	
}
trait DNMVCS_Handel
{
	//@override
	public function onShow404()
	{
		header("HTTP/1.1 404 Not Found");
		DNView::G()->_Show([],'_sys/error-404');
	}
	public function onException($ex)
	{
		$data=array();
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['ex']=$ex;
		$data['trace']=$ex->getTraceAsString();

		DNView::G()->_Show($data,'_sys/error-exception');
	}
	public function onErrorException($ex)
	{
		$message=$ex->getMessage();
		$code=$ex->getCode();
		
		$data=array();
		$data['message']=$message;
		$data['code']=$code;
		$data['ex']=$ex;
		$data['trace']=$ex->getTraceAsString();
		
		DNView::G()->_Show($data,'_sys/error-500');
	}
	public function onDebugError($errno, $errstr, $errfile, $errline)
	{
		if(!$this->isDev){return;}
		$data=array();
		$data['message']=$errstr;
		$data['code']=$errno;
		
		DNView::G()->showBlock('_sys/error-debug',$data);
	}
	public function onErrorHandel($errno, $errstr, $errfile, $errline)
	{
		//var_dump($errno, $errstr, $errfile, $errline);
		throw new Error($errstr,$errno);
	}
	
	//  close database before show;
	public function onBeforeShow()
	{
		if(!$this->auto_close_db){ return ;}
		try{
			DNDBManager::G()->closeAllDB();
		}catch(Error $ex){
		}catch(Exception $ex){
		}
	}
}
class DNMVCS
{
	use DNSingleton;
	use DNMVCS_Glue;
	use DNMVCS_Handel;
	use DNMVCS_Misc;
	
	protected $path=null;
	
	protected $auto_close_db=true;
	protected $has_autoload=false;
	protected $path_lib;
	
	public $options=[];
	public $config;
	public $isDev=false;
	
	public static function RunQuickly($options=array())
	{
		DNMVCS::G()->autoload($options);
		$framework_class=isset($options['framework_class'])?$options['framework_class']:'\\MY\\Framework\\App';
		if(class_exists($framework_class)){
			return DNMVCS::G($framework_class::G())->init($options)->run();
		}else{
			return DNMVCS::G()->init($options)->run();
		}
	}
	public function autoload($options=array())
	{
		$this->init_options($options);
		
		if(! DNAutoLoad::G()->isInited()){
			DNAutoLoad::G()->init($this->options)->run();
		}
		$this->options=array_merge($this->options,DNAutoLoad::G()->options); 
		$this->path=$this->options['path'];
		$this->path_lib=$this->path.rtrim($this->options['path_lib'],'/').'/';

		return $this;
	}
	
	protected function init_options($options)
	{
		$default_options=[
			'namesapce'=>'MY',
			
			'path_namespace'=>'app',
			'path_autoload'=>'classes',
			'fullpath_framework_common'=>'',
			
			'enable_simple_mode'=>true,
		];
		$default_options_other=[
			'path_framework_simple'=>'app',
			
			'path_controller'=>'app/Controller',
			'namespace_controller'=>'Controller',
			
			
			'path_config'=>'config',
			'fullpath_config_common'=>'',
			'enable_paramters'=>true,
			'path_view'=>'view',
			'path_lib'=>'lib',
			'default_controller_class'=>'DNController',
		];
		
		if(!isset($options['path']) || !($options['path'])){
			$path=realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/../');
			$options['path']=rtrim($path,'/').'/';
		}
		$this->options=array_merge($default_options,$default_options_other,$options);
		
		
	}
	//@override me
	public function init($options=array())
	{
		DNExceptionManager::HandelAllException([$this,'onErrorException'],[$this,'onException']);
		DNExceptionManager::HandelAllError([$this,'onErrorHandel'],[$this,'onDebugError']);
		
		
		
		//override me to autoload; 
		$this->autoload($this->options);
		$options=$this->options;
		
		$path_view=$this->path.rtrim($options['path_view'],'/').'/';
		DNView::G()->init($path_view);
		DNView::G()->setBeforeShow([$this,'onBeforeShow']);
		
		$path_config=$this->path.rtrim($options['path_config'],'/').'/';
		$fullpath_config_common=rtrim($options['fullpath_config_common'],'/').'/';
		DNConfig::G()->init($path_config,$fullpath_config_common);
		$this->config=$config;
		$this->isDev=DNConfig::G()->_Setting('is_dev')?true:false;
		DNView::G()->isDev=$this->isDev;
		
		DNRoute::G()->init(array(
			'path'=>$options['path'],
			'namesapce'=>$options['namesapce'],
			
			'path_namespace'=>$options['path_namespace'],
			
			'path_controller'=>$options['path_controller'],
			'namespace_controller'=>$options['namespace_controller'],
			
			'enable_simple_mode'=>$options['enable_simple_mode'],
			'enable_paramters'=>$options['enable_paramters'],
		));
		
		DNRoute::G()->set404(array($this,'onShow404'));	
		
		return $this;
	}
	public function isDev()
	{
		return $this->isDev;
	}
	public function run()
	{
		ob_start();
		DNRoute::G()->run();
		ob_end_flush();
		return $this;
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
class DNSystemException extends \Exception
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