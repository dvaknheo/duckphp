<?php
namespace DNMVCS;
// 用于不想用 PHP 的超级变量的场合 在 swoole 应用里用到
class SwooleSuperGlobalServer extends SuperGlobalBase
{
	public function init($request)
	{
		foreach($request->header as $k=>$v){
			$k='HTTP_'.str_replace('-','_',strtoupper($k));
			$this->data[$k]=$v;
		}
		foreach($request->server as $k=>$v){
			$this->data[strtoupper($k)]=$v;
		}
	}
}

class SwooleSuperGlobalGet extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=$request->get??[];
	}
}
class SwooleSuperGlobalPost extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=$request->post??[];
	}
}
class SwooleSuperGlobalRequest extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=array_merge($request->get??[],$request->post??[]);
	}
}
class SwooleSuperGlobalCookie extends SuperGlobalBase
{
	public function init($request)
	{
		$this->data=$request->cookie??[];
	}
}