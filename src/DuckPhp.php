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
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\App;

class DuckPhp extends App
{
    public function __construct()
    {
        $this->core_options['ext'] = [
            DbManager::class => true,
            RouteHookRouteMap::class => true,
        ];
        $this->core_options['route_map_auto_extend_method'] = false;
        $this->core_options['database_auto_extend_method'] = false;

        parent::__construct();
    }
    protected function initAfterOverride(array $options, object $context = null)
    {
        if (PHP_SAPI === 'cli') {
            Console::G()->options['cli_default_command_class'] = DuckPhpCommand::class;
            $this->options['ext'][Console::class] = $this->options['ext'][Console::class] ?? true;
            $this->options['ext'][DuckPhpCommand::class] = $this->options['ext'][DuckPhpCommand::class] ?? true;
        }
        if (($options['path_info_compact_enable'] ?? false) || ($this->options['path_info_compact_enable'] ?? false)) {
            $this->options['ext'][RouteHookPathInfoCompat::class] = $this->options['ext'][RouteHookPathInfoCompat::class] ?? true;
        }
        return parent::initAfterOverride($options, $context);
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
    // setBeforeGetDbHandler
    //'assignImportantRoute' => [static::class.'::G','assignImportantRoute'],
    //'assignRoute' => [static::class.'::G','assignRoute'],
    //'routeMapNameToRegex' => [static::class.'::G','routeMapNameToRegex'],
    //'getRoutes' => [static::class.'::G','getRoutes'],
}
