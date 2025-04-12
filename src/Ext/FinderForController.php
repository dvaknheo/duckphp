<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;
use DuckPhp\Foundation\Helper;
use DuckPhp\GlobalAdmin\AdminControllerInterface;
use DuckPhp\GlobalUser\UserControllerInterface;

//@codeCoverageIgnoreStart
class FinderForController extends ComponentBase
{
    // 暂时没测试，没文档， 是枚举控制器用的扩展。
    ////[[[[
    public function pathInfoFromClassAndMethod($class, $method, $adjuster = null)
    {
        $class_postfix = Route::_()->options['controller_class_postfix'];
        $method_prefix = Route::_()->options['controller_method_prefix'];
        
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
        
        if ($first === $controller_welcome_class && $last === $controller_welcome_method) {
            return $controller_url_prefix? $controller_url_prefix:'';
        }
        if ($first === $controller_welcome_class) {
            return $controller_url_prefix.$last.$controller_path_ext;
        }
        [$first, $method] = $this->doControllerClassAdjust($first, $method);
        
        return $controller_url_prefix.$first. '/' .$last.$controller_path_ext;
    }
    
    protected function doControllerClassAdjust($first, $method)
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
                $w = lcfirst($w ?? '');
                array_push($blocks, $w);
                $first = implode('/', $blocks);
            } elseif ($v === 'uc_full_class') {
                $blocks = explode('/', $first);
                array_map('lcfirst', $blocks);
                $first = implode('/', $blocks);
            }
        }
        return [$first,$method];
    }
    protected function getAllControllerClasses()
    {
        $prefix = Route::_()->getControllerNamespacePrefix();
        $classToTest[] = Route::_()->options['controller_welcome_class'].Route::_()->options['controller_class_postfix'];
        $classToTest[] = 'Helper';
        $classToTest[] = 'Base';
        $path = '';
        foreach ($classToTest as $base_class) {
            try {
                $class = $prefix.$base_class;
                // @phpstan-ignore-next-line
                $path = dirname((new \ReflectionClass($class))->getFileName()).'/';
            } catch (\ReflectionException $ex) {
                continue;
            }
            break;
        }
        if (!$path) {
            $namespace = App::Current()->options['namespace'];
            $base_app = App::Current()->getOverridingClass();
            if (substr($prefix, 0, strlen($namespace.'\\')) === $namespace.'\\') {
                $reflect = new \ReflectionClass($base_app);
                $filename = $reflect->getFileName();
                $filename_relative = str_replace('\\', '/', $base_app).'.php';
                $base_path = substr($filename.'', 0, -strlen($filename_relative));
                $path = $base_path.str_replace('\\', '/', $prefix);
            }
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
    protected function getControllerMethods($full_class, $adjuster = null)
    {
        try {
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
    public function getRoutePathInfoMap($adjuster = null)
    {
        $controllers = $this->getAllControllerClasses();
        $ret = [];
        foreach ($controllers as $class => $file) {
            $ret = array_merge($ret, $this->getControllerMethods($class, $adjuster));
        }
        return $ret;
    }
    public function getRoutePathInfoMapWithChildren($adjuster = null)
    {
        $ret = $this->getRoutePathInfoMap($adjuster);
        Helper::recursiveApps(
            $ret,
            function ($app_class, &$ret) use ($adjuster) {
                $data = $this->getRoutePathInfoMap($adjuster);
                $ret = array_merge($ret, $data);
            }
        );
        return $ret;
    }
    
    public function getAllAdminController()
    {
        $ret = [];
        Helper::recursiveApps(
            $ret,
            function ($app_class, &$ret) {
                $data = $this->getAllControllerClasses();
                $ret = array_merge($ret, $data);
            }
        );
        $ret2 = array_filter($ret, function ($key) {
            try {
                $obj = new \ReflectionClass($key);
                return $obj->isSubclassOf(\DuckPhp\GlobalAdmin\AdminControllerInterface::class);
            } catch (\ReflectionException $ex) {
                return false;
            }
        }, \ARRAY_FILTER_USE_KEY);
        return array_keys($ret2);
    }
    public function getAllUserController()
    {
        $ret = [];
        Helper::recursiveApps(
            $ret,
            function ($app_class, &$ret) {
                $data = $this->getAllControllerClasses();
                $ret = array_merge($ret, $data);
            }
        );
        $ret2 = array_filter($ret, function ($key) {
            try {
                $obj = new \ReflectionClass($key);
                return $obj->isSubclassOf(UserControllerInterface::class);
            } catch (\ReflectionException $ex) {
                return false;
            }
        }, \ARRAY_FILTER_USE_KEY);
        return array_keys($ret2);
    }
}//@codeCoverageIgnoreEnd
