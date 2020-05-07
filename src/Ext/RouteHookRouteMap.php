<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentInterface;
use DuckPhp\Core\Route;
use DuckPhp\Core\SingletonEx;

class RouteHookRouteMap implements ComponentInterface
{
    use SingletonEx;
    public $options = [
        'route_map_important' => [],
        'route_map' => [],
        
    ];
    protected $is_inited = false;
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
        
        if ($context && \method_exists($context, 'addRouteHook')) {
            $context->addRouteHook([static::class,'PrependHook'], 'prepend-inner');
            $context->addRouteHook([static::class,'AppendHook'], 'append-outter');
        }
        if ($context && \method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
                    'assignRoute' => [static::class.'::G','assignRoute'],
                    'routeMapNameToRegex' => [static::class.'::G','routeMapNameToRegex'],
                ],
                ['A']
            );
            $context->extendComponents(
                [
                    'getRoutes' => [static::class.'::G','getRoutes'],
                ],
                ['C','A']
            );
        }
        $this->is_inited = true;
        return $this;
    }
    public function isInited(): bool
    {
        return $this->is_inited;
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
        if ($firstWord === '^') {
            $flag = preg_match('~'.$pattern_url.'$~x', $path_info, $m);
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
    protected function getRouteHandelByMap($routeMap, $path_info)
    {
        $parameters = [];
        $path_info = ltrim($path_info, '/');
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
        Route::G()->setParameters($parameters);
        if (is_string($callback)) {
            if (false !== strpos($callback, '@')) {
                list($class, $method) = explode('@', $callback);
                Route::G()->setRouteCallingMethod($method);
                return [new $class(),$method];
            } elseif (false !== strpos($callback, '->')) {
                list($class, $method) = explode('->', $callback);
                Route::G()->setRouteCallingMethod($method);
                return [new $class(),$method];
            } elseif (false !== strpos($callback, '::')) {
                list($class, $method) = explode('::', $callback);
                Route::G()->setRouteCallingMethod($method);
                return [$class,$method];
            }
        }
        if (is_array($callback) && isset($callback[1])) {
            $method = $callback[1];
            Route::G()->setRouteCallingMethod($method);
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
        $callback = $this->getRouteHandelByMap($route_map, $path_info);
        if (!$callback) {
            return false;
        }
        ($callback)();
        $callback = null;
        return true;
    }
}
