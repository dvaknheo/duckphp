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
use DuckPhp\Component\RouteHookCheckStatus;
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
            //ExtOptionsLoader::class => false,
            Lang::class => true,
            //RouteHookCheckStatus::class => true,
            RouteHookRewrite::class => true,
            RouteHookRouteMap::class => true,
            RouteHookResource::class => true,
            
            //RouteHookPathInfoCompat::class => false,
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


        'component_shared' => [
            DbManager::class,
            RedisManager::class,
            GlobalAdmin::class,
            GlobalUser::class,
            GlobalEvent::class,
        ],
        'compnoent_dynmic' => [
        ],
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
    protected function initComponents(): void
    {
        if ($this->options['cli_command_with_app']) {
            $this->options['cmd'] = array_merge([static::class => true], $this->options['cmd']);
        }
        if ($this->options['cli_command_with_common']) {
            $this->options['cmd'][Command::class] = true;
        }
        
        // Main
        parent::initComponents();
        
        $this->addPublicClassesInRoot([
            DbManager::class => true,
            RedisManager::class => true,
            GlobalAdmin::class => true,
            GlobalUser::class => true,
            GlobalEvent::class => true,
        ]);
        if ($this->options['data_file_enable']) {
            ExtOptionsLoader::_()->init($this->options, $this);
        }
        if ($this->is_root) {
            DbManager::_()->init($this->options, $this);
            RedisManager::_()->init($this->options, $this);
            $this->options['database_driver'] = DbManager::_()->getDatabaseDriver();
        } else {
            if ($this->isLocalDatabase()) {
                $this->createLocalObject(DbManager::class);
                DbManager::_()->init($this->options, $this);
            }
            if ($this->isLocalRedis()) {
                $this->createLocalObject(RedisManager::class);
                RedisManager::_()->init($this->options, $this);
            }
        }
        if ($this->options['path_info_compact_enable'] ?? false) {
            RouteHookPathInfoCompat::_()->init($this->options, $this);
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
        //just for skip self::_()->Init;
    }
    protected function isLocalDatabase(): bool
    {
        $flag = $this->options['local_database'] ?? false;
        if ($flag) {
            return true;
        }
        $driver = DbManager::_()->getDatabaseDriver();
        
        if ($this->options['database_driver'] && ($driver != $this->options['database_driver'])) {
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
