<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Core\App;
use DuckPhp\Ext\DbManager;
use DuckPhp\Ext\EventManager;
use DuckPhp\Ext\Pager;
use DuckPhp\Ext\RouteHookPathInfoByGet;
use DuckPhp\Ext\RouteHookRouteMap;

class DuckPhp extends App
{
    const VERSION = '1.2.6-dev';
    
    public function __construct()
    {
        parent::__construct();
        $ext = [
            DbManager::class => true,
            RouteHookPathInfoByGet::class => true,
            RouteHookRouteMap::class => true,
        ];
        $this->options['ext'] = array_merge($ext, $this->options['ext']);
    }
    protected function onBeforeOutput()
    {
        if(!$this->options['close_resource_at_output']){
            return;
        }
        DbManager::CloseAllDb();
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
}
