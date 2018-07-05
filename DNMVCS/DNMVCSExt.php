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
		if($data[$key]){unset($data[$key]);}
		$frag=DNDB::G()->quote_array($data);
		$sql="update {$table_name} set ".$frag." where {$key}=?";
		$ret=DNDB::G()->exec($sql,$id);
		return $ret;
	}
}


class API
{
	protected static function GetTypeFilter()
	{
		return [
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
		return $name;
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

