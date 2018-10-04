<?php
namespace DNMVCS;
use \DNMVCS\DNMVCS as DN;

class DNRouteAdvance
{
	use DNSingleton;
	protected function mergeHttpGet($get)
	{
		if(class_exists('\DNMVCS\SuperGlobal\GET',false)){
			$data=array_merge($get, SuperGlobal\GET::All());
			foreach($data as $k=>$v){
				SuperGlobal\GET::Set($k,$v);
			}
			if(defined('DN_SWOOLE_SERVER_RUNNING')){return;}
		}
		$_GET=array_merge($get,$_GET??[]);
	}
	public function matchRewrite($old_url,$new_url,$route)
	{
		$path_info=$route->path_info;
		if(substr($old_url,0,1)!=='~'){
			if($path_info===$old_url){
				$route->path_info=$new_url;
				return true;
			}else{
				return false;
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
	protected function matchRoute($pattern_url,$path_info,$route)
	{
		$request_method=$route->request_method;
		$enable_paramters=$route->enable_paramters;
		
		$pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
		$flag=preg_match($pattern,$pattern_url,$m);
		if(!$flag){return false;}
		$method=$m[2];
		$is_regex=$m[3];
		$url=$m[4];
		if($method && $method!==$request_method){return false;}
		if(!$is_regex){
			$params=explode('/',$path_info);
			$url_params=explode('/',$url);
			if(!$enable_paramters){
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
	protected function getRouteHandelByMap($route,$routeMap)
	{
		$path_info=$route->path_info;
		foreach($routeMap as $pattern =>$callback){
			if(!$this->matchRoute($pattern,$path_info,$route)){continue;}
			if(!is_string($callback)){return $callback;}
			if(false!==strpos($callback,'->')){
				list($class,$method)=explode('->',$callback);
				return [new $class(),$method];
			}
			return $callback;
		}
		return null;
	}
	protected function hookRewrite($route)
	{
		$rewriteMap=DN::G()->options['rewrite_list'];
		foreach($rewriteMap as $old_url =>$new_url){
			if($this->matchRewrite($old_url,$new_url,$route)){
				break;
			}
		}
	}
	protected function hookRouteMap($route)
	{
		$route->callback=$this->getRouteHandelByMap($route,DN::G()->options['route_list']);
	}
	public function hook($route)
	{
		$this->hookRewrite($route);
		$this->hookRouteMap($route);
	}
	
	protected $is_installed=false;
	public function install()
	{
		if($this->is_installed){return;}
		$this->is_installed=true;
		DNRoute::G()->addRouteHook([static::G(),'hook'],true);
		return $this;
	}
}
