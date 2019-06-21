<?php
namespace DNMVCS\Ext;

use DNMVCS\SingletonEx;

class RouteHookRouteMap
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'route_map'=>[],
    ];
    
    protected $route_map=[];
    public function init($options=[], $context=null)
    {
        $this->route_map=array_merge($this->route_map, $options['route_map']??[]);
        
        if ($context) {
            $context->addRouteHook([static::class,'Hook']);
            // $context->extendClassMethodByThirdParty(static::class,[],['assignRoute','getRoutes']);
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
    public function getRoutes()
    {
        return $this->route_map;
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
        $enable_paramters=$route->controller_enable_paramters;
        
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
    public static function Hook($route)
    {
        return static::G()->_Hook($route);
    }
    public function _Hook($route)
    {
        $route->callback=$this->getRouteHandelByMap($route, $this->route_map);
    }
}
