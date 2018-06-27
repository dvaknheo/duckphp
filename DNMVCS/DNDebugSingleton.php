<?php
namespace DNMVCS;

class DNDebugService
{
	protected static $_instances=[];
	public static function G($object=null)
	{
		$class=get_called_class();

		$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
		$caller=array_pop($backtrace);
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
				if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
				if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
				DNMVCS::ThrowOn(true,"ServiceCan not call service");
				if(substr($caller_class,0,strlen("\\$namespace\\Model\\"))=="\\$namespace\\Model\\"){break;}
				if(substr($caller_class,0,0-strlen("Model"))=="Model"){break;}	
				DNMVCS::ThrowOn(true,"Service Can not call by Model");
			}while(false);
		}
		
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
class DNDebugModel
{
	protected static $_instances=[];
	public static function G($object=null)
	{
		$backtrace=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
		$caller=array_pop($backtrace);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		do{
			if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
			if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
			if(substr($caller_class,0,0-strlen("ExModel"))=="ExModel"){break;}
			DNMVCS::ThrowOn(true,"Model Can Only call by Service or ExModel!");
		}while(false);
		
		//// because this you can not use parent::G();
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

class DNDebugDBManager
{
	use DNSingleton;
	
	public $db=null;
	public $db_r=null;
	public function __construct()
	{
	}
	public function _DB()
	{
		$caller=array_pop($backtrace);
		$caller_class=$caller['class'];
		//call controller can not use DB directly;
		// service can not use  DB directly
		
		return parent::_DB();
	}
}