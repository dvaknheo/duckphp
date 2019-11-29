<?php
namespace DNMVCS\Ext;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\Route;

class RouteHookRouteMap
{
    use SingletonEx;
    const DEFAULT_OPTIONS=[
        'route_map'=>[],
    ];
    
    protected $route_map=[];
    ////
    public static function Hook($path_info)
    {
        return static::G()->doHook($path_info);
    }
    ////
    public function init($options=[], $context=null)
    {
        $this->route_map=array_merge($this->route_map, $options['route_map']??[]);
        
        if ($context) {
            Route::G()->add404Handler([static::class,'Hook']);
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
    protected function matchRoute($pattern_url, $path_info, &$parameters)
    {
        $firstWord=substr($pattern_url, 0, 1);
        if ($firstWord==='~') {
            $flag=preg_match($pattern_url.'~x', $path_info, $m);
            if (!$flag) {
                return false;
            }
            unset($m[0]);
            $parameters=$m; // reference
            return true;
        }
        if ($firstWord==='/') {
            $pattern_url=substr($pattern_url, 1);
        }
        $lastWord=substr($pattern_url, -1);
        if ($lastWord==='*') {
            $pattern_url=substr($pattern_url, 0, -1);
            $p=substr($path_info, strlen($pattern_url));
            if (strlen($p)>0 && substr($p, 0, 1)!=='/') {
                return false;
            }
            $m=explode('/', $p);
            array_shift($m);
            $parameters=$m; // reference
            return true;
        }
        return ($pattern_url === $path_info) ? true:false;
    }
    protected function getRouteHandelByMap($routeMap, $path_info, &$parameters)
    {
        foreach ($routeMap as $pattern =>$callback) {
            if (!$this->matchRoute($pattern, $path_info, $parameters)) {
                continue;
            }
            return $this->adjustCallback($callback);
        }
        return null;
    }
    protected function adjustCallback($callback)
    {
        if (is_string($callback)) {
            if (false!==strpos($callback, '@')) {
                list($class, $method)=explode('@', $callback);
                return [new $class(),$method];
            } elseif (false!==strpos($callback, '->')) {
                list($class, $method)=explode('->', $callback);
                return [new $class(),$method];
            }
        }
        return $callback;
    }
    public function doHook($path_info)
    {
        $path_info=ltrim($path_info, '/');
        $route=Route::G();
        $callback=$this->getRouteHandelByMap($this->route_map, $path_info, $route->parameters);
        if (!$callback) {
            return false;
        }
        ($callback)();
        $callback=null;
        return true;
    }
}
