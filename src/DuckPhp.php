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
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\Admin;
use DuckPhp\GlobalAdmin\AdminControllerInterface;
use DuckPhp\GlobalUser\User;
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
        'database_driver' => '',
        'cli_command_with_common' => true,

        'lang_default' => null,
        'lang_final' => null,
        'local_database' => false,
        'local_redis' => false,

        'exception_reporter' => null,
        'exception_for_project' => null,

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
        'not_empty' => true,
        'url_admin_home' => null,
        'url_user_home' => null,
        'session_prefix' => '',
        'table_prefix' => '',

        'exception_for_business' => \Exception::class,
        'exception_for_controller' => \Exception::class,
        // DuckPhpAllInOne::embedMe() sets it to true
        'duckphp_all_in_one_wrap_header_foot' => false,

        'permission_menu_tree_for_admin' => null,

        // @used-by dvaknheo/duckcoverage : used by that composer package for coverage testing (no reader inside this repo)
        'duckcoverage_test_lister' => null,
    ];
    protected function initComponentsOfRoot($components, $default): void
    {
        $my_components = [
            DbManager::class => self::EXT_ROOT_HOLD_POSISION_ONLY,
            RedisManager::class => self::EXT_ROOT_HOLD_POSISION_ONLY,
            Admin::class => self::EXT_ROOT_HOLD_POSISION_ONLY,
            User::class => self::EXT_ROOT_HOLD_POSISION_ONLY,
            GlobalEvent::class => self::EXT_ROOT_HOLD_POSISION_ONLY,
        ];
        $components = array_merge($components, $my_components);

        parent::initComponentsOfRoot($components, $default);
        if ($this->options['data_file_enable'] ?? false) {
            ExtOptionsLoader::_()->init($this->options, $this);
        }
        if (isset($this->setting['redis']) || isset($this->setting['redis_list']) ||
            isset($this->options['redis']) || isset($this->options['redis_list'])) {
            RedisManager::_()->init($this->options, $this);
        }
        if (isset($this->setting['database']) || isset($this->setting['database_list']) ||
            isset($this->options['database']) || isset($this->options['database_list'])) {
            DbManager::_()->init($this->options, $this);
            $this->options['database_driver'] = DbManager::_()->options['database_driver'];
        }
    }
    ////////////////////
    protected function initComponentsOfInner($components, $default): void
    {
        if ($this->options['exception_reporter'] ?? null) {
            $exception_class = $this->options['exception_for_project'] ?? \Exception::class;
            if (!is_callable($this->options['exception_reporter'])) {
                throw new DuckPhpSystemException("'exception_reporter' config error!:" . var_export($this->options['exception_reporter'], true));
            }
            ExceptionManager::_()->assignExceptionHandler($exception_class, $this->options['exception_reporter']);
        }
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
        $enable = $data['__use_logined_view_data'] ?? (View::_()->data['__use_logined_view_data'] ?? null);
        if (!($enable ?? false)) {
            return parent::_Show($data, $view);
        }
        if (\is_a(Route::_()->getRouteCallingClass(), UserControllerInterface::class, true)) {
            $data['__logined_render_header_footer'] = $data['__logined_render_header_footer'] ?? (View::_()->data['__logined_render_header_footer'] ?? null);
            $data = User::_()->mergeViewData($data);
        }
        if (\is_a(Route::_()->getRouteCallingClass(), AdminControllerInterface::class, true)) {
            $data['__logined_render_header_footer'] = $data['__logined_render_header_footer'] ?? (View::_()->data['__logined_render_header_footer'] ?? null);
            $data = Admin::_()->mergeViewData($data);
        }
        $enable_header_footer = $data['__use_logined_header_footer_file'] ?? (View::_()->data['__use_logined_header_footer_file'] ?? null);
        if ($enable_header_footer ?? false) {
            View::_()->setViewHeadFoot($data['__logined_header_file'], $data['__logined_footer_file']);
        }

        return parent::_Show($data, $view);
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
