<?php

if(!defined('DN_PATH_PUBLIC')){
	define('DN_PATH_PUBLIC',realpath(__DIR__ .'/../').'/');
	define('DN_PATH_PUBLIC_LIB',realpath(__DIR__ .'/../lib/').'/');
}
class DNSingletoner
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
class DNMVCBase extends DNSingletoner
{
	protected $is_production;
	protected $path;

	public function init($path,$options=array())
	{	
		$this->path=$path;
	}

	public function run()
	{
		return;
	}

}
class DNAutoLoadBase extends DNSingletoner
{
	public $path;
	public $path_common;
	public function init($path,$path_common='')
	{
		$this->path=$path;
		$this->path_common=$path_common;
		if(!$this->path_common){
			$this->path_common=realpath(__DIR__.'/../').'/';
		}
	}
	public function autoLoad()
	{
		spl_autoload_register(function($classname){
			if($classname!=basename($classname)){return false;}
			
			
			$flag=preg_match('/(Common)?(Service|Model)$/',$classname,$m);
			if(!$flag){
				$file=$this->path_common.'lib'.'/'.$classname.'.php';
				if(file_exists($file)){
					$flag=include($file);
					return true;
				}
				
				$file=$this->path.'lib'.'/'.$classname.'.php';
				if(file_exists($file)){
					$flag=include($file);
					return true;
				}
			}
			if(!$m[1]){
				//normal
				$file=$this->path.strtolower($m[2]).'/'.$classname.'.php';
				if(!file_exists($file)){return false;}
				$flag=include($file);
				return true;
			}else{
				// core
				
				$file=$this->path_common.strtolower($m[2]).'/'.$classname.'.php';
				if(!file_exists($file)){return false;}
				$flag=include($file);
				return true;
			}
			
			
		});
	}
}

class DNRouteBase extends DNSingletoner
{
	protected $site='';
	protected $route_handels=array();
	protected $dispatches=array();
	protected $on404Handel;
	
	//独立的功能
	public static function URL($url=null)
	{
		static $basepath;
		if(null===$url){return $_SERVER['REQUEST_URI'];}
		if(''===$url){return $_SERVER['REQUEST_URI'];}
		$url=preg_replace('/^\//','',$url);
		
		if(null===$basepath){
			$basepath=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME'])),'/').'/';
			if(defined('DN_EMU_ROUTE')){
				$flag=preg_match('/\/_([a-z0-9+]*)/',$_SERVER['REQUEST_URI'],$m);
				$basepath=$m[0].'/';
			}
		}
		
		
		if('/'==$url{0}){
			return $url;
		};
		if('?'==$url{0} || '#'==$url{0}){
			return $basepath.$path_info.$url;
		}
		return $basepath.$url;
	}
	
	public function init($path)
	{	
		$this->path=$path;
		array_push($this->route_handels,array($this,'defaltRouteHandle'));
	}
	protected function default404()
	{
		// 这里了也应该调
		throw new Exception("404 ,找不到地址");
	}
	public function set404($callback)
	{
		$this->on404Handel=$callback;
	}
	public function getRouteCallback()
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
		return $callback();
	}
	
	public function defaltRouteHandle()
	{
		$path_info=isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
		
		
		if(defined('DN_EMU_ROUTE')){
			$path_info=preg_replace('/^\/[^\/]*/','',$path_info);
		}
		
		
		$is_post=($_SERVER['REQUEST_METHOD']=='POST')?true:false;
		
		$site=$this->site?$this->site.'/':'';
		
		list($default,$c,$m)=array_pad(explode('/',$path_info),3,null);
		
		if($m===null){
			$file=$this->path.$site.$c.'.php';
			if(is_file($file)){
				$class=$c;
				$method='index';
			}else{
				$file=$this->path.$site.'Main.php';
				if(is_file($file)){
					$class='Main';
					$method=$c?$c:'index';
				}
			}
		}else{
			$class=$c;
			$method=$m;
			if(null==$m){$method='index';}
			
			$file=$this->path.$site.$class.'.php';
			if(!is_file($file)){
				return null;
			}
		}

		include $file;
		$obj=new $class;
		
		//POST ，添加定位到 do_*上来。TODO GET do_* 就不允许 GET 方法了。
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
	
	public function defaltDispathHandle()
	{
		//'POST:/xx*/'
		//'GET:/xxf*saf[a-z]fdsfds';
		//'GET:~afasdf/bdfdsafs/;
		//'~a.b;
		
		$path_info=$_SERVER['PATH_INFO'];
		$ret=null;
		foreach($this->dispatches as $pattern =>$callback){
			if($pattern==$path_info){
				$ret=$callback;
			}
			if($ret){break;}
			
		}
		
		return $ret;
	}
	
	public function addDispathRoute($key,$callback)
	{
		if(empty($this->dispatches)){
			array_push($this->route_handels,array($this,'defaltDispathHandle'));
		}
		$this->dispatches[$key]=$callback;
	}

}
class DNConfigBase extends DNSingletoner
{
	public static function Setting($key)
	{
		return self::G()->getSetting($key);
	}
	public static function Get($key,$file_basename='config')
	{
		return self::G()->getConfig($file_basename);
	}
	public static function Load($file_basename)
	{
		return self::G()->loadConfig($file_basename);
	}
	public function init($path,$path_public=null)
	{
		$this->path=$path;
		$this->path_public=$path_public;
	}
	
	//隔离变量
	protected function include_file($file)
	{
		return include($file);
	}
	public function getSetting($key)
	{
		//不做多文件的 setting;
		static $setting;
		if(isset($setting[$key])){return $setting[$key];}
		if(null===$setting){
			$base_config=array();
			if($this->path_public){
				$base_setting=$this->include_file($this->path_public.'config/setting.php');
				$base_setting=is_array($base_setting)?$base_setting:array();
			}
			$setting=$this->include_file($this->path.'config/setting.php');
			$setting=array_merge($base_setting,$setting);
		}
		return isset($setting[$key])?$setting[$key]:null;
	}
	
	public function getConfig($key,$file_basename='config')
	{
		$config=$this->loadConfig($file_basename);
		return isset($config[$key])?$config[$key]:null;
	}
	public function loadConfig($file_basename='config')
	{
		//多文件多配置？
		static $all_config=array();
		if(isset($all_config[$file_basename])){return $all_config[$file_basename];}
		$base_config=array();
		if($this->path_public){
			$base_config=$this->include_file($this->path_public.'config/'.$file_basename.'.php');
			$base_config=is_array($base_config)?$base_config:array();
		}
		$config=$this->include_file($this->path.'config/'.$file_basename.'.php');
		$config=array_merge($base_config,$config);
		
		$all_config[$file_basename]=$config;
		return $config;
		
	}
}



//OK，懒得写字用。
if(!function_exists('url')){
function URL($url)
{
	return DNRouteBase::URL($url);
}
}
class DNExceptionBase extends Exception
{
	public static $is_handeling;
	public static $default_handel;
	
	public static $error_handel;
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		if(!DNExceptionBase::$is_handeling){
			DNExceptionBase::HandelAllException();
		}
		$class=static::class;
		throw new $class($message,$code);
	}
	public static function DefaultHandel($callback)
	{
		DNExceptionBase::$default_handel=$callback;
	}
	public static function HandelAllException()
	{
		DNExceptionBase::$is_handeling=true;
		set_exception_handler(array(__CLASS__,'ManageException'));
	}
	public static function ManageException($ex)
	{
		$class=get_class($ex);
		if(is_callable(array($class,'OnException'))){
			$class->OnException($ex);
		}else{
			if(DNExceptionBase::$default_handel){
				call_user_func(DNExceptionBase::$default_handel,$ex);
			}else{
				throw $ex;
			}
		}
		
	}
	public static function SetMyHandel($error_handel)
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


class DNViewBase extends DNSingletoner
{
	protected $head_file;
	protected $foot_file;
	protected $data=array();
	public $onBeforeShow=null;
	public $path;
	
	//这个静态函数背后调用动态函数了，因为要继承一些东西
	public static function Show($view,$data=array(),$close_db=true)
	{
		self::G()->_show($view,$data,$close_db);
	}
	
	public function init($path)
	{
		$this->path=$path;
		
	}
	public function setPath($path)
	{
		$this->path=$path;
		
	}
	public function setBeforeShow($callback)
	{
		$this->onBeforeShow=$callback;
	}
	public function _show($view,$data=array(),$use_wrapper=true)
	{
		if(is_callable($this->onBeforeShow)){
			$t=$this->onBeforeShow;
			$t($view,$data,$use_wrapper);
		}
		//这里最好还要用 OB 函数，使得 500 错误的时候只输出 500 错误
		// 屏蔽 notice 级别的错误。
		error_reporting(error_reporting() & ~E_NOTICE);
		
		// TODO 这里的 extract 和本地变量的结合
		//页面，页脚
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
	public function showBlock($view,$data)
	{
		error_reporting(error_reporting() & ~E_NOTICE);
		extract($data);
		include($this->path.$view.'.php');
	}
	public function _assign($key,$value)
	{
		//不建议在普通方法里用。
		$this->data[$key]=$value;
	}
	public function setWrapper($head_file,$foot_file)
	{
		$this->head_file=$head_file;
		$this->foot_file=$foot_file;
	}
}

class DNDBBase extends DNSingletoner
{
	protected $pdo;
	protected $rowCount;
	
	public function init($config)
	{
		$this->host=$config['host'];
		$this->port=$config['port'];
		$this->dbname=$config['dbname'];
		$this->user=$config['user'];
		$this->password=$config['password'];
	}
	public function check_connect()
	{
		if($this->pdo){return;}
		$dsn="mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8";
		$this->pdo= new PDO($dsn, $this->user, $this->password,array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
	}
	public function getPDO()
	{
		return $this->pdo;
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
	//注意，这里的key是没转码的哦.
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
		$ret=$sth->fetch(PDO::FETCH_ASSOC);
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
		$sql="select {$table_name} from terms where {$key}=? limit 1";
		return $this->fetch($sql,$id);
	}
	
	public function insert($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".$this->quote_array($data);
		$ret=$this->exec($sql);
		if(!$return_last_id){return $ret;}
		$ret=DNDB::G()->lastInsertId();
		return $ret;
	}
	public function delete($table,$id,$key='id')
	{
		throw new Exception("不建议删除！");
		$sql="delete from {$table_name} where {$key}=? limit 1";
		return $this->exec($sql,$id);
	}
	
	public function update($table_name,$id,$data,$key='id')
	{
		if($data[$key]){unset($data[$key]);}
		$frag=DNDB::G()->quote_array($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=DNDB::G()->exec($sql,$id);
		return $ret;
	}
}
class DNMVC extends DNMVCBase
{
	protected $auto_close_db=true;
	//@override
	public function onShow404()
	{
		DNView::Show('_sys/error-404',array(),false);
	}
	public function onException($ex)
	{
		$data=array();
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['ex']=$ex;
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
		DNView::Show('_sys/error-500',$data,false);
	}
	// view 之前关闭数据库
	public function onBeforeShow()
	{
		if($this->auto_close_db){
			DNDB::G()->close();
		}
	}
	
	//@override
	public function init($path,$options=array())
	{
		//所有初始化都放这里
		parent::init($path,$options);
		
		$default_option=array(
			'path_project'=>'',
			'path_common'=>'',
			'path_www'=>'',
			
			'path_config'=>'',
			'path_default_config'=>'',
			
			'path_view'=>'',
			'path_controller'=>'',
			
		);
		$options=array_merge($default_option,$options);
		
		
		DNAutoLoad::G()->init($path,'');
		DNAutoLoad::G()->autoLoad();

		DNException::HandelAllException();
		DNException::DefaultHandel(array($this,'onOtherException'));
		DNException::SetMyHandel(array($this,'onException'));
		
		
		DNView::G()->setPath($path.'view/');
		DNView::G()->setWrapper("inc-head","inc-foot");
		
		
		DNConfig::G()->init($this->path,DN_PATH_PUBLIC);
		$db_config=DNConfig::Setting('db');
		
		DNDB::G()->init($db_config);
		DNView::G()->setBeforeShow(array($this,'onBeforeShow'));
		
		DNRoute::G()->init($path.'controller/');
		DNRoute::G()->set404(array($this,'onShow404'));
		DNView::G()->isDev=$this->isDev();
	}

	//@override
	public function run()
	{
		set_error_handler(array($this,'onErrorHandler'));
		// 这里要把所有变量连接起来
		DNRoute::G()->run();
	}
	

	public function isDev()
	{
		$is_dev=DNConfig::Setting('is_dev');
		return $is_dev?true:false;
	}
	public function onDebugError()
	{
		
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
				//我们在日志里记录下错误，然后返回
				break;
			}
			$data=array();
			$data['message']=$errstr;
			$data['code']=$errno;
			DNView::G()->showBlock('_sys/error-debug',$data,false);
			break;
		default:
			echo "Unknown error type: [$errno] $errstr<br />\n";
			break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}
	
}


/////////////////////////
////
class DNRoute extends DNRouteBase
{
}
class DNConfig extends DNConfigBase
{
}
class DNAutoLoad extends DNAutoLoadBase
{
}
// 前台 View ，后台view
//View 页眉唯一单例
class DNView extends DNViewBase
{
}

class DNModel extends DNModelBase
{
}

class DNController extends DNControllerBase
{
}
class DNService extends DNServiceBase
{
}
class DNDB extends DNDBBase
{
}

class DNException extends DNExceptionBase
{
}
/**
//*/


//helper 和系统相关
class DNHelperBase
{

}
// utils 和系统无关
class DNUtilsBase
{
}
class DNModelBase extends DNSingletoner
{

}

class DNServiceBase extends DNSingletoner
{
}class DNControllerBase //extends DNSingletoner
{

}