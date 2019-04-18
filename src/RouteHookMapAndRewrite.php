<?php
namespace DNMVCS;

use DNMVCS\DNSingleton;
use DNMVCS\RouteHookMapAndRewrite;

class RouteHookMapAndRewrite
{
    use DNSingleton;
    protected $rewrite_map=[];
    protected $route_map=[];
    protected $enable_paramters=false;
    public function init($options=[], $context=null)
    {
        $this->rewrite_map=array_merge($this->rewrite_map, $options['rewrite_map']??[]);
        $this->route_map=array_merge($this->route_map, $options['route_map']??[]);
        
        if ($context) {
            $this->enable_paramters=$context->options['enable_paramters'];
            $context->addRouteHook([RouteHookMapAndRewrite::G(),'hook'], true);
            // $context->extendClassMethodByThirdParty(static::class,[],['assignRewrite','assignRoute']);
        }
    }
    public function assignRewrite($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->rewrite_map=array_merge($this->rewrite_map, $key);
        } else {
            $this->rewrite_map[$key]=$value;
        }
    }
    public function assignRoute($key, $value=null)
    {
        if (is_array($key)&& $value===null) {
            $this->route_map=array_merge($this->route_map, $key);
        } else {
            $this->route_map[$key]=$value;
        }
    }
    
    public function replaceRegexUrl($input_url, $template_url, $new_url)
    {
        if (substr($template_url, 0, 1)!=='~') {
            return null;
        }
        
        $input_path=parse_url($input_url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($input_url, PHP_URL_QUERY), $input_get);
        
        //$template_path=parse_url($template_url,PHP_URL_PATH);
        //$template_get=[];
        parse_str(parse_url($template_url, PHP_URL_QUERY), $template_get);
        $p='/'.str_replace('/', '\/', substr($template_url, 1)).'/A';
        if (!preg_match($p, $input_path)) {
            return null;
        }
        //if(array_diff_assoc($input_get,$template_get)){ return null; }
        
        $new_url=str_replace('$', '\\', $new_url);
        $new_url=preg_replace($p, $new_url, $input_path);
        
        $new_path=parse_url($new_url, PHP_URL_PATH);
        $new_get=[];
        parse_str(parse_url($new_url, PHP_URL_QUERY), $new_get);
        
        $get=array_merge($input_get, $new_get);
        $query=$get?'?'.http_build_query($get):'';
        return $new_path.$query;
    }
    public function replaceNormalUrl($input_url, $template_url, $new_url)
    {
        if (substr($template_url, 0, 1)==='~') {
            return null;
        }
        
        $input_path=parse_url($input_url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($input_url, PHP_URL_QUERY), $input_get);
        
        $template_path=parse_url($template_url, PHP_URL_PATH);
        $template_get=[];
        parse_str(parse_url($template_url, PHP_URL_QUERY), $template_get);
        
        if (array_diff_assoc($input_get, $template_get)) {
            return null;
        }
        
        $new_path=parse_url($new_url, PHP_URL_PATH);
        $new_get=[];
        parse_str(parse_url($new_url, PHP_URL_QUERY), $new_get);
        if ($input_path!==$template_path) {
            return null;
        }
        
        $get=array_merge($input_get, $new_get);
        $query=$get?'?'.http_build_query($get):'';
        
        return $new_path.$query;
    }
    public function filteRewrite($input_url)
    {
        foreach ($this->rewrite_map as $template_url=>$new_url) {
            $ret=$this->replaceNormalUrl($input_url, $template_url, $new_url);
            if ($ret!==null) {
                return $ret;
            }
            $ret=$this->replaceRegexUrl($input_url, $template_url, $new_url);
            if ($ret!==null) {
                return $ret;
            }
        }
        return null;
    }
    protected function matchRoute($pattern_url, $path_info, $route, $enable_paramters)
    {
        $request_method=$route->request_method;
        
        $pattern='/^(([A-Z_]+)\s+)?(~)?\/?(.*)\/?$/';
        $flag=preg_match($pattern, $pattern_url, $m);
        if (!$flag) {
            return false;
        }
        $method=$m[2];
        $is_regex=$m[3];
        $url=$m[4];
        if ($method && $method!==$request_method) {
            return false;
        }
        if (!$is_regex) {
            $params=explode('/', $path_info);
            $url_params=explode('/', $url);
            if (!$enable_paramters) {
                return ($url_params===$params)?true:false;
            }
            if ($url_params === array_slice($params, 0, count($url_params))) {
                $route->parameters=array_slice($params, 0, count($url_params));
                return true;
            } else {
                return false;
            }
        }
        
        $p='/'.str_replace('/', '\/', $url).'/';
        $flag=preg_match($p, $path_info, $m);
        
        if (!$flag) {
            return false;
        }
        array_shift($m);
        $route->parameters=$m;
        return true;
    }
    protected function getRouteHandelByMap($route, $routeMap)
    {
        $path_info=$route->path_info;
        $enable_paramters=$this->enable_paramters;
        
        foreach ($routeMap as $pattern =>$callback) {
            if (!$this->matchRoute($pattern, $path_info, $route, $enable_paramters)) {
                continue;
            }
            if (!is_string($callback)) {
                return $callback;
            }
            if (false!==strpos($callback, '->')) {
                list($class, $method)=explode('->', $callback);
                return [new $class(),$method];
            }
            return $callback;
        }
        return null;
    }
    protected function changeRouteUrl($route, $url)
    {
        $path=parse_url($url, PHP_URL_PATH);
        $input_get=[];
        parse_str(parse_url($url, PHP_URL_QUERY), $input_get);
        $route->path_info=$path;
        DNSuperGlobal::G()->_SERVER['init_get']=DNSuperGlobal::G()->_GET;
        DNSuperGlobal::G()->_GET=$input_get;
    }
    protected function hookRewrite($route)
    {
        $path_info=$route->path_info;
        
        $uri=DNSuperGlobal::G()->_SERVER['REQUEST_URI'];
        $query=parse_url($uri, PHP_URL_QUERY);
        $query=$query?'?'.$query:'';
        $input_url=$path_info.$query;
        foreach ($this->rewrite_map as $template_url=>$new_url) {
            $url=$this->replaceNormalUrl($input_url, $template_url, $new_url);
            if ($url!==null) {
                $this->changeRouteUrl($route, $url);
            }
            $url=$this->replaceRegexUrl($input_url, $template_url, $new_url);
            if ($url!==null) {
                $this->changeRouteUrl($route, $url);
            }
        }
    }
    protected function hookRouteMap($route)
    {
        $route->callback=$this->getRouteHandelByMap($route, $this->route_map);
    }
    public function hook($route)
    {
        $this->hookRewrite($route);
        $this->hookRouteMap($route);
    }
}
