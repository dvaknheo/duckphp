<?php
//dvaknheo@github.com
//OK，Lazy
if(!function_exists('URL')){
function URL($url)
{
	return DNRoute::URL($url);
}
}
if(!function_exists('H')){
function H($str)
{
	return htmlspecialchars( $str, ENT_QUOTES );
}
}
trait DNSingleton
{
	protected static $_instances=array();
	public static function G($object=null)
	{
		$class=get_called_class();
		if($object){
			self::$_instances[$class]=$object;
			return $object;
		}
		$me=isset(self::$_instances[$class])?self::$_instances[$class]:null;
		if(null===$me){
			$me=new $class();
			self::$_instances[$class]=$me;
		}
		return $me;
	}
	
}
class DNAutoLoad
{
	use DNSingleton;
	public $path;
	public $path_common;
	public function init($path,$path_common='')
	{
		$this->path=$path;
		$this->path_common=$path_common;
	}
	public function run()
	{
		spl_autoload_register(function($classname){
			if($classname!=basename($classname)){return false;}
			
			
			$flag=preg_match('/(Common)?(Service|Model)$/',$classname,$m);
			if(!$flag){
				$file=$this->path_common.'lib'.'/'.$classname.'.php';
				if($this->path_common && file_exists($file)){
					$flag=include($file);
					return true;
				}
				
				$file=$this->path.'lib'.'/'.$classname.'.php';
				if(file_exists($file)){
					$flag=include($file);
					return true;
				}
				
			}else{
				if(!$m[1]){
					//normal
					$file=$this->path.strtolower($m[2]).'/'.$classname.'.php';
					if(!file_exists($file)){return false;}
					$flag=include($file);
					return true;
				}else{
					DNException::ThrowOn(!$this->path_common,'CommonService/CommonModel need path_common'); // 
					
					$file=$this->path_common.strtolower($m[2]).'/'.$classname.'.php';
					if(!file_exists($file)){return false;}
					$flag=include($file);
					return true;
				}
			
			}
			
			
		});
		//Controller
		spl_autoload_register(function ($class) {
			$prefix = 'DnController\\';
			$base_dir =$this->path.'controller/';

			$len = strlen($prefix);
			if (strncmp($prefix, $class, $len) !== 0) {
				return;
			}
			$relative_class = substr($class, $len);
			$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
			if (is_file($file)) {
				require $file;
				return true;
			}
		});
		
		spl_autoload_register(function ($class) {
			if(substr($class,0,strlen('Core'))=='Core'){
				$file = $this->path.'core/'.$class . '.php';
				if (is_file($file)) {
					require $file;
					return true;
				}
			}
		});
	}
}

class DNRoute
{
	use DNSingleton;
	
	protected $site=''; //for sites in a controller
	protected $route_handels=array();
	protected $routeMap=array();
	protected $on404Handel;
	protected $params=array();
	public $method_calling=null;
	public static function URL($url=null)
	{
		return self::G()->_URL($url);
	}
	public static function Param()
	{
		return self::G()->_Param();
	}
	public function _URL($url=null)
	{
		static $basepath;
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
	public function _Param()
	{
		return $this->params;
	}
	public function init($path)
	{	
		$this->path=$path;
		array_push($this->route_handels,array($this,'defaltRouteHandle'));
	}
	protected function default404()
	{
		throw new Exception("DNMVCS Notice: 404 , Develop should override this");
	}
	public function set404($callback)
	{
		$this->on404Handel=$callback;
	}
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
				$this->default404();
				return;
			}
			$t=$this->on404Handel;
			return $t();
		}
		
		return call_user_func_array($callback,$this->params);
	}

	public function defaltRouteHandle()
	{
		$path_info=isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
		return $this->mapPathToFunction($path_info);
	}
	public function mapPathToFunction($path_info)
	{
		$default_controller='Main';
		$default_method='index';

		$site=$this->site?$this->site.'/':'';
		$site='';
		
		$blocks=explode('/',$path_info);
		array_shift($blocks);
		$prefix=$this->path.$site;
		$l=count($blocks);
		$current_class='';
		$method='';
		
		for($i=0;$i<$l;$i++){
			$v=$blocks[$i];
			$method=$v;
			if(''==$v){break;}
			if(!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',$v)){
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
		
		$param=array_slice($blocks,count(explode('/',$current_class))+($current_class?1:0));
		
		if($param==array(0=>'')){$param=array();}
		
		$this->params=$param;
		
		$class='';
		$method=$method?$method:'index';
		$current_class=$current_class?$current_class:'Main';
		$file=$this->path.$site.$current_class.'.php';
		$this->method_calling=$method;
		$this->includeControllerFile($file);
		$obj=$this->getObecjectToCall($current_class);
		
		
		if(null==$obj){return null;}
		return $this->getMethodToCall($obj,$method);
	}
	public function getMethodCalling()
	{
		return $this->method_calling;
	}
	// You can override it; variable indived
	protected function includeControllerFile($file)
	{
		return include($file);
	}
	// You can override it;
	protected function getObecjectToCall($class_name)
	{
		if(substr(basename($class_name),0,1)=='_'){return null;}
		$classname='\DNControllerNamespace\\'.str_replace('/','\\',$class_name);
		if(class_exists($classname)){
			$obj=new $classname();
			return $obj;
		}
		
		$obj=new DnController();
		return $obj;
	}
	protected function getMethodToCall($obj,$method)
	{
		if(substr($method,0,2)=='__'){return null;}
		$is_post=($_SERVER['REQUEST_METHOD']=='POST')?true:false;
		if($is_post){
			if(method_exists ($obj,'do_'.$method)){
				$method='do_'.$method;
			}else if(! method_exists ($obj,$method)){
				return null;
			}
		}else{
			if(!method_exists ($obj,$method)){
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
		//var_dump($pattern_url,$path_info);exit;
		$pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
		$flag=preg_match($pattern,$pattern_url,$m);
		if(!$flag){return false;}
		$method=$m[2];
		$is_reg=$m[3];
		$url=$m[4];
		if($method && $method!==$_SERVER['REQUEST_METHOD']){return false;}
		if(!$is_reg){
			$params=explode('/',$path_info);
			array_shift($params);
			$url_params=explode('/',$url);
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
	public function defaltDispathHandle()
	{
		$path_info=$_SERVER['PATH_INFO'];
		$ret=null;
		foreach($this->routeMap as $pattern =>$callback){
			if($this->match_path_info($pattern,$path_info)){
				if(!is_callable($callback)){
					list($class,$method)=explode('$',$callback);
					$obj=new $class;
					$callback=array($obj,$method);
					//DNException::ThrowOn(true,"...");
				}
				$ret=$callback;
			}
			if($ret){break;}
			
		}
		
		return $ret;
	}
	
	public function addDispathRoute($key,$callback)
	{
		if(empty($this->routeMap)){
			array_push($this->route_handels,array($this,'defaltDispathHandle'));
		}
		$this->routeMap[$key]=$callback;
	}
	public function mapRoutes($map)
	{
		if(empty($this->routeMap)){
			array_push($this->route_handels,array($this,'defaltDispathHandle'));
		}
		$this->routeMap=$map;
	}

}

class DNView
{
	use DNSingleton;

	protected $head_file;
	protected $foot_file;
	protected $data=array();
	public $onBeforeShow=null;
	public $path;
	
	public static function Show($view,$data=array(),$use_wrapper=true)
	{
		self::G()->_Show($view,$data,$use_wrapper);
	}

	public static function return_json($ret)
	{
		header('content-type:text/json');
		echo json_encode($ret,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
		exit;
	}
	public static function return_redirect($url)
	{
		//TODO check redirect safe.
		header('location: '.$url);
		exit;
	}
	//outter function url()
	public static function return_route_to($url)
	{
		//TODO check redirect safe.
		header('location: '.URL($url));
		exit;
	}
	
	public function _Show($view,$data=array(),$use_wrapper=true)
	{
		if(is_callable($this->onBeforeShow)){
			$t=$this->onBeforeShow;
			$t($view,$data,$use_wrapper);
		}
		// stop notice 
		error_reporting(error_reporting() & ~E_NOTICE);
		
		$this->data=$this->data?$this->data:array();
		$this->data=array_merge($this->data,$data);
		unset($data);
		extract($this->data);
		
		if( $use_wrapper && $this->head_file){
			include($this->path.$this->head_file.'.php');
		}
		include( $this->path.$view.'.php');
		
		if( $use_wrapper && $this->foot_file){
			include($this->path.$this->foot_file.'.php');
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
	public function setWrapper($head_file,$foot_file)
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
	public function _assign($key,$value)
	{
		$this->data[$key]=$value;
	}
	public function _setData($data)
	{
		$this->data=$data;
	}
}

class DNConfig
{
	use DNSingleton;

	protected $path;
	protected $path_common;
	public static function Setting($key)
	{
		return self::G()->_Setting($key);
	}
	public static function Get($key,$file_basename='config')
	{
		return self::G()->_Get($file_basename);
	}
	public static function Load($file_basename)
	{
		return self::G()->_Load($file_basename);
	}
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
				throw new Exception('DNMVCS Notice: no setting file!,change setting.sample.php to setting.php');
			}
			if(!is_array($setting)){
				throw new Exception('DNMVCS Notice: need return array !');
			}
			$setting=array_merge($base_setting,$setting);
		}
		return isset($setting[$key])?$setting[$key]:null;
	}
	
	public function _Get($key,$file_basename='config')
	{
		$config=$this->_Load($file_basename);
		return isset($config[$key])?$config[$key]:null;
	}
	
	public function _Load($file_basename='config')
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
	use DNSingleton;

	protected $pdo;
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
			throw new Exception('DNMVCS Notice: database not setting!');
		}
		$config=$this->config;
		$this->pdo= new PDO($config['dsn'], $config['user'], $config['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
	}
	public function getPDO()
	{
		return $this->pdo;
	}
	public function setPDO($pdo)
	{
		$this->pdo=$pdo;
	}
	public function close()
	{
		if(null===$this->pdo){return;}
		
		$this->rowCount=0;
		$this->pdo=null;
	}
	public function quote($string)
	{
		$this->check_connect();
		return $this->pdo->quote($string);
	}
	//Warnning, escape the key by yourself
	public function quote_array($array)
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
	public function exec($sql)
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
	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}
	
	public function get($table_name,$id,$key='id')
	{
		$sql="select * from {$table_name} where {$key}=? limit 1";
		return $this->fetch($sql,$id);
	}
	
	public function insert($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".DNDB::G()->quote_array($data);
		$ret=$this->exec($sql);
		if(!$return_last_id){return $ret;}
		$ret=DNDB::G()->lastInsertId();
		return $ret;
	}
	public function delete($table,$id,$key='id')
	{
		throw new Exception("DNMVCS Notice : override me to delete");
		$sql="delete from {$table_name} where {$key}=? limit 1";
		return $this->exec($sql,$id);
	}
	
	public function update($table_name,$id,$data,$key='id')
	{
		if(isset($data[$key])){unset($data[$key]);}
		$frag=DNDB::G()->quote_array($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=DNDB::G()->exec($sql,$id);
		return $ret;
	}
}
class DNException extends Exception
{
	public static $is_handeling;
	public static $default_handel;
	
	public static $error_handel;
	public static $specail_exceptions=array();
	
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		if(!DNException::$is_handeling){
			DNException::HandelAllException();
		}
		//$class=get_class();//static::class; //
		$class=get_called_class();
		throw new $class($message,$code);
	}
	public static function SetDefaultAllExceptionHandel($callback)
	{
		DNException::$default_handel=$callback;
	}
	public static function HandelAllException()
	{
		DNException::$is_handeling=true;
		set_exception_handler(array(__CLASS__,'ManageException'));
	}
	public static function SetSpecial($class,$callback)
	{
		DNException::$specail_exceptions[$class]=$callback;
	}
	public static function ManageException($ex)
	{
		$class=get_class($ex);

		if(isset(DNException::$specail_exceptions[$class])){
			call_user_func(DNException::$specail_exceptions[$class],$ex);
			return;
		}
		if(is_callable(array($class,'OnException'))){
			$class::OnException($ex);
			return;
		}
		if(DNException::$default_handel){
			call_user_func(DNException::$default_handel,$ex);
		}else{
			throw $ex;
		}
		
	}
	public static function SetErrorHandel($error_handel)
	{
		self::$error_handel=$error_handel;
	}
	public static function OnException($ex)
	{
		if(self::$error_handel){
			return call_user_func(self::$error_handel,$ex);
		}
		throw $ex;
	}
}

class DNMVCS
{
	use DNSingleton;
	
	protected $path;
	protected $auto_close_db=true;
	protected $config;
	protected $has_autoload=false;
	public function RunQuickly($path='')
	{
		return DNMVCS::G()->init($path)->run();
	}
	//@override
	public function onShow404()
	{
		header("HTTP/1.1 404 Not Found");
		DNView::Show('_sys/error-404',array(),false);
	}
	public function onException($ex)
	{
		$data=array();
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['ex']=$ex;
		$data['trace']=$ex->getTraceAsString();

		DNView::Show('_sys/error-exception',$data,false);
	}
	public function onOtherException($ex)
	{
		$message=$ex->getMessage();
		$code=$ex->getCode();
		
		$data=array();
		$data['message']=$message;
		$data['code']=$code;
		$data['ex']=$ex;
		$data['trace']=$ex->getTraceAsString();
		
		DNView::Show('_sys/error-500',$data,false);
	}
	public function onDebugError($errno, $errstr, $errfile)
	{
		$data=array();
		$data['message']=$errstr;
		$data['code']=$errno;
		
		DNView::G()->showBlock('_sys/error-debug',$data,false);
	}
	
	//  close database before show;
	public function onBeforeShow()
	{
		if(!$this->auto_close_db){ return ;}
		try{
			DNDB::G()->close();
		}catch( Exception $ex){
		}
	}
	protected function init_path($path)
	{
		$path=$path!=''?$path:realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/../');
		$path=rtrim($path,'/').'/';
		$this->path=$path;
	}
	public function autoload($path,$path_common='')
	{
		$this->has_autoload=true;
		$this->init_path($path);
		DNAutoLoad::G()->init($this->path,$path_common?$path_common:'');
		DNAutoLoad::G()->run();
		return $this;
	}
	//@override
	public function init($path='',$path_common='',$config=array())
	{
		//子类化之后，路径还是存在问题
		$this->config=$config;
		if(!$this->has_autoload){
			$this->autoload($path,$path_common);
		}else{
			$this->init_path($path);
		}
		
		DNException::HandelAllException();
		DNException::SetDefaultAllExceptionHandel(array($this,'onOtherException'));
		DNException::SetErrorHandel(array($this,'onException'));
		
		DNRoute::G()->init($this->path.'controller/');
		DNRoute::G()->set404(array($this,'onShow404'));	
		
		DNConfig::G()->init($this->path.'config/',$path_common?$path_common.'config/':'');
		
		DNView::G()->init($this->path.'view/');
		//DNView::G()->setWrapper("inc-head","inc-foot");
		DNView::G()->setBeforeShow(array($this,'onBeforeShow'));
		DNView::G()->isDev=$this->isDev();
		
		
		$db_config=DNConfig::Setting('db');
		DNDB::G()->init($db_config);
		set_error_handler(array($this,'onErrorHandler'));
		
		return $this;
	}

	public function run()
	{
		ob_start();
		DNRoute::G()->run();
		ob_end_flush();
		return $this;
	}
	

	public function isDev()
	{
		$is_dev=DNConfig::Setting('is_dev');
		return $is_dev?true:false;
	}

	public function onErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			return false;
		}
		switch ($errno) {
		case E_ERROR:
		case E_USER_ERROR:
			throw new Exception($errstr,$errno);
			exit;
		case E_USER_WARNING:
		case E_WARNING:
		case E_USER_NOTICE:
		case E_NOTICE:
			if(!$this->isDev()){
				break;
			}
			$this->onDebugError($errno, $errstr, $errfile);
			break;
		default:
			echo "DNMVCS Notice: Unknown error type: [$errno] $errstr<br />\n";
			break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}
	
}

/////////////////////////

class DNControllerBase
{
}
class DNService
{
	use DNSingleton;
}
class DNModel
{
	use DNSingleton;
}
