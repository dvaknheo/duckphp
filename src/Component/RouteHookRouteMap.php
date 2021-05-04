<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class RouteHookRouteMap extends ComponentBase
{
    public $options = [
        'route_map_important' => [],
        'route_map' => [],
        'route_map_by_config_name' => '',
        'route_map_auto_extend_method' => true,
    ];
    protected $route_map = [];
    protected $route_map_important = [];
    protected $is_compiled = false;
    protected $context_class;
    
    public static function PrependHook($path_info)
    {
        return static::G()->doHook($path_info, false);
    }
    public static function AppendHook($path_info)
    {
        return static::G()->doHook($path_info, true);
    }
    //@override
    protected function initOptions(array $options)
    {
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        ($this->context_class)::Route()->addRouteHook([static::class,'PrependHook'], 'prepend-inner');
        ($this->context_class)::Route()->addRouteHook([static::class,'AppendHook'], 'append-outter');
        
        if ($this->options['route_map_by_config_name']) {
            $config = get_class($context)::LoadConfig($this->options['route_map_by_config_name']);
            $this->assignRoute($config['route_map'] ?? []);
            $this->assignImportantRoute($config['route_map_important'] ?? []);
        }
        if ($this->options['route_map_auto_extend_method'] && \method_exists($context, 'extendComponents')) {
            $context->extendComponents(
                [
                    'assignImportantRoute' => static::class . '@assignImportantRoute',
                    'assignRoute' => static::class . '@assignRoute',
                    'routeMapNameToRegex' => static::class . '@routeMapNameToRegex',
                ],
                ['A']
            );
            $context->extendComponents(
                [
                    'getRoutes' => static::class . '@getRoutes',
                ],
                ['C','A']
            );
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
        ($this->context_class)::Route()->setParameters($parameters);
        if (is_string($callback) && !\is_callable($callback)) {
            if (false !== strpos($callback, '@')) {
                list($class, $method) = explode('@', $callback);
                ($this->context_class)::Route()->setRouteCallingMethod($method);
                return [$class::G(),$method];
            } elseif (false !== strpos($callback, '->')) {
                list($class, $method) = explode('->', $callback);
                ($this->context_class)::Route()->setRouteCallingMethod($method);
                return [new $class(),$method];
            }
            /*
            // ???
            elseif (false !== strpos($callback, '::')) {
                list($class, $method) = explode('::', $callback);
                ($this->context_class)::Route()->setRouteCallingMethod($method);
                return [$class,$method];
            }
            //*/
        }
        if (is_array($callback) && isset($callback[1])) {
            $method = $callback[1];
            ($this->context_class)::Route()->setRouteCallingMethod($method);
        }
        return $callback;
    }
    public function doHook($path_info, $is_append)
    {
        if (!$this->options['route_map'] && !$this->options['route_map_important']) {
            return false;
        }
        if (!$this->is_compiled) {
            $namespace_controller = ($this->context_class)::Route()->getControllerNamespacePrefix();
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
