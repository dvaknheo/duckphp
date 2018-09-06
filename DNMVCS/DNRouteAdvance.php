<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;

class RouteRewriteHook
{
	use DNSingleton;
	protected $rewriteMap=[];
	protected function mergeHttpGet($get)
	{
		if(class_exists(__NAMESPACE__.'\SuperGlobal\GET' ,false)){
			$data=array_merge($get, SuperGlobal\GET::All());
			foreach($data as $k=>$v){
				SuperGlobal\GET::Set($k,$v);
			}
			return;
		}
		$_GET=array_merge($get,$_GET??[]);
	}
	public function matchRewrite($old_url,$new_url,$route)
	{
		$path_info=$route->path_info;
		if(substr($old_url,0,1)!=='~'){
			if($path_info===$url){
				$route->path_info=$url;
				return true;
			}
		}
		$old_url=substr($old_url,1);
		$new_url=str_replace('$','\\',$new_url);
		$p='/'.str_replace('/','\/',$old_url).'/';
		
		$url=preg_replace($p,$new_url,$path_info);
		if($url===$path_info){return false;}
		
		$path_info=parse_url($url,PHP_URL_PATH);
		$q=parse_url($url,PHP_URL_QUERY);
		parse_str($q,$get);
		$this->mergeHttpGet($get);
		$route->path_info=$path_info;
		return true;
	}
	public function  hook($route)
	{
		foreach($this->rewriteMap as $old_url =>$new_url){
			if($this->matchRewrite($old_url,$new_url,$route)){
				break;
			}
		}

	}
	public function assignRewrite($key,$value=null)
	{
		if(is_array($key)&& $value===null){
			$this->rewriteMap=array_merge($this->rewriteMap,$key);
		}else{
			$this->rewriteMap[$key]=$value;
		}
	}
}
class RouteMapHook
{
	use DNSingleton;
	protected $routeMap=[];
	protected function matchRoute($pattern_url,$path_info,$route)
	{
		$pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
		$flag=preg_match($pattern,$pattern_url,$m);
		if(!$flag){return false;}
		$method=$m[2];
		$is_regex=$m[3];
		$url=$m[4];
		if($method && $method!==$route->request_method){return false;}
		if(!$is_regex){
			$params=explode('/',$path_info);
			$url_params=explode('/',$url);
			if(!$route->enable_paramters){
				return ($url_params===$params)?true:false;
			}
			if($url_params === array_slice($params,0,count($url_params))){
				$route->parameters=array_slice($params,0,count($url_params));
				return true;
			}else{
				return false;
			}
			
		}
		
		$p='/'.str_replace('/','\/',$url).'/';
		$flag=preg_match($p,$path_info,$m);
		
		if(!$flag){return false;}
		array_shift($m);
		$route->parameters=$m;
		return true;
	}
	protected function getRouteHandelByMap($route)
	{
		foreach($this->routeMap as $pattern =>$callback){
			if(!$this->matchRoute($pattern,$route->path_info,$route)){continue;}
			if(!is_string($callback)){return $callback;}
			if(false!==strpos($callback,'->')){
				$obj=new $class;
				return array($obj,$method);
			}
			return $callback;
		}
		
		return null;
	}
	public function  hook($route)
	{
		$route->callback=$this->getRouteHandelByMap($route);
	}
	public function assignRoute($key,$callback=null)
	{
		if(is_array($key)&& $callback===null){
			$this->routeMap=array_merge($this->routeMap,$key);
		}else{
			$this->routeMap[$key]=$callback;
		}
	}
}

class DNRouteAdvance
{
	use DNSingleton;
	
	protected $is_installed=false;	
	public function init()
	{
		if($this->is_installed){return;}
		$this->is_installed=true;
		DNRoute::G()->addRouteHook([RouteMapHook::G(),'hook'],true);
		DNRoute::G()->addRouteHook([RouteRewriteHook::G(),'hook'],true);
	}
	public function run()
	{
		RouteRewriteHook::G()->assignRewrite(DN::G()->options['rewrite_list']);
		RouteMapHook::G()->assignRoute(DN::G()->options['route_list']);
		//
	}
}
