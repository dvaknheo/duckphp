<?php
namespace DNMVCS;
trait DNWrapper
{
	protected static $objects=[];
	protected $obj;
	protected function _wrap_the_object($object)
	{
		$this->obj=$object;
	}
	protected function _call_the_object($method,$args)
	{
		return call_user_func_array([$this->obj,$method],$args);
	}
	public static function W($object=null)
	{
		$caller=get_called_class();
		if($object==null){
			return self::$objects[$caller];
		}
		$self=new $caller();
		$self->_wrap_the_object($object);
		self::$objects[$caller]=$self;
		return $self;
	} 
}

//not use ,you can use SimpleRouteHandel
class SimpleRoute extends DNRoute 
{
	public $options;
	protected $key_for_simple_route='_r';
	
	public function _URL($url=null,$innerCall=false)
	{
		if(!$innerCall && $this->onURL){return ($this->onURL)($url,true);}

		$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		$path=substr($path,0,0-strlen($_SERVER['PATH_INFO']));
		if($url===null || $url==='' || $url==='/'){return $path;}
		$url='/'.ltrim($url,'/');
		$c=parse_url($url,PHP_URL_PATH);
		$q=parse_url($url,PHP_URL_QUERY);
		
		$q=$q?'&'.$q:'';
		$url=$path.'?'.$this->key_for_simple_route.'='.$c.$q;
		return $url;
	}
	public function init($options)
	{
		parent::init($options);
		$this->key_for_simple_route=$options['key_for_simple_route'];
		
		$path_info=isset($_GET[$this->key_for_simple_route])?$_GET[$this->key_for_simple_route]:'';
		$path_info=ltrim($path_info,'/');
		$this->path_info=$path_info;
	}
}
class SimpleRouteHandel
{
	use DNSingleton;

	public $key_for_simple_route='_r';
	public function onURL($url=null,$innerCall=false)
	{
		$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		$path=substr($path,0,0-strlen($_SERVER['PATH_INFO']));
		if($url===null || $url==='' || $url==='/'){return $path;}
		$url='/'.ltrim($url,'/');
		$c=parse_url($url,PHP_URL_PATH);
		$q=parse_url($url,PHP_URL_QUERY);
		
		$q=$q?'&'.$q:'';
		$url=$path.'?'.$this->key_for_simple_route.'='.$c.$q;
		return $url;
	}
	public function handel($route)
	{
		$route->setURLHandel([$this,'onURL']);
		$this->key_for_simple_route=isset($route->options['key_for_simple_route'])?$route->options['key_for_simple_route']:$this->key_for_simple_route;
		
		$path_info=isset($_GET[$this->key_for_simple_route])?$_GET[$this->key_for_simple_route]:'';
		$path_info=ltrim($path_info,'/');
		$route->path_info=$path_info;
	}
}
class RouteMapHandel
{
	use DNSingleton;
	protected $routeMap=[];
	
	protected function matchRoute($pattern_url,$path_info,$route)
	{
		$pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
		$flag=preg_match($pattern,$pattern_url,$m);
		if(!$flag){return false;}
		$method=$m[2];
		$is_regex=$m[3];
		$url=$m[4];
		if($method && $method!==$route->request_method){return false;}
		if(!$is_regex){
			$params=explode('/',$path_info);
			$url_params=explode('/',$url);
			if(!$route->enable_paramters){
				return ($url_params===$params)?true:false;
			}
			if($url_params === array_slice($params,0,count($url_params))){
				$route->parameters=array_slice($params,0,count($url_params));
				return true;
			}else{
				return false;
			}
			
		}
		
		$p='/'.str_replace('/','\/',$url).'/';
		$flag=preg_match($p,$path_info,$m);
		
		if(!$flag){return false;}
		array_shift($m);
		$route->parameters=$m;
		return true;
	}
	protected function getRouteHandelByMap($route)
	{
		foreach($this->routeMap as $pattern =>$callback){
			if(!$this->matchRoute($pattern,$route->path_info,$route)){continue;}
			if(!is_string($callback)){return $callback;}
			if(false!==strpos($callback,'->')){
				$obj=new $class;
				return array($obj,$method);
			}
			return $callback;
		}
		
		return null;
	}
	public function  handel($route)
	{
		$route->callback=$this->getRouteHandelByMap($route);
	}
	public function assignRoute($key,$callback=null)
	{
		if(is_array($key)&& $callback===null){
			$this->routeMap=array_merge($this->routeMap,$key);
		}else{
			$this->routeMap[$key]=$callback;
		}
	}
}
class RouteRewriteHandel
{
	use DNSingleton;
	protected $rewriteMap=[];
	public function matchRewrite($old_url,$new_url,$route)
	{
		$path_info=$route->path_info;
		if(substr($old_url,0,1)!=='~'){
			if($path_info===$url){
				$route->path_info=$url;
				return true;
			}
		}
		$old_url=substr($old_url,1);
		$new_url=str_replace('$','\\',$new_url);
		$p='/'.str_replace('/','\/',$old_url).'/';
		
		$url=preg_replace($p,$new_url,$path_info);
		if($url===$path_info){return false;}
		
		$path_info=parse_url($url,PHP_URL_PATH);
		$q=parse_url($url,PHP_URL_QUERY);
		parse_str($q,$get);
		$_GET=array_merge($get,$_GET);
		$route->path_info=$path_info;
		return true;
	}
	public function  handel($route)
	{
		foreach($this->rewriteMap as $old_url =>$new_url){
			if($this->matchRewrite($old_url,$new_url,$route)){
				break;
			}
		}

	}
	public function assignRewrite($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->rewriteMap=array_merge($this->rewriteMap,$key);
		}else{
			$this->rewriteMap[$key]=$value;
		}
	}
}
class StrictService
{
	use DNSingleton;
	public static function _before_instance($object)
	{
		if(!DNMVCS::G()->isDev){return $object;}
		$class=get_called_class();
		list($_0,$_1,$caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		if(substr($class,0,0-strlen("LibService"))=="LibService"){
			do{
				if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
				if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
				DNMVCS::ThrowOn(true,"LibService Must Call By Serivce");
			}while(false);
		}else{
			do{
				if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){
					DNMVCS::ThrowOn(true,"ServiceCan not call Service");
				}
				if(substr($caller_class,0,strlen("Service"))=="Service"){
					DNMVCS::ThrowOn(true,"ServiceCan not call Service");
				}
				if(substr($caller_class,0,strlen("\\$namespace\\Model\\"))=="\\$namespace\\Model\\"){
					DNMVCS::ThrowOn(true,"Service Can not call by Model");
				}
				if(substr($caller_class,0,strlen("Model"))=="Model"){
					DNMVCS::ThrowOn(true,"Service Can not call by Model");
				}	
				
			}while(false);
		}
		return $object;
	}	
}
class StrictModel
{
	use DNSingleton;
	public static function _before_instance($object)
	{
		
		if(!DNMVCS::G()->isDev){return $object;}
		list($_0,$_1,$caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		do{
			if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
			if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
			if(substr($caller_class,0,0-strlen("ExModel"))=="ExModel"){break;}
			DNMVCS::ThrowOn(true,"Model Can Only call by Service or ExModel!");
		}while(false);
		return $object;
	}
}

class StrictDBManager extends DNDBManager
{
	use DNWrapper;
	public function __call($method,$args)
	{
		if(in_array($method,['_DB','_DB_W','_DB_R'])){
			$this->checkPermission();
		}
		return parent::__call([$this->obj,$method],$args);
	}
	protected function checkPermission()
	{
		if(!DNMVCS::G()->isDev){return;}
		list($_0,$_1,$_2,$caller,$bak)=$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,5);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		$default_controller_class=DNMVCS::G()->options['default_controller_class'];
		do{
			if($caller_class==$default_controller_class){
				DNMVCS::ThrowOn(true,"DB Can not Call By Controller");
			}
			if(substr($caller_class,0,strlen("\\$namespace\\Controller\\"))=="\\$namespace\\Controller\\"){
				DNMVCS::ThrowOn(true,"DB Can not Call By Controller");
			}
			
			if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){
				DNMVCS::ThrowOn(true,"DB Can not Call By Service");
			}
			if(substr($caller_class,0-strlen("Service"))=="Service"){
				DNMVCS::ThrowOn(true,"DB Can not Call By Service");
			}
		}while(false);
	}
}
class DBExt extends DNDB
{
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
	public function get($table_name,$id,$key='id')
	{
		$sql="select {$table_name} from terms where {$key}=? limit 1";
		return $this->fetch($sql,$id);
	}
	
	public function insert($table_name,$data,$return_last_id=true)
	{
		$sql="insert into {$table_name} set ".$this->quote_array($data);
		$ret=$this->execQuick($sql);
		if(!$return_last_id){return $ret;}
		$ret=$this->pdo->lastInsertId();
		return $ret;
	}
	public function delete($table,$id,$key='id')
	{
		throw new Exception("DNMVCS Notice : override me to delete");
		$sql="delete from {$table_name} where {$key}=? limit 1";
		return $this->execQuick($sql,$id);
	}
	
	public function update($table_name,$id,$data,$key='id')
	{
		if($data[$key]){unset($data[$key]);}
		$frag=$this->quote_array($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=$this->execQuick($sql,$id);
		return $ret;
	}
}


class API
{
	protected static function GetTypeFilter()
	{
		return [
			'boolean'=>FILTER_VALIDATE_BOOLEAN  ,
			'bool'=>FILTER_VALIDATE_BOOLEAN  ,
			'int'=>FILTER_VALIDATE_INT,
			'float'=>FILTER_VALIDATE_FLOAT,
			'string'=>FILTER_SANITIZE_STRING,
		];
	}
	public static function Call($class,$method,$input)
	{
		$f=self::GetTypeFilter();
		$reflect = new ReflectionMethod($class,$method);
		
		$params=$reflect->getParameters();
		$args=array();
		foreach ($params as $i => $param) {
			$name=$param->getName();
			if(isset($input[$name])){
				$type=$param->getType();
				if(null!==$type){
					$type=''.$type;
					if(in_array($type,array_keys($f))){
						$flag=filter_var($input[$name],$f[$type],FILTER_NULL_ON_FAILURE);
						DNMVCS::ThrowOn($flag===null,"Type Unmatch: {$name}",-1);
					}
					
				}
				$args[]=$input[$name];
				continue;
			}else if($param->isDefaultValueAvailable()){
				$args[]=$param->getDefaultValue();
			}else{
				DNMVCS::ThrowOn(true,"Need Parameter: {$name}",-2);
			}
			
		}
		
		$ret=$reflect->invokeArgs(new $service(), $args);
		return $ret;
	}
}
class MedooSimpleInstaller
{
	public static function CreateDBInstance($db_config)
	{
		$dsn=$db_config['dsn'];
		list($driver,$dsn)=explode(':',$dsn);
		$dsn=rtrim($dsn,';');
		$a=explode(';',$dsn);
		$dsn_array['driver']=$driver;
		foreach($a as $v){
			list($key,$value)=explode('=',$v);
			$dsn_array[$key]=$value;
		}
		$db_config['dsn']=$dsn_array;
		$db_config['database_type']='mysql';
		
		return new Medoo($db_config);
	}
}
class MyArgsAssoc
{
	protected static function GetCalledAssocByTrace($trace)
	{
		list($top,$_)=$trace;
		if($top['object']){
			$reflect=new ReflectionMethod($top['object'],$top['function']);
		}else{
			$reflect=new ReflectionFunction($top['function']);
		}
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		return $names;
	}
	
	public static function GetMyArgsAssoc()
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		return self::GetCalledAssocByTrace($trace);
	}
	
	public static function CallWithMyArgsAssoc($callback)
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		$names=self::GetCalledAssocByTrace($trace);
		return ($callback)($names);
	}
}
//mysqldump -uroot -p123456 DnSample -d --opt --skip-dump-date --skip-comments | sed 's/ AUTO_INCREMENT=[0-9]*\b//g' >../data/database.sql

