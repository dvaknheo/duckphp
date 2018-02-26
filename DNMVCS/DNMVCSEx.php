<?php

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
	public function template()
	{
		$files=array(
			'config/config.php',
			'config/setting.php',
			'controller/Main.php',
			'model/TestModel.php',
			'service/TestService.php',
			'view/main.php',
			'view/_sys/error-404.php',
			'view/_sys/error-500.php',
			'view/_sys/error-debug.php',
			'view/_sys/error-exception.php',
			'www/index.php',
		);
	}
}