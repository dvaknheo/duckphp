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
use DuckPhp\Component\Configer;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\Lang;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookResource;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Core\App;
use DuckPhp\Core\Route;
use DuckPhp\GlobalAdmin\AdminControllerInterface;
use DuckPhp\GlobalAdmin\GlobalAdmin;
use DuckPhp\GlobalUser\GlobalUser;
use DuckPhp\GlobalUser\UserControllerInterface;

class DuckPhp extends App
{
    protected $common_options = [
        'data_file_enable' => false,
        'ext' => [
            Lang::class => true,
            RouteHookRewrite::class => true,
            RouteHookRouteMap::class => true,
            RouteHookResource::class => true,
            RouteHookPathInfoCompat::class => 'path_info_compact_enable',
        ],

        'admin_provider' => '',
        'user_provider' => '',
        'database_driver' => '',
        'cli_command_with_common' => true,

        'lang_default' => null,
        'lang_final' => null,
        'local_database' => false,
        'local_redis' => false,

        // 'use_user_view' => true,
        // 'use_admin_view' => true,


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
    /**
     * Hidden options: read by the framework, but intentionally NOT declared in $options.
     *
     * Listed here only so tooling/docs can show them (see scripts/scan-options.py).
     * The value written here is the effective fallback used at the read site.
     */
    protected $hidden_options = [
        'session_prefix' => '',
        'table_prefix' => '',

        'use_user_view' => false,
        'use_admin_view' => false,
        'use_user_view_header_footer' => false,
        'use_admin_view_header_footer' => false,
        'exception_for_business'    => \Exception::class,
        'exception_for_controller'  => \Exception::class,
        'duckphp_all_in_one_wrap_header_foot'   => false, // DuckPhpAllInOne::embedMe() sets it to true

        'permission_menu_tree_for_admin'    => null,

        // @used-by dvaknheo/duckcoverage : used by that composer package for coverage testing (no reader inside this repo)
        'duckcoverage_test_lister' => null,
    ];
    protected function initComponentsOfRoot($components, $default): void
    {
        $my_components = [
            DbManager::class => self::EXT_DEFAULT,
            RedisManager::class => self::EXT_DEFAULT,
            GlobalAdmin::class => self::EXT_DISABLE,
            GlobalUser::class => self::EXT_DISABLE,
            GlobalEvent::class => self::EXT_DISABLE,
        ];
        $components = array_merge($components, $my_components);

        parent::initComponentsOfRoot($components, $default);
        if ($this->options['data_file_enable'] ?? false) {
            ExtOptionsLoader::_()->init($this->options, $this);
        }
        DbManager::_()->init($this->options, $this);
        RedisManager::_()->init($this->options, $this);
        $this->options['database_driver'] = DbManager::_()->options['database_driver'];
    }
    ////////////////////
    protected function initComponentsOfInner($components, $default): void
    {
        if (!$this->is_root && ($this->options['data_file_enable'] ?? false)) {
            ExtOptionsLoader::_()->init($this->options, $this);
        }
        if ($this->options['cli_command_with_common']) {
            $this->options['cmd'] = array_merge([Command::class => true], $this->options['cmd']);
        }
        $components[Configer::class] = true;
        parent::initComponentsOfInner($components, $default);

        if ($this->isLocalDatabase()) {
            $this->options['database_list_reload_by_setting'] = false;
            $this->createLocalObject(DbManager::class);
            DbManager::_()->init($this->options, $this);
            $this->options['database_driver'] = DbManager::_()->options['database_driver'];
        }
        if ($this->isLocalRedis()) {
            $this->createLocalObject(RedisManager::class);
            RedisManager::_()->init($this->options, $this);
        }
    }
    protected function initComponentsOfExt($classes, $default): void
    {
        parent::initComponentsByClasseOptions($classes, $default);
        if ($this->options['admin_provider']) {
            $class = $this->options['admin_provider'];
            $object = $class::_();
            GlobalAdmin::_(PhaseProxy::CreatePhaseProxy($this->getThisPhaseName(), $object));
        }
        if ($this->options['user_provider']) {
            $class = $this->options['user_provider'];
            $object = $class::_();
            GlobalUser::_(PhaseProxy::CreatePhaseProxy($this->getThisPhaseName(), $object));
        }
    }

    protected function haltInitInBaseClass(): void
    {
        // Just Keep Blank
    }
    /**
     * override
     * @param array<string, mixed> $data
     * @return void
     */

    public function _Show(array $data, string $view = '')
    {
        if (($this->options['use_user_view'] ?? false) && \is_a(Route::_()->getRouteCallingClass(), UserControllerInterface::class, true)) {
            GlobalUser::_()->_Show($data, $view);
            return;
        }
        if (($this->options['use_admin_view'] ?? false) && \is_a(Route::_()->getRouteCallingClass(), AdminControllerInterface::class, true)) {
            GlobalAdmin::_()->_Show($data, $view);
            return;
        }
        parent::_Show($data, $view);
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
    public function lang($str, $args = [], $fallback = null)
    {
        $handler = $this->options['lang_handler'] ?? null;
        if ($handler) {
            return $handler($str, $args);
        }
        //Lang::_()->init($this->options,$this);
        return Lang::_()->language($str, $args, $fallback);
    }
}
