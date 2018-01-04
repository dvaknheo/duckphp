<?php
//dvaknheo@github.com

class DNSingleton
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
class DNAutoLoad extends DNSingleton
{
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
					DNException::ThrowOn(!$this->path_common,'CommonService/CommonModel need path_common');
					
					$file=$this->path_common.strtolower($m[2]).'/'.$classname.'.php';
					if(!file_exists($file)){return false;}
					$flag=include($file);
					return true;
				}
			
			}
			
			
		});
	}
}

class DNRoute extends DNSingleton
{
	protected $site='';
	protected $route_handels=array();
	protected $dispatches=array();
	protected $on404Handel;
	
	//独立的功能 ,我们可以替换，比如  #! 模式
	public static function URL($url=null)
	{
		return self::G()->_URL($url);
	}
	public function _URL($url=null)
	{
		static $basepath;
		if(null===$url){return $_SERVER['REQUEST_URI'];}
		if(''===$url){return $_SERVER['REQUEST_URI'];}
		$url=preg_replace('/^\//','',$url);
		
		if(null===$basepath){
			$basepath=rtrim(str_replace('\\','/',$_SERVER['SCRIPT_NAME']),'/').'/';
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
		return $callback();
	}
	
	public function defaltRouteHandle()
	{
/*

/index 
/Test => Test::index  Main::Test
/Test/index  Test/index::index
/Test/Method1  	Test::Method1 Test\Method1::index
/Test/Class2/index
//*/

		$default_controller='Main';
		$default_method='index';
		
		$is_post=($_SERVER['REQUEST_METHOD']=='POST')?true:false;
		$site=$this->site?$this->site.'/':'';
		
		$path_info=isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
		
		if(substr($path_info,-1)=='/'){
			$path_info.='index';
		}
		if($path_info=='/index.php'){$path_info='/';}
		list($default,$c,$m)=array_pad(explode('/',$path_info),3,null);
		
		//我们扩展到全部。
		
		$p=preg_match('/(.*)?\/([^\/]*)$/',$path_info,$mm);
		$full_class='';
		if(!$p){
			$full_class=$default_controller;
			$method=$default_method;
		}else{
			$full_class=$mm[1];
			$method=$mm[2];
		}
		if(!$full_class){
			$full_class=$default_controller;
			
		}
		//if(substr($full_class,0,1)=='/'){
		//	substr($full_class,0,1)
		//}
		$class=basename($full_class);
		
		$file=$this->path.$site.$full_class.'.php';
		if(!is_file($file)){
			return null;
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

	protected function match_path_info($pattern,$path_info)
	{
		//'POST:/xx*/'
		//'GET:/xxf*saf[a-z]fdsfds';
		//'GET:~afasdf/bdfdsafs/;
		//'~a.b;
		if($pattern==$path_info){return true;}
		if($pattern==$_SERVER['HTTP_METHOD'].':'.$path_info){return true;}
		
		return false;
	}
	public function defaltDispathHandle()
	{
		
		
		$path_info=$_SERVER['PATH_INFO'];
		$ret=null;
		foreach($this->dispatches as $pattern =>$callback){
			if($this->match_path_info($pattern,$path_info)){
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
//OK，懒得写字用。
if(!function_exists('url')){
function URL($url)
{
	return DNRoute::URL($url);
}
}
class DNView extends DNSingleton
{
	protected $head_file;
	protected $foot_file;
	protected $data=array();
	public $onBeforeShow=null;
	public $path;
	
	//这个静态函数背后调用动态函数了，因为要继承一些东西
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
		//TODO 判断跳转地址.
		header('location: '.$url);
		exit;
	}
	public static function return_route_to($url)
	{
		//TODO 判断跳转地址.
		header('location: '.URL($url));
		exit;
	}
	
	public function _Show($view,$data=array(),$use_wrapper=true)
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
	public function assign($key,$value)
	{
		//不建议在普通方法里用。
		$this->data[$key]=$value;
	}
	
}

class DNConfig extends DNSingleton
{
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
	
	//隔离变量
	protected function include_file($file)
	{
		return include($file);
	}
	public function _Setting($key)
	{
		//不做多文件的 setting;
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
				echo('DNMVCS ERROR: no setting file!');
				exit;
			}
			if(!is_array($setting)){
				echo('DNMVCS ERROR: need return array !');
				exit;
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
	//TODO 合法性判断
	public function _Load($file_basename='config')
	{
		//多文件多配置？
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
class DNDB extends DNSingleton
{
	protected $pdo;
	protected $rowCount;
	
	protected $config;
	
	public function init($config)
	{
		$this->config=$config;
	}
	public function check_connect()
	{
		if($this->pdo){return;}
		//TODO 这里检查配置是否有误。
		if(empty($this->config)){
			echo('DNMVCS ERROR: database not setting!');
			exit;
		}
		$config=$this->config;
		if(!isset($config['dsn'])){
			$dsn="mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8";
		}else{
			$dsn=$config['dsn'];
		}
		$this->pdo= new PDO($dsn, $config['user'], $config['password'],array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
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
class DNException extends Exception
{
	public static $is_handeling;
	public static $default_handel;
	
	public static $error_handel;
	public static function ThrowOn($flag,$message,$code=0)
	{
		if(!$flag){return;}
		if(!DNException::$is_handeling){
			DNException::HandelAllException();
		}
		$class=get_class();//static::class; // 兼容旧版
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
	public static function ManageException($ex)
	{
		$class=get_class($ex);
		if(is_callable(array($class,'OnException'))){
			$class::OnException($ex);
		}else{
			if(DNException::$default_handel){
				call_user_func(DNException::$default_handel,$ex);
			}else{
				throw $ex;
			}
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

class DNMVCS extends DNSingleton
{
	
	protected $path;
	protected $auto_close_db=true;
	
	//@override
	public function onShow404()
	{
		header("HTTP/1.1 404 Not Found");
		DNView::Show('_sys/error-404',array(),false);
		if(!is_file($this->path.'view/'.'_sys/error-404'.'.php')){
echo <<<EOT
<div>
DNMVCS::Tip: You Need A View name _sys/error-404 in view path;
</div>
<pre>
404!
</pre>
EOT;
		}
	}
	public function onException($ex)
	{
		$data=array();
		$data['message']=$ex->getMessage();
		$data['code']=$ex->getCode();
		$data['ex']=$ex;
		$data['trace']=$ex->getTraceAsString();

		DNView::Show('_sys/error-exception',$data,false);
		if(!is_file($this->path.'view/'.'_sys/error-exception'.'.php')){
echo <<<EOT
<div>
DNMVCS::Tip: You Need A View name _sys/error-exception in view path;
</div>
<pre>
{$data['message']}
{$data['code']}
{$data['trace']}
</pre>
EOT;
		}
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
		if(!is_file($this->path.'view/'.'_sys/error-500'.'.php')){
echo <<<EOT
<div>
DNMVCS::Tip: You Need A View name _sys/error-500 in view path;
</div>
<pre>
{$data['message']}
{$data['code']}
{$data['trace']}
</pre>
EOT;
		}
	}
	public function onDebugError($errno, $errstr, $errfile)
	{
		$data=array();
		$data['message']=$errstr;
		$data['code']=$errno;
		DNView::G()->showBlock('_sys/error-debug',$data,false);
		if(!is_file($this->path.'view/'.'_sys/error-debug'.'.php')){
echo <<<EOT
<div>
DNMVCS::Tip: You Need A View name _sys/error-debug in view path;<br />
[ $errstr, $errfile:$errno]
</div>
EOT;
		}
	}
	
	// view 之前关闭数据库
	public function onBeforeShow()
	{
		if($this->auto_close_db){
			DNDB::G()->close();
		}
	}
	
	//@override
	public function init($path='',$path_common='')
	{
		$path=$path!=''?$path:realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/../');
		$path=rtrim($path,'/').'/';
		$this->path=$path;
		
		DNAutoLoad::G()->init($path,$path_common?$path_common:'');
		DNAutoLoad::G()->run();
		
		DNException::HandelAllException();
		DNException::SetDefaultAllExceptionHandel(array($this,'onOtherException'));
		DNException::SetErrorHandel(array($this,'onException'));
		
		DNRoute::G()->init($path.'controller/');
		DNRoute::G()->set404(array($this,'onShow404'));	
		
		DNConfig::G()->init($path.'config/',$path_common?$path_common.'config/':'');
		
		DNView::G()->init($path.'view/');
		//DNView::G()->setWrapper("inc-head","inc-foot");
		DNView::G()->setBeforeShow(array($this,'onBeforeShow'));
		DNView::G()->isDev=$this->isDev();
		
		
		$db_config=DNConfig::Setting('db');
		DNDB::G()->init($db_config);
		
		set_error_handler(array($this,'onErrorHandler'));
	}

	public function run()
	{
		DNRoute::G()->run();
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
				//我们在日志里记录下错误，然后返回
				break;
			}
			$this->onDebugError($errno, $errstr, $errfile);
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

class DNController
{
}
class DNService extends DNSingleton
{
}
class DNModel extends DNSingleton
{
}
