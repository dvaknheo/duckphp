<?php
namespace DNMVCS;

trait DNWrapper
{
	protected static $objects=[];
	protected $_object_wrapping;
	protected function _wrap_the_object($object)
	{
		$this->_object_wrapping=$object;
	}
	protected function _call_the_object($method,$args)
	{
		return call_user_func_array([$this->_object_wrapping,$method],$args);
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
	public function __set($name,$value){
		$this->_object_wrapping->$name=$value;
	}
	public function __get($name){
		return $this->_object_wrapping->$name;
	}
}

//use with DNSingleton
trait DNStaticCall
{
	use DNSingleton;
	//remark ，method do not public
	public static function __callStatic($method, $params)
    {
		$classname=get_called_class();
        $class=$classname::G();
		return ([$class, $method])(...$params);
    }
}
trait DNSimpleSingleton
{
	protected static $_instances=[];
	public static function G($object=null)
	{
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

class DNFuncionModifer
{
	protected $FunctionMap=[];
	public static function __callStatic($method, $params)
    {
		$temp=self::$FunctionMap[$method]??null;
		if(null==$temp){
			return ($method)(...$params);
		}
		list($func,$header,$footer)=$temp;
		if(null!==$header){($header)(...$params);}
		if(null!==$func){
			$ret=($func)(...$params);
		}else{
			$ret=($method)(...$params);
		}
		if(null!==$footer){($footer)(...$params);}
		return $ret;
    }
	public static function Assign($functionName,$callback=null,$header=null,$footer=null)
	{
		if(null===$callback && null===$header && null===$footer){
			unset(self::$FunctionMap[$functionName]);
			return;
		}
		self::$FunctionMap[$functionName]=[$callback,$header,$footer];
		
	}
}

class SimpleRoute extends DNRoute 
{
	public $options;
	protected $key_for_simple_route='_r';
	
	public function _URL($url=null)
	{
		return _url_by_key($url,$this->key_for_simple_route);
	}
	public function init($options)
	{
		parent::init($options);
		$this->key_for_simple_route=$options['ext']['key_for_simple_route'];
		
		$path_info=_HTTP_REQUEST($this->key_for_simple_route)??'';
		$path_info=ltrim($path_info,'/');
		$this->path_info=$path_info;
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