<?php
/**
 这个类是单独使用的
 
 call api 调用　api　的时候会很方便，　input 参数传入　$_GET　就行了。利用好 PHP　的反射
 serverice ,model ,两个调用方法提供的是 nodejs 方式的调用
 DNMVCSEx::Service('MyService')
 DNMVCSEx::Model('Model')
*/
class DNMVCSEx extends DNMVCS
{
	protected  $services=array();
	protected  $models=array();

	//单独使用
	public static function CallAPI($service,$method,$input)
	{
		$f=array(
			'int'=>FILTER_VALIDATE_INT,
			'float'=>FILTER_VALIDATE_FLOAT,
			'string'=>FILTER_SANITIZE_STRING,
		);
		
		$reflect = new ReflectionMethod($service,$method);
		
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
						DNException::ThrowOn($flag===null,"参数类型错误: {$name}");
					}
					
				}
				$args[]=$input[$name];
				continue;
			}else if($param->isDefaultValueAvailable()){
				$args[]=$param->getDefaultValue();
			}else{
				DNException::ThrowOn(true,"缺少参数: {$name}");
			}
			
		}
		
		$ret=$reflect->invokeArgs(new $service(), $args);
		return $ret;
	}
	public static function Service($name)
	{
		return self::G()->_load($name,'service');
	}
	public static function Model($name)
	{
		return self::G()->_load($name,'model');
	}
	public function _load($name,$type)
	{
		if($type=='service'){
			$containner=&$this->services;
		}
		if($type=='model'){
			$containner=&$this->models;
		}
		if(isset($containner[$name])){
			return $container[$name];
		}
		$filename=$this->path.$type.'/'.$name.'.php';
		$data=file_get_contents($filename);
		
		$data=preg_replace('/\/\*(.*?)\*\//s','',$data);
		$data=preg_replace('/\/\/.*$/m','',$data);
		
		$flag=preg_match('/^s*namespace\s*(\w+)/m',$data,$m);
		$namespace=$flag?$m[1]:'';
		$flag=preg_match('/^s*class\s*(\w+)/m',$data,$m);
		$class=$flag?$m[1]:'';
		$fullclass=$namespace?$namespace.'\\'.$class:$class;
		
		include $filename;
		
		$ret=new $fullclass();
		
		$container[$name]=$ret;
		return $ret;
	}
	
	// 这是个内部用的函数，获取 参数名称的关联数组
	protected function getArgAssoc()
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		$top=array_pop($trace);
		
		//TODO ，和类相分离 这里写得不通用
		$reflect=new ReflectionMethod($top['object'],$top['function']);
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		
		return $names;
	}
	// 这个就连带调用 __FUNCTION__._with_names 了
	protected function callWithNames()
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		$top=array_pop($trace);
		
		$reflect=new ReflectionMethod($top['object'],$top['function']);
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		
		$func=$top['function'];
		$func_in=$func.'_with_names';
		return $this->$func_in($names);
	}
}