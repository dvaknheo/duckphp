<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Core\App;
use DuckPhp\Ext\Cache;
use DuckPhp\Ext\Console;
use DuckPhp\Ext\DbManager;
use DuckPhp\Ext\EventManager;
use DuckPhp\Ext\Pager;
use DuckPhp\Ext\RouteHookPathInfoCompat;
use DuckPhp\Ext\RouteHookRouteMap;

class DuckPhp extends App
{
    //@override
    protected $core_options = [
        'default_exception_do_log' => true,
        'default_exception_self_display' => true,
        'close_resource_at_output' => false,
        'injected_helper_map' => '',
        
        //// error handler ////
        'error_404' => null,          //'_sys/error-404',
        'error_500' => null,          //'_sys/error-500',
        'error_debug' => null,        //'_sys/error-debug',

        'ext' => [
            DbManager::class => true,
            RouteHookPathInfoCompat::class => true,
            RouteHookRouteMap::class => true,
            Console::class => true,
        ],
        
        'database_auto_method_extend' => true,
        'route_map_auto_extend_method' => true,
    ];
    
    //@override
    public function _Cache($object = null)
    {
        return Cache::G($object);
    }
    //@override
    public function _Pager($object = null)
    {
        $pager = Pager::G($object);
        $pager->options['pager_context_class'] = static::class;
        return $pager;
    }
    //@override
    public function _Db($tag)
    {
        return DbManager::G()->_Db($tag);
    }
    //@override
    public function _DbCloseAll()
    {
        return DbManager::G()->_CloseAll();
    }
    //@override
    public function _DbForRead()
    {
        return DbManager::G()->_DbForRead();
    }
    //@override
    public function _DbForWrite()
    {
        return DbManager::G()->_DbForWrite();
    }
    //@override
    public function _Event()
    {
        return EventManager::G();
    }
    //@override
    public function _FireEvent($event, ...$args)
    {
        return EventManager::G()->fire($event, ...$args);
    }
    //@override
    public function _OnEvent($event, $callback)
    {
        return EventManager::G()->on($event, $callback);
    }
    // setBeforeGetDbHandler
    //'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
    //'assignRoute' => [static::class.'::G','assignRoute'],
    //'routeMapNameToRegex' => [static::class.'::G','routeMapNameToRegex'],
    //'getRoutes' => [static::class.'::G','getRoutes'],
}
