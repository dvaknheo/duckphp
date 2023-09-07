<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Component\Cache;
use DuckPhp\Component\Console;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\Component\EventManager;
use DuckPhp\Component\Pager;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\App;

class DuckPhp extends App
{
    protected $admin;
    protected $user;
    protected function initComponents(array $options, object $context = null)
    {
        parent::initComponents($options, $context);
        
        $this->options['database_auto_extend_method'] = $this->options['database_auto_extend_method'] ?? false;
        DbManager::G()->init($this->options, $this);
        
        if (PHP_SAPI === 'cli') {
            DuckPhpCommand::G()->init($this->options, $this);
            Console::G()->init($this->options, $this);
            Console::G()->options['cli_default_command_class'] = DuckPhpCommand::class;
        }
        if (($options['path_info_compact_enable'] ?? false) || ($this->options['path_info_compact_enable'] ?? false)) {
            $this->options['route_map_auto_extend_method'] = $this->options['route_map_auto_extend_method'] ?? false;
            RouteHookPathInfoCompat::G()->init($this->options, $this);
        }
        $phase = $this->_Phase();
        if ($phase) {
            $this->getContainer()->addPublicClasses([
                Console::class,
                DbManager::class,
                RedisManager::class
                ]);
        }
        //我们要加个可以InstallableTrait;
        
        return $this;
    }
    public static function Admin($admin = null)
    {
        return static::G()->_Admin($admin);
    }
    public function _Admin($admin = null)
    {
        if ($admin) {
            $this->admin = $admin;
        }
        return $this->admin;
    }
    public static function AdminId()
    {
        return static::Admin()->id();
    }
    public static function User($user = null)
    {
        return static::G()->_User($user);
    }
    public function _User($user = null)
    {
        if ($user) {
            $this->user = $user;
        }
        return $this->user;
    }
    public static function UserId()
    {
        return static::User()->id();
    }
    //@override
    public function _Cache($object = null)
    {
        return Cache::G($object);
    }
    //@override
    public function _Pager($object = null)
    {
        return Pager::G($object);
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

    public static function setBeforeGetDbHandler($db_before_get_object_handler)
    {
        return DbManager::G()->setBeforeGetDbHandler($db_before_get_object_handler);
    }
    public static function getRoutes()
    {
        return RouteHookRouteMap::G()->getRoutes();
    }
    public static function assignRoute($key, $value = null)
    {
        return RouteHookRouteMap::G()->assignRoute($key, $value);
    }
    public static function assignImportantRoute($key, $value = null)
    {
        return RouteHookRouteMap::G()->assignImportantRoute($key, $value);
    }
}
