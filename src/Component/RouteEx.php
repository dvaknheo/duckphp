<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\Route;

class RouteEx extends Route
{
    public $options = [
        'controllerex_welcome_class' => 'Main',
        'controllerex_class_postfix' => '',
        'controllerex_enable_slash' => false,
        'controllerex_path_ext' => '',
        'controller_stop_static_method' => true,
        'controllerex_strict_mode' => true,
        'controllerex_methtod_for_miss' => '',
    ];
    /*

        */
    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        parent::__construct();
    }
    public function defaultGetRouteCallback($path_info)
    {
        $path_info = ltrim((string)$path_info, '/');
        
        /*
        if ($this->options['controller_path_prefix'] ?? false) {
            $prefix = trim($this->options['controller_path_prefix'], '/').'/';
            $l = strlen($prefix);
            if (substr($path_info, 0, $l) !== $prefix) {
                $this->route_error = "path_prefix error";
                return null;
            }
            $path_info = substr($path_info, $l - 1);
            $path_info = ltrim((string)$path_info, '/');
        }
        //*/
        if ($this->options['controllerex_enable_slash']) {
            $path_info = rtrim($path_info, '/');
        }
        if (!empty($this->options['controllerex_path_ext']) && !empty($path_info)) {
            $l = strlen($this->options['controllerex_path_ext']);
            if (substr($path_info, -$l) !== $this->options['controllerex_path_ext']) {
                $this->route_error = "path_extention error";
                return null;
            }
            $path_info = substr($path_info, 0, -$l);
        }
        
        $t = explode('/', $path_info);
        $method = array_pop($t);
        $path_class = implode('/', $t);
        
        $this->calling_path = $path_class?$path_info:$this->welcome_class.'/'.$method;
        $this->route_error = '';
        
        if ($this->options['controller_hide_boot_class'] && $path_class === $this->welcome_class) {
            $this->route_error = "controller_hide_boot_class! {$this->welcome_class} ";
            return null;
        }
        $path_class = $path_class ?: $this->welcome_class;
        $full_class = $this->namespace_prefix.str_replace('/', '\\', $path_class).$this->options['controllerex_class_postfix'];
        if (!class_exists($full_class)) {
            $this->route_error = "can't find class($full_class) by $path_class ";
            return null;
        }
        if ($this->options['controllerex_strict_mode']) {
            /** @var object */ $t = ''.ltrim($full_class, '\\');
            $full_class = $t; // phpstan ,I hate you.
            if ($full_class !== (new \ReflectionClass($full_class))->getName()) {
                $this->route_error = "can't find class($full_class) by $path_class (strict_mode miss case).";
                return null;
            }
        }
        
        
        $this->calling_class = $full_class;
        $this->calling_method = !empty($method)?$method:'index';
        
        /** @var string */
        $base_class = str_replace('~', $this->namespace_prefix, $this->options['controller_base_class']);
        /** @var mixed */ $class = $full_class; // phpstan
        if (!empty($base_class) && !is_subclass_of($class, $base_class)) {
            $this->route_error = "no the controller_base_class! {$base_class} ";
            return null;
        }
        $object = $this->createControllerObject($full_class);
        return $this->getMethodToCall($object, $method);
    }

    protected function createControllerObject($full_class)
    {
        $full_class = $this->options['controller_class_map'][$full_class] ?? $full_class;
        return new $full_class();
    }
    protected function getMethodToCall($object, $method)
    {
        $method = ($method === '') ? $this->index_method : $method;
        if (substr($method, 0, 2) == '__') {
            $this->route_error = 'can not call hidden method';
            return null;
        }
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($this->options['controller_prefix_post'] && $_SERVER['REQUEST_METHOD'] === 'POST' && method_exists($object, $this->options['controller_prefix_post'].$method)) {
            $method = $this->options['controller_prefix_post'].$method;
        }
        if ($this->options['controllerex_methtod_for_miss']) {
            if (!method_exists($object, $method)) {
                $method = $this->options['controllerex_methtod_for_miss'];
            }
            if ($this->options['controllerex_strict_mode']) {
                try {
                    if ($method !== (new \ReflectionMethod($object, $method))->getName()) {
                        $this->route_error = 'method can not call(strict_mode miss case)';
                        return null;
                    }
                } catch (\ReflectionException $ex) {
                    $this->route_error = 'method can not call';
                    return null;
                }
            } else {
                if (!is_callable([$object,$method])) {
                    $this->route_error = 'method can not call';
                    return null;
                }
            }
        }
        
        if ($this->options['controller_stop_static_method']) {
            $ref = new \ReflectionMethod($object, $method);
            if ($ref->isStatic()) {
                $this->route_error = 'can not call static function';
                return null;
            }
        }
        return [$object,$method];
    }
}
