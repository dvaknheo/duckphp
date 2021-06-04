<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Core\App;

trait AdvanceHelperTrait
{
    public static function CallException($ex)
    {
        return App::CallException($ex);
    }
    public static function isRunning()
    {
        return App::isRunning();
    }
    public static function isInException()
    {
        return App::isInException();
    }
    
    public static function assignPathNamespace($path, $namespace = null)
    {
        return App::assignPathNamespace($path, $namespace);
    }
    public static function addRouteHook($hook, $position, $once = true)
    {
        return App::addRouteHook($hook, $position, $once);
    }
    public static function add404RouteHook($callback)
    {
        return App::add404RouteHook($callback);
    }
    public static function replaceControllerSingelton($old_class, $new_class)
    {
        return App::replaceControllerSingelton($old_class, $new_class);
    }
    public static function getViewData()
    {
        return App::getViewData();
    }
    ////////////
    public static function SESSION($key = null, $default = null)
    {
        return App::SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return App::FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return App::SessionSet($key, $value);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return App::CookieSet($key, $value, $expire);
    }
    public static function Event()
    {
        return App::Event();
    }
    public static function OnEvent($event, $callback)
    {
        return App::OnEvent($event, $callback);
    }
    ////////////////////
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return App::header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return App::setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return App::exit($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return App::set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return App::register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options = [])
    {
        return App::session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return App::session_id($session_id);
    }
    public static function session_destroy()
    {
        return App::session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return App::session_set_save_handler($handler);
    }
    //////////////////////////////////////
    public static function extendComponents($method_map, $components = [])
    {
        return App::G()->extendComponents($method_map, $components);
    }
    public static function cloneHelpers($new_namespace, $componentClassMap = [])
    {
        return App::G()->cloneHelpers($new_namespace, $componentClassMap);
    }
    public static function addBeforeShowHandler($handler)
    {
        return App::G()->addBeforeShowHandler($handler);
    }
    public static function getDynamicComponentClasses()
    {
        return App::G()->getDynamicComponentClasses();
    }
    public static function addDynamicComponentClass($class)
    {
        return App::G()->addDynamicComponentClass($class);
    }
}
