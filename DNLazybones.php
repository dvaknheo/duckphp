<?php
namespace DNMVCS;

class DNLazybones
{
	use DNSingleton;
	const DEFAULT_OPTIONS=[
		'lazy_mode'=>true,
		'use_app_path_as_lazy_path'=>true,
		
		'lazy_path'=>'app',
		'lazy_path_service'=>'Service',
		'lazy_path_model'=>'Model',
		'lazy_path_contorller'=>'Controller',
		
		'lazy_controller_class'=>'DNController',
		'use_namespace_as_lazy_namespace'=>true,
		'with_lazy_controller_namespace'=>true,
	];
	
	public function init($options)
	{
		if($options['lazy_mode']??false){return;}
		
		$this->lazy_path=$options['lazy_path'];
		$this->lazy_path_service=$options['lazy_path_service'];
		$this->lazy_path_model=$options['lazy_path_contorller'];
		$this->lazy_path_contorller=$options['lazy_path_contorller'];
		
		if($options['use_app_path_as_lazy_path']??false){
			$this->lazy_path=$options['path'];
		}
		
		
	}
	protected function loadSeriveClass($class)
	{
		if(strpos($class,'\\')!==false){ return; }
		if('Service'!==substr($class,-strlen('Service'))){
			return false;
		}
		$file=$this->lazy_path_service.str_replace('__','/',$class).'.php';
		require $file;
		return true;
	}
	protected function loadModelClass($class)
	{
		if(strpos($class,'\\')!==false){ return; }
		if('Model'!==substr($class,-strlen('Model'))){
			return false;
		}
		$file=$this->lazy_path_model.'Model'.'/'.$class.'.php';
		require $file;
		return true;
	}
	
	/////////////////////////////
	
	public function runRoute()
	{
		$path_info=DNRoute::G()->path_info;
		$enable_paramters=DNRoute::G()->enable_paramters;
		$class_blocks=explode('/',$path_info);
		$method=array_pop($class_blocks);
		$class_path=implode('/',$class_blocks);
		
		$full_class=$this->getFullClassByNoNameSpace($class_path);
		$callback=DNRoute::G()->getCallback($full_class,$method);
		if($callback){
			return $callback; 
		}
		if(!$enable_paramters ){
			return null;
		}
		list($full_class,$the_method,$parameters,$calling_path)=$this->getClassMethodAndParameters2($class_blocks,$method);
		if(!$full_class){
			return null;
		}
		$method=$the_method;
		DNRoute::G()->parameters=$parameters;
		DNRoute::G()->calling_path=$calling_path;
		return DNRoute::G()->getCallback($full_class,$method);
	}
	protected function getClassMethodAndParameters2($blocks,$method)
	{
		$class=null;
		$paramters=[];
		$callinig_path='';
		$p=implode('/',$blocks);
		$l=count($blocks);
		for($i=0;$i<$l;$i++){
			$class_names=array_slice($blocks,0,$l-$i);
			$parameters=$i?array_slice($blocks,-$i):[];
			$calling_path=implode('/',$class_names);
			
			$class=$this->getFullClassByNoNameSpace($calling_path,true);
			if($class){ break; }
		}
		if(!$class){
			return [null,$method,$parameters,$calling_path];
		}
		array_push($parameters,$method);
		$method=array_shift($parameters);
		$calling_path=$calling_path.'/'.$method;
		
		return [$class,$method,$parameters,$calling_path];
	}
	protected function getFullClassByNoNameSpace($path_class,$confirm=false)
	{
		$class=$this->checkLoadClass($path_class);
		if($class){
			if($confirm){return null;}
			return $class;
		}
		$file=$this->path.$path_class.'.php';
		if(!is_file($file)){
			//if(!$confirm){ $this->error="no file to get class"; }
			return null;
		}
		$this->includeControllerFile($file);
		
		return $this->checkLoadClass($path_class);
	}
	protected function checkLoadClass($path_class)
	{
		$class=$this->prefix_no_namespace_mode . str_replace('/','__',$path_class); // MyProject__Controller__AA__BB_CC;
		
		if(class_exists($class)){return $class; }
		$class=($this->lazy_controller_class)?$this->lazy_controller_class:'';  // DNController
		if(class_exists($class)){return $class; }
		
		///////////
		$class=($this->lazy_controller_class)?$this->namespace_controller.'\\'.$this->lazy_controller_class:''; // MyProject/Controller/DNController;
		if(class_exists($class)){return $class; }
		return null;
	}
	// You can override it; variable indived
	protected function includeControllerFile($file)
	{
		require_once($file);
	}
}