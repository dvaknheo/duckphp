<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;

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
    protected function matchRoute($pattern_url, $path_info, $route)
    {
        $firstWord=substr($pattern_url, 0, 1);
        if ($firstWord==='~') {
            $flag=preg_match($pattern_url.'~x', $path_info, $m);
            if (!$flag) {
                return false;
            }
            unset($m[0]);
            $route->parameters=$m;
            return true;
        }
        if ($firstWord==='/') {
            $pattern_url=substr($pattern_url, 1);
        }
        $lastWord=substr($pattern_url, -1);
        if ($lastWord==='*') {
            $pattern_url=substr($pattern_url, -1);
            if ($pattern_url!=$path_info) {
                return false;
            }
            $p=substr($path_info, strlen($pattern_url));
            if (strlen($p)>0 && substr($p, 0, 1)!=='/') {
                return false;
            }
            $m=explode('/', $p);
            array_shift($m);
            $route->parameters=$m;
            return false;
        }
        return ($pattern_url === $path_info) ? true:false;
    }
    protected function getRouteHandelByMap($route, $routeMap)
    {
        $path_info=$route->path_info;
        foreach ($routeMap as $pattern =>$callback) {
            if (!$this->matchRoute($pattern, $path_info, $route)) {
                continue;
            }
            
            $route->stopRunDefaultHandler();
            $callback=$this->adjustCallback($callback);
            
            return $callback;
        }
        return null;
    }
    protected function adjustCallback($callback)
    {
        //TODO  , add @ 
        if (is_string($callback)){
            if(false!==strpos($callback, '@')) {
                list($class, $method)=explode('@', $callback);
                return [new $class(),$method];
            } elseif (false!==strpos($callback, '->')) {
                list($class, $method)=explode('->', $callback);
                return [new $class(),$method];
            }
        }
        return $callback;
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
