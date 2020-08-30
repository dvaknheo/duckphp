<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Core\App;
use DuckPhp\Ext\DBManager;
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
        $options = [
            'log_sql_query' => false,
            'log_sql_level' => 'debug',
            'db_before_query_handler' => [static::class, 'OnQuery']
        ];
        $this->options = array_merge($options, $this->options);
        $ext = [
            DBManager::class => true,
            RouteHookPathInfoByGet::class => true,
            RouteHookRouteMap::class => true,
        ];
        $this->options['ext'] = array_merge($ext, $this->options['ext']);
    }
    //@override
    public static function OnQuery($db, $sql, ...$args)
    {
        return static::G()->_OnQuery($db, $sql, ...$args);
    }
    //@override
    public function _OnQuery($db, $sql, ...$args)
    {
        if (!$this->options['log_sql_query']) {
            DBManager::G()->setBeforeQueryHandler($db, null);
            return;
        }
        static::Logger()->log($this->options['log_sql_level'], '[sql]: ' . $sql, $args);
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
        return DBManager::G()->_Db($tag);
    }
    //@override
    public function _DbForRead()
    {
        return DBManager::G()->_DbForRead();
    }
    //@override
    public function _DbForWrite()
    {
        return DBManager::G()->_DbForWrite();
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
