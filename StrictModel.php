<?php
namespace DNMVCS;

class StrictModel
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
		do{
			if(substr($caller_class,0,strlen("$namespace\\Service\\"))=="$namespace\\Service\\"){break;}
			if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
			if(substr($caller_class,0,0-strlen("ExModel"))=="ExModel"){break;}
			DNMVCS::ThrowOn(true,"Model Can Only call by Service or ExModel!");
		}while(false);
		return $object;
	}
}
