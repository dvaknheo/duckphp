<?php
/**
 这个类是单独使用的
 
 call api 调用　api　的时候会很方便，　input 参数传入　$_GET　就行了。利用好 PHP　的反射
 serverice ,model ,两个调用方法提供的是 nodejs 方式的调用
 DNMVCSEx::Service('MyService')
 DNMVCSEx::Model('Model')
*/
namespace DNMVCS;

function CallAPI($class,$method,$input)
{
	$f=array(
		'int'=>FILTER_VALIDATE_INT,
		'float'=>FILTER_VALIDATE_FLOAT,
		'string'=>FILTER_SANITIZE_STRING,
	);
	
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
					DNMVCS::ThrowOn($flag===null,"参数类型错误: {$name}",-1);
				}
				
			}
			$args[]=$input[$name];
			continue;
		}else if($param->isDefaultValueAvailable()){
			$args[]=$param->getDefaultValue();
		}else{
			DNMVCS::ThrowOn(true,"缺少参数: {$name}",-2);
		}
		
	}
	
	$ret=$reflect->invokeArgs(new $service(), $args);
	return $ret;
}
class DNDebugCallWithMyAssoc
{
	// 这是个内部用的函数，获取 参数名称的关联数组 // TODDo 转移到 trait;
	// 父类  foo_with_names($assoc);
	// 子类1 foo($id=1,$title=2)=>_with_names($this->getArgAssoc());
	// 子类2 foo($name=>'a',$value=>"")=> DNDebugCallWithMyAssoc::GO($this->foo_with_names);
	// 
	public static function getCalledAssoc()
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		list($top,$_)=$trace;
		DNMVCS::ThrowOn(!$top['object'],"必须在类里调用");
		
		$reflect=new ReflectionMethod($top['object'],$top['function']);
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		
		return $names;
	}

	// 这个就连带调用 __FUNCTION__._with_names 了
	protected function calledWithMyAssoc($callback)
	{
		$trace=debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT ,2);
		list($top,$_)=$trace;
		DNMVCS::ThrowOn(!$top['object'],"必须在类里调用");
		
		$reflect=new ReflectionMethod($top['object'],$top['function']);
		$params=$reflect->getParameters();
		$names=array();
		foreach($params as $v){
			$names[]=$v->getName();
		}
		return ($callback)($names);
	}
}