<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Helper;

use DuckPhp\Component\DbManager;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;
use DuckPhp\Core\SingletonTrait;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;

trait AppHelperTrait
{
    use SingletonTrait;
    public static function CallException($ex)
    {
        return ExceptionManager::CallException($ex);
    }
    public static function isRunning()
    {
        return Runtime::_()->isRunning();
    }
    public static function isInException()
    {
        return Runtime::_()->isInException();
    }
    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
    {
        return Route::_()->addRouteHook($callback, $position, $once);
    }
    public static function replaceController($old_class, $new_class)
    {
        return Route::_()->replaceController($old_class, $new_class);
    }
    public static function getViewData()
    {
        return View::_()->getViewData();
    }
    //////////////
    public static function DbCloseAll()
    {
        return DbManager::_()->_DbCloseAll(); //TODO;
    }
    public static function SESSION($key = null, $default = null)
    {
        return SuperGlobal::_()->_SESSION($key, $default);
    }
    public static function FILES($key = null, $default = null)
    {
        return SuperGlobal::_()->_FILES($key, $default);
    }
    public static function SessionSet($key, $value)
    {
        return SuperGlobal::_()->_SessionSet($key, $value);
    }
    public static function SessionUnset($key)
    {
        return SuperGlobal::_()->_SessionUnset($key);
    }
    public static function SessionGet($key, $default = null)
    {
        return SuperGlobal::_()->_SessionGet($key, $default);
    }
    public static function CookieSet($key, $value, $expire = 0)
    {
        return SuperGlobal::_()->_CookieSet($key, $value, $expire);
    }
    public static function CookieGet($key, $default = null)
    {
        return SuperGlobal::_()->_CookieGet($key, $default);
    }
    ////////////////////
    public static function system_wrapper_replace(array $funcs)
    {
        return SystemWrapper::_()->_system_wrapper_replace($funcs);
    }
    public static function system_wrapper_get_providers():array
    {
        return SystemWrapper::_()->_system_wrapper_get_providers();
    }
    public static function header($output, bool $replace = true, int $http_response_code = 0)
    {
        return SystemWrapper::_()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        return SystemWrapper::_()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit($code = 0)
    {
        return SystemWrapper::_()->_exit($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return SystemWrapper::_()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return SystemWrapper::_()->_register_shutdown_function($callback, ...$args);
    }
    public static function session_start(array $options = [])
    {
        return SystemWrapper::_()->_session_start($options);
    }
    public static function session_id($session_id = null)
    {
        return SystemWrapper::_()->_session_id($session_id);
    }
    public static function session_destroy()
    {
        return SystemWrapper::_()->_session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SystemWrapper::_()->_session_set_save_handler($handler);
    }
    public static function mime_content_type($file)
    {
        return SystemWrapper::_()->_mime_content_type($file);
    }
    ////////////////////////////////////////////
    public static function setBeforeGetDbHandler($db_before_get_object_handler)
    {
        return DbManager::_()->setBeforeGetDbHandler($db_before_get_object_handler);
    }
    public static function Redis($tag = 0)
    {
        return RedisManager::Redis($tag);
    }
    public static function getRoutes()
    {
        return RouteHookRouteMap::_()->getRoutes();
    }
    public static function assignRoute($key, $value = null)
    {
        return RouteHookRouteMap::_()->assignRoute($key, $value);
    }
    public static function assignImportantRoute($key, $value = null)
    {
        return RouteHookRouteMap::_()->assignImportantRoute($key, $value);
    }
    public static function assignRewrite($key, $value = null)
    {
        return RouteHookRewrite::_()->assignRewrite($key, $value);
    }
    public static function getRewrites()
    {
        return RouteHookRewrite::_()->getRewrites();
    }
}
