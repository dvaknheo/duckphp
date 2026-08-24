<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Component;

use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;
use DuckPhp\GlobalAdmin\AdminControllerInterface;
use DuckPhp\GlobalUser\UserControllerInterface;

class RouteLister extends ComponentBase
{
    // Route listing for admin permission import, route display, etc.
    public $options = [
        'classes_to_get_controller_path' => [],
    ];
    ////[[
    public function pathInfoFromClassAndMethod($class, $method, $adjuster = null)
    {
        $class_postfix = (string) Route::_()->options['controller_class_postfix'];
        $method_prefix = (string) Route::_()->options['controller_method_prefix'];

        $controller_welcome_class = Route::_()->options['controller_welcome_class'];
        $controller_welcome_method = Route::_()->options['controller_welcome_method'];
        $controller_path_ext = Route::_()->options['controller_path_ext'];
        $controller_url_prefix = Route::_()->options['controller_url_prefix'];


        $namespace_prefix = Route::_()->getControllerNamespacePrefix();
        if (substr($class, 0, strlen($namespace_prefix)) !== $namespace_prefix) {
            return null;
        }

        if ($class_postfix && substr($class, -strlen($class_postfix)) !== $class_postfix) {
            return null;
        }
        $first = substr($class, strlen($namespace_prefix), 0 - strlen($class_postfix));

        if ($adjuster) {
            $first = call_user_func($adjuster, $first);
        }

        if ($method_prefix && substr($method, 0, strlen($method_prefix)) !== $method_prefix) {
            return null; // TODO do_action
        }
        $last = substr($method, strlen($method_prefix));
        [$first, $last] = $this->doControllerClassAdjust($first, $last);

        if ($first === $controller_welcome_class && $last === $controller_welcome_method) {
            return $controller_url_prefix? $controller_url_prefix:'';
        }
        if ($first === $controller_welcome_class) {
            return $controller_url_prefix.$last.$controller_path_ext;
        }

        return $controller_url_prefix.$first. '/' .$last.$controller_path_ext;
    }

    protected function doControllerClassAdjust(string $first, string $method): array
    {
        $adj = is_array(Route::_()->options['controller_class_adjust']) ? Route::_()->options['controller_class_adjust'] : explode(';', Route::_()->options['controller_class_adjust']);
        if (!$adj) {
            return [$first,$method];
        }
        foreach ($adj as $v) {
            if ($v === 'uc_method') {
                $method = ucfirst($method);
            } elseif ($v === 'uc_class') {
                $blocks = explode('/', $first);
                $w = array_pop($blocks);
                $w = lcfirst((string)$w);
                array_push($blocks, $w);
                $first = implode('/', $blocks);
            } elseif ($v === 'uc_full_class') {
                $blocks = explode('/', $first);
                $blocks = array_map('lcfirst', $blocks);
                $first = implode('/', $blocks);
            }
        }
        return [$first,$method];
    }
    protected function getAllControllerClasses(): array
    {
        $prefix = Route::_()->getControllerNamespacePrefix();
        $classToTest[] = Route::_()->options['controller_welcome_class'].Route::_()->options['controller_class_postfix'];
        $classToTest[] = 'Helper';
        $classToTest[] = 'Base';

        $classToTest = array_merge($classToTest, $this->options['classes_to_get_controller_path']);
        $path = '';
        foreach ($classToTest as $base_class) {
            try {
                $class = $prefix. basename(str_replace("\\", '/', $base_class));
                // @phpstan-ignore-next-line
                $path = dirname((new \ReflectionClass($class))->getFileName()).'/';
            } catch (\ReflectionException $ex) {
                continue;
            }
            break;
        }

        if (!$path) {
            return [];
        }
        $directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = \iterator_to_array($iterator, false);

        $ret = [];
        $postfix = Route::_()->options['controller_class_postfix'];
        foreach ($files as $file) {
            if (substr($file, -strlen('.php')) !== '.php') {
                continue;
            };
            $key = substr($file, strlen($path), -strlen('.php'));
            $key = str_replace('/', '\\', $prefix.$key);
            if (!empty($postfix) && substr($key, -strlen($postfix)) != $postfix) {
                continue;
            }
            $ret[$key] = $file;
        }
        return $ret;
    }
    protected function getControllerMethods(string $full_class, ?callable $adjuster = null): array
    {
        try {
            // @phpstan-ignore-next-line
            $ref = new \ReflectionClass($full_class);
            $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        } catch (\ReflectionException $ex) {
            return [];
        }

        $ret = [];
        foreach ($methods as $method) {
            if ($method->isStatic()) {
                continue;
            }
            if ($method->isConstructor()) {
                continue;
            }
            $function = $method->getName();
            $path_info = $this->pathInfoFromClassAndMethod($full_class, $function, $adjuster);
            if (!isset($path_info)) {
                continue;
            }
            $ret[$full_class.'->'.$function] = $path_info;
        }
        return $ret;
    }
    /**
     * List all routes as recordset.
     * Order: rewrite_map, route_map_important, controller routes, route_map.
     * @return array<int, array<string, mixed>>
     */
    public function listAll(bool $with_children = true, bool $only_controller = false, bool $only_admin = false, bool $only_user = false): array
    {
        if ($only_admin && $only_user) {
            throw new \InvalidArgumentException('only_admin and only_user cannot both be true');
        }
        if ($only_admin || $only_user) {
            $only_controller = true;
        }
        $maps = RouteHookRouteMap::_()->getRouteMaps();
        $ret = [];
        if (!$only_controller) {
            $phase = App::Phase();
            // 1. rewrite_map
            foreach (RouteHookRewrite::_()->getRewrites() as $url => $rewrite) {
                $ret[] = [
                    'url' => $url, 'phase' => $phase, 'controller' => '', 'method' => '',
                    'is_admin' => false, 'is_user' => false,
                    'route_map' => false, 'route_map_important' => false, 'rewrite_map' => true,
                ];
            }
            // 2. route_map_important
            foreach ($maps['route_map_important'] as $url => $callback) {
                [$controller, $method] = $this->parseRouteMapCallback($callback);
                $ret[] = [
                    'url' => $url, 'phase' => $phase, 'controller' => $controller, 'method' => $method,
                    'is_admin' => false, 'is_user' => false,
                    'route_map' => false, 'route_map_important' => true, 'rewrite_map' => false,
                ];
            }
        }
        // 3. controller routes
        $controller_rows = $this->listControllerRows($only_admin, $only_user);
        $ret = array_merge($ret, $controller_rows);
        // 4. route_map
        if (!$only_controller) {
            $phase = App::Phase();
            foreach ($maps['route_map'] as $url => $callback) {
                [$controller, $method] = $this->parseRouteMapCallback($callback);
                $ret[] = [
                    'url' => $url, 'phase' => $phase, 'controller' => $controller, 'method' => $method,
                    'is_admin' => false, 'is_user' => false,
                    'route_map' => true, 'route_map_important' => false, 'rewrite_map' => false,
                ];
            }
        }

        // with_children: inline recursion over child apps (no callback)
        if ($with_children) {
            $parent_app = App::_();
            $last_phase = App::Phase();
            foreach ($parent_app->options['app'] as $class => $app_options) {
                if ($parent_app->getThisChild($class) === null) {
                    // e.g. app entry is false (disabled)
                    continue;
                }
                $ret = array_merge($ret, $this->listAll($with_children, $only_controller, $only_admin, $only_user));
                App::Phase($last_phase);
            }
        }

        return $ret;
    }
    /**
     * @return array<int, array<string, mixed>>
     */
    protected function listControllerRows(bool $only_admin, bool $only_user): array
    {
        $rows = [];
        $phase = App::Phase();
        foreach ($this->getAllControllerClasses() as $class => $file) {
            $is_admin = $this->isSubclassOf($class, AdminControllerInterface::class);
            $is_user = $this->isSubclassOf($class, UserControllerInterface::class);
            if ($only_admin && !$is_admin) {
                continue;
            }
            if ($only_user && !$is_user) {
                continue;
            }
            foreach ($this->getControllerMethods($class) as $full => $url) {
                [$controller, $method] = explode('->', $full);
                $rows[] = [
                    'url' => $url, 'phase' => $phase, 'controller' => $controller, 'method' => $method,
                    'is_admin' => $is_admin, 'is_user' => $is_user,
                    'route_map' => false, 'route_map_important' => false, 'rewrite_map' => false,
                ];
            }
        }
        return $rows;
    }
    /**
     * @return array{0: string, 1: string}
     */
    protected function parseRouteMapCallback(string $callback): array
    {
        if (substr($callback, 0, 1) === '~') {
            $callback = Route::_()->getControllerNamespacePrefix() . substr($callback, 1);
        }
        $pos = strpos($callback, '@');
        if ($pos === false) {
            return [$callback, ''];
        }
        return [substr($callback, 0, $pos), substr($callback, $pos + 1)];
    }
    protected function isSubclassOf(string $class, string $interface): bool
    {
        try {
            return (new \ReflectionClass($class))->isSubclassOf($interface);
        } catch (\ReflectionException $ex) {
            return false;
        }
    }
}
