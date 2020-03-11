# Helper\AppHelper

## 简介

App 助手类。 
助手类，全静态方法

## 公开方法
public static function IsDebug()
public static function Platform()
public static function trace_dump()
public static function var_dump(...$args)

public static function OnException($ex)
{
    return App::OnException($ex);
}
public static function IsRunning()
{
    return App::IsRunning();
}
public static function InException()
{
    return App::InException();
}

public static function assignPathNamespace($path, $namespace = null)
{
    return App::assignPathNamespace($path, $namespace);
}
public static function addRouteHook($hook, $position, $once = true)
{
    return App::addRouteHook($hook, $position, $once);
}
public static function setUrlHandler($callback)
{
    return App::setUrlHandler($callback);
}
//
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
public static function &GLOBALS($k, $v = null)
{
    return App::GLOBALS($k, $v);
}
public static function &STATICS($k, $v = null, $_level = 1)
{
    return App::STATICS($k, $v, $_level + 1);
}
public static function &CLASS_STATICS($class_name, $var_name)
{
    return App::CLASS_STATICS($class_name, $var_name);
}

### 默认扩展的方法
Import

    [DuckPhp\Ext\Misc::Import](xx)

    
DI
    
    DuckPhp\Ext\Misc::DI
RecordsetUrl
    
    DuckPhp\Ext\Misc::RecordsetUrl
RecordsetH
    
    DuckPhp\Ext\Misc::RecordsetH
CallAPI
    
    DuckPhp\Ext\Misc::CallAPI
DB
    
    DuckPhp\Ext\DBManager::DB
DB_R
    
    DuckPhp\Ext\DBManager::DB_R
DB_W
    
    DuckPhp\Ext\DBManager::DB_W
setDBHandler
    
    DuckPhp\Ext\DBManager::G::setDBHandler
setBeforeGetDBHandler
    
    DuckPhp\Ext\DBManager::G::setBeforeGetDBHandler
assignRewrite
    
    DuckPhp\Ext\RouteHookRewrite::G::assignRewrite
getRewrites
    
    DuckPhp\Ext\RouteHookRewrite::G::getRewrites
assignImportantRoute
    
    DuckPhp\Ext\RouteHookRouteMap::G::assignImportantRoute
assignRoute
    
    DuckPhp\Ext\RouteHookRouteMap::G::assignRoute
getRoutes
    
    DuckPhp\Ext\RouteHookRouteMap::G::getRoutes
routeMapNameToRegex

    DuckPhp\Ext\RouteHookRouteMap::G::routeMapNameToRegex
## 详解
