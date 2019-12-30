<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\SingletonEx;
use DuckPhp\Core\Route;

class RouteHookRouteMap
{
    use SingletonEx;
    public $options = [
        'route_map_important' => [],
        'route_map' => [],
        
    ];
    public function __construct()
    {
    }
    public static function PrependHook($path_info)
    {
        return static::G()->doHook($path_info, false);
    }
    public static function AppendHook($path_info)
    {
        return static::G()->doHook($path_info, true);
    }
    public function init(array $options, object $context = null)
    {
        $this->options = array_intersect_key(array_replace_recursive($this->options, $options) ?? [], $this->options);
        
        if ($context) {
            $context->addRouteHook([static::class,'PrependHook'], 'prepend-inner');
            $context->addRouteHook([static::class,'AppendHook'], 'append-outter');
            if (\method_exists($context, 'extendComponents')) {
                $context->extendComponents(
                    [
                        'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
                        'assignRoute' => [static::class.'::G','assignRoute'],
                        'getRoutes' => [static::class.'::G','getRoutes'],
                        'routeMapNameToRegex' => [static::class.'::G','routeMapNameToRegex'],
                    ],
                    []
                );
                $context->extendComponents(
                    [
                        'getRoutes' => [static::class.'::G','getRoutes'],
                    ],
                    ['C']
                );
            }
        }
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
    public function getRoutes()
    {
        return $this->options;
    }
    protected function matchRoute($pattern_url, $path_info, &$parameters)
    {
        $firstWord = substr($pattern_url, 0, 1);
        if ($firstWord === '~') {
            $flag = preg_match($pattern_url.'~x', $path_info, $m);
            if (!$flag) {
                return false;
            }
            unset($m[0]);
            $parameters = $m; // reference
            return true;
        }
        if ($firstWord === '@') {
            $pattern_url = $this->compile($pattern_url);
            $flag = preg_match($pattern_url, $path_info, $m);
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
            $p = substr($path_info, strlen($pattern_url));
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
    protected function getRouteHandelByMap($routeMap, $path_info, &$parameters)
    {
        $path_info = ltrim($path_info, '/');
        foreach ($routeMap as $pattern => $callback) {
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
            if (false !== strpos($callback, '@')) {
                list($class, $method) = explode('@', $callback);
                return [new $class(),$method];
            } elseif (false !== strpos($callback, '->')) {
                list($class, $method) = explode('->', $callback);
                return [new $class(),$method];
            }
        }
        return $callback;
    }
    public function doHook($path_info, $is_append)
    {
        $map = $is_append ? $this->options['route_map']: $this->options['route_map_important'];
        if (empty($map)) {
            return false;
        }
        
        return $this->doHookByMap($path_info, $map);
    }
    protected function doHookByMap($path_info, $route_map)
    {
        $route = Route::G();
        $parameters = [];
        $callback = $this->getRouteHandelByMap($route_map, $path_info, $parameters);
        if (!$callback) {
            return false;
        }
        if (is_array($callback)) {
            $route->setRouteCallingMethod($callback[1]);
        }
        $route->setParameters($parameters);
        
        ($callback)();
        $callback = null;
        return true;
    }
}
