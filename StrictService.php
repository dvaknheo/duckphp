<?php
namespace DNMVCS;

trait StrictService
{
	use DNSingleton { G as public parentG;}
	public static function G($object=null)
	{
		$object=self::_before_instance($object);
		return static::parentG($object);
	}
	
	public static function _before_instance($object)
	{
		if(!DNMVCS::Developing()){return $object;}
		list($_0,$_1,$caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		if(substr($class,0,0-strlen("LibService"))=="BatchService"){
			return $object;
		}
		if(substr($class,0,0-strlen("LibService"))=="LibService"){
			do{
				if(substr($caller_class,0,strlen("$namespace\\Service\\"))=="$namespace\\Service\\"){break;}
				if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
				DNMVCS::ThrowOn(true,"LibService Must Call By Serivce($caller_class)");
			}while(false);
		}else{
			do{
				if(substr($caller_class,0,strlen("$namespace\\Service\\"))=="$namespace\\Service\\"){
					DNMVCS::ThrowOn(true,"Service Can not call Service($caller_class)");
				}
				if(substr($caller_class,0,strlen("Service"))=="Service"){
					DNMVCS::ThrowOn(true,"Service Can not call Service($caller_class)");
				}
				if(substr($caller_class,0,strlen("$namespace\\Model\\"))=="$namespace\\Model\\"){
					DNMVCS::ThrowOn(true,"Service Can not call by Model($caller_class)");
				}
				if(substr($caller_class,0,strlen("Model"))=="Model"){
					DNMVCS::ThrowOn(true,"Service Can not call by Model($caller_class)");
				}	
				
			}while(false);
		}
		return $object;
	}	
}
