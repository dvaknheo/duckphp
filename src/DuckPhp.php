<?php
declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK, Lazy

namespace DuckPhp;

use DuckPhp\Component\Command;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\Lang;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookResource;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;
use DuckPhp\FastInstaller\FastInstaller;
use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalUser\GlobalUser;

class DuckPhp extends App
{
    protected $common_options = [
        'data_file_enable' => false,
        'ext' => [
            ExtOptionsLoader::class => 'data_file_enable',
            Lang::class => true,
            RouteHookRewrite::class => true,
            RouteHookRouteMap::class => true,
            RouteHookResource::class => true,
            RouteHookPathInfoCompat::class => 'path_info_compact_enable',
        ],
        
        'session_prefix' => null,
        'table_prefix' => null,
        
        'class_admin' => '',
        'class_user' => '',
        'database_driver' => '',

        'cli_command_with_app' => false,
        'cli_command_with_common' => true,

        'lang_default' => null,
        'lang_final' => null,
        'local_database' => false,
        'local_redis' => false,

        //'error_maintain' => null,
        //'error_need_install' => null,

        //'install_need_database' => true,
        //'install_need_redis' => false,
        //
        
        //*
        // 'path_config' => 'config',
        // 'database' => null,
        // 'database_driver' => '',
        // 'database_list' => null,
        // 'database_list_reload_by_setting' => true,
        // 'database_list_try_single' => true,
        // 'database_log_sql_query' => false,
        // 'database_log_sql_level' => 'debug',
        // 'database_class' => '',
        
        // 'redis' => null,
        // 'redis_list' => null,
        // 'redis_list_reload_by_setting' => true,
        // 'redis_list_try_single' => true,
        
        // 'controller_url_prefix' => '',
        // 'route_map_important' => [],
        // 'route_map' => [],
        
        // 'rewrite_map' => [],

        // 'path_info_compact_enable' => false,
        // 'path_info_compact_action_key' => '_r',
        // 'path_info_compact_class_key' => '',
        
        //*/
    ];
    protected function initComponentsOfRoot($components, $default): void
    {
        $my_components = [
            DbManager::class => true,
            RedisManager::class => true,
            GlobalAdmin::class => self::EXT_SKIP_INIT,
            GlobalUser::class => self::EXT_SKIP_INIT,
            GlobalEvent::class => self::EXT_SKIP_INIT,
        ];
        $components = array_merge($components, $my_components);
        
        parent::initComponentsOfRoot($components, $default);
         
        DbManager::_()->init($this->options, $this);
        RedisManager::_()->init($this->options, $this);
        $this->options['database_driver'] = DbManager::_()->options['database_driver'];
    }
    ////////////////////
    protected function initComponentsOfInner($components, $default): void
    {
        //$my_components = [

        //];
        //$components = array_merge($classes, $my_components);
        
        parent::initComponentsOfInner($components, $default);
        
        if ($this->isLocalDatabase()) {
            $this->createLocalObject(DbManager::class);
            DbManager::_()->init($this->options, $this);
        }
        if ($this->isLocalRedis()) {
            $this->createLocalObject(RedisManager::class);
            RedisManager::_()->init($this->options, $this);
        }
        if ($this->options['class_admin']) {
            $class = $this->options['class_admin'];
            GlobalAdmin::_($class::_Z($this->getThisPhaseName()));
        }
        if ($this->options['class_user']) {
            $class = $this->options['class_user'];
            GlobalUser::_($class::_Z($this->getThisPhaseName()));
        }
    }
    protected function onPrepare(): void
    {
        if ($this->options['cli_command_with_app']) {
            $this->options['cmd'] = array_merge([static::class => true], $this->options['cmd']);
        }
        if ($this->options['cli_command_with_common']) {
            $this->options['cmd'][Command::class] = true;
        }
    }
    
    protected function isLocalDatabase(): bool
    {
        $flag = $this->options['local_database'] ?? false;
        if ($flag) {
            return true;
        }
        $driver = DbManager::_()->options['database_driver'];
        if ($this->options['database_driver'] && ($driver !== $this->options['database_driver'])) {
            return true;
        }
        return false;
    }
    protected function isLocalRedis(): bool
    {
        return ($this->options['local_redis'] ?? false) ? true : false;
    }
    public function lang($str, $args = [])
    {
        $handler = $this->options['lang_handler'] ?? null;
        if ($handler) {
            return $handler($str, $args);
        }
        //Lang::_()->init($this->options,$this);
        return Lang::_()->lang($str, $args);
    }
}
