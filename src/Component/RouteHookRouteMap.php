<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;

class RouteHookRouteMap extends ComponentBase
{
    public $options = [
        'controller_url_prefix' => '',
        'route_map_important' => [],
        'route_map' => [],
    ];
    protected $route_map = [];
    protected $route_map_important = [];
    protected $is_compiled = false;
    
    public static function PrependHook($path_info)
    {
        // $path_info = Route::_()::PathInfo();
        return static::_()->doHook($path_info, false);
    }
    public static function AppendHook($path_info)
    {
        // $path_info = Route::_()::PathInfo();
        return static::_()->doHook($path_info, true);
    }
    //@override
    protected function initContext(object $context)
    {
        Route::_()->addRouteHook([static::class,'PrependHook'], 'prepend-inner');
        Route::_()->addRouteHook([static::class,'AppendHook'], 'append-outter');
    }
    public function compile($pattern_url, $rules = [])
    {
        $pattern_url = substr($pattern_url, 1);
        $ret = preg_replace_callback('/\{(\w+)(:([^\}]+))?(\??)\}/', function ($matches) use ($rules) {
            $rule = $rules[$matches[1]] ?? '\w+';
            $rule = !empty($matches[3])?$matches[3]:$rule;
            return "(?<".$matches[1].">".$rule.")".$matches[4];
        }, $pattern_url);
        $ret = '~^'.$ret.'$ # '.$pattern_url.'~x';
        return $ret;
    }
    protected function compileMap($map, $namespace_controller)
    {
        $ret = [];
        foreach ($map as $pattern_url => $callback) {
            $firstWord = substr($pattern_url, 0, 1);
            if ($firstWord === '@') {
                $pattern_url = $this->compile($pattern_url);
            }
            if (is_string($callback) && substr($callback, 0, 1) === '~') {
                $callback = str_replace('~', $namespace_controller, $callback);
            }
            $ret[$pattern_url] = $callback;
        }
        return $ret;
    }
    
    public function assignRoute($key, $value = null)
    {
        if (is_array($key) && $value === null) {
            $this->options['route_map'] = array_merge($this->options['route_map'], $key);
        } else {
            $this->options['route_map'][$key] = $value;
        }
    }
    public function assignImportantRoute($key, $value = null)
    {
        if (is_array($key) && $value === null) {
            $this->options['route_map_important'] = array_merge($this->options['route_map_important'], $key);
        } else {
            $this->options['route_map_important'][$key] = $value;
        }
    }
    public function getRouteMaps()
    {
        $ret = [
            'route_map_important' => $this->options['route_map_important'],
            'route_map' => $this->options['route_map'],
        ];
        return $ret;
    }
    protected function matchRoute($pattern_url, $path_info, &$parameters)
    {
        $firstWord = substr($pattern_url, 0, 1);
        if ($firstWord === '^') {
            $flag = preg_match('~'.$pattern_url.'$~x', $path_info, $m);
            if (!$flag) {
                return false;
            }
            unset($m[0]);
            $parameters = $m; // reference
            return true;
        }
        if ($firstWord === '/') {
            $pattern_url = substr($pattern_url, 1);
        }
        $lastWord = substr($pattern_url, -1);
        if ($lastWord === '*') {
            $pattern_url = substr($pattern_url, 0, -1);
            $p = ''.substr($path_info, strlen($pattern_url));
            if (strlen($p) > 0 && substr($p, 0, 1) !== '/') {
                return false;
            }
            $m = explode('/', $p);
            array_shift($m);
            $parameters = $m; // reference
            return true;
        }
        return ($pattern_url === $path_info) ? true:false;
    }
    protected function getRouteHandelByMap($routeMap, $path_info)
    {
        $parameters = [];
        $path_info = ltrim($path_info, '/');
        $prefix = $this->options['controller_url_prefix'];
        if ($prefix && substr($path_info, 0, strlen($prefix)) !== $prefix) {
            return null;
        }
        
        foreach ($routeMap as $pattern => $callback) {
            if (!$this->matchRoute($pattern, $path_info, $parameters)) {
                continue;
            }
            return $this->adjustCallback($callback, $parameters);
        }
        return null;
    }
    protected function adjustCallback($callback, $parameters)
    {
        Route::_()->setParameters($parameters);
        if (is_string($callback) && !\is_callable($callback)) {
            if (false !== strpos($callback, '@')) {
                list($class, $method) = explode('@', $callback);
                Route::_()->setRouteCallingMethod($method);
                return [$class::_(),$method];
            } elseif (false !== strpos($callback, '->')) {
                list($class, $method) = explode('->', $callback);
                Route::_()->setRouteCallingMethod($method);
                return [new $class(),$method];
            }
            /*
            // ???
            elseif (false !== strpos($callback, '::')) {
                list($class, $method) = explode('::', $callback);
                Route::_()->setRouteCallingMethod($method);
                return [$class,$method];
            }
            //*/
        }
        if (is_array($callback) && isset($callback[1])) {
            $method = $callback[1];
            Route::_()->calling_method = $method;
        }
        return $callback;
    }
    public function doHook($path_info, $is_append)
    {
        if (!$this->options['route_map'] && !$this->options['route_map_important']) {
            return false;
        }
        if (!$this->is_compiled) {
            $namespace_controller = Route::_()->getControllerNamespacePrefix();
            $this->route_map = $this->compileMap($this->options['route_map'], $namespace_controller);
            $this->route_map_important = $this->compileMap($this->options['route_map_important'], $namespace_controller);
            $this->is_compiled = true;
        }
        $map = $is_append ? $this->route_map : $this->route_map_important;
        
        return $this->doHookByMap($path_info, $map);
    }
    protected function doHookByMap($path_info, $route_map)
    {
        $callback = $this->getRouteHandelByMap($route_map, $path_info);
        if (!$callback) {
            return false;
        }
        ($callback)();
        $callback = null;
        return true;
    }
}
