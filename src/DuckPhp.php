<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Component\Command;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\GlobalAdmin;
use DuckPhp\Component\GlobalUser;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookResource;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;

class DuckPhp extends App
{
    protected $common_options = [
        'ext_options_file_enable' => true,
        'ext_options_file' => 'config/DuckPhpApps.config.php',
        'ext' => [
            RouteHookRouteMap::class => true,
            RouteHookRewrite::class => true,
            RouteHookResource::class => true,
            //RouteHookPathInfoCompat::class => false,
        ],
        
        'session_prefix' => null,
        'table_prefix' => null,
        
        'path_info_compact_enable' => false,
        'class_admin' => '',
        'class_user' => '',
        //'install_need_database' => true,
        //'install_need_redis' => false,
        
        //*
        // 'path_config' => 'config',
        // 'database' => null,
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
        'cli_command_class' => Command::class,
    ];

    protected function initComponents(array $options, object $context = null)
    {
        //must be first
        if ($this->options['ext_options_file_enable']) {
            ExtOptionsLoader::_()->loadExtOptions(static::class);
        }
        parent::initComponents($options, $context);
        $this->addPublicClassesInRoot([
            DbManager::class,
            RedisManager::class,
            GlobalAdmin::class,
            GlobalUser::class,
        ]);
        if ($this->is_root) {
            DbManager::_()->init($this->options, $this);
            RedisManager::_()->init($this->options, $this);
        } else {
            if ($this->options['local_db'] ?? false) {
                $this->createLocalObject(DbManager::class);
                DbManager::_()->init($this->options, $this);
            }
            if ($this->options['local_redis'] ?? false) {
                $this->createLocalObject(RedisManager::class);
                RedisManager::_()->init($this->options, $this);
            }
        }

        if ($this->options['path_info_compact_enable'] ?? false) {
            RouteHookPathInfoCompat::_()->init($this->options, $this);
        }
        if ($this->options['class_admin']) {
            GlobalAdmin::_(PhaseProxy::CreatePhaseProxy(static::class, $this->options['class_admin']));
        }
        if ($this->options['class_user']) {
            GlobalUser::_(PhaseProxy::CreatePhaseProxy(static::class, $this->options['class_user']));
        }
        
        return $this;
    }
}
