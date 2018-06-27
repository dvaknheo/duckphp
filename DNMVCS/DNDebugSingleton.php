<?php
namespace DNMVCS;
class DNDebugService
{
	use DNSingleton;
	public static function _before_instance($object)
	{
		if(!DNMVCS::G()->isDev){return;}
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
	}	
}
class DNDebugModel
{
	use DNSingleton;
	public static function _before_instance($object)
	{
	
		if(!DNMVCS::G()->isDev){return;}
		list($_0,$_1,$caller)=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
		$caller_class=$caller['class'];
		$namespace=DNMVCS::G()->options['namespace'];
		do{
			if(substr($caller_class,0,strlen("\\$namespace\\Service\\"))=="\\$namespace\\Service\\"){break;}
			if(substr($caller_class,0,0-strlen("Service"))=="Service"){break;}
			if(substr($caller_class,0,0-strlen("ExModel"))=="ExModel"){break;}
			DNMVCS::ThrowOn(true,"Model Can Only call by Service or ExModel!");
		}while(false);
	}
}

class DNDebugDBManager extends DNDBManager
{
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
	public function _DB()
	{
		$this->checkPermission();
		return parent::_DB();
	}
	public function _DB_W()
	{
		$this->checkPermission();
		return parent::_DB_W();
	}
	
	public function _DB_R()
	{
		$this->checkPermission();
		return parent::_DB_R();
	}
}