<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Component\Cache;
use DuckPhp\Component\Configer;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\GlobalAdmin;
use DuckPhp\Component\GlobalUser;
use DuckPhp\Component\Pager;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Component\SqlDumper;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;

class DuckPhp extends App
{
    protected $common_options = [
        'ext_options_file_enable' => false,
        'ext_options_file' => 'config/DuckPhpApps.config.php',
        'exception_reporter' => null,
        
        'path_info_compact_enable' => false,
        
        'session_prefix' => null,
        'table_prefix' => null,
        
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
    ];
    public static function InitAsContainer($options, $welcome_handle = null)
    {
        $options['handle_all_exception'] = false;
        $options['handle_all_dev_error'] = false;
        $options['skip_404'] = $welcome_handle ? true : false;
        
        $self = DuckPhp::_(new DuckPhp())->init($options);
        Route::_()->addRouteHook(function () {
            Route::_()->forceFail();
            return true;
        }, 'prepend-outter', false);
        EventManager::OnEvent([DuckPhp::class,'On404'], function () use ($welcome_handle) {
            if (!$welcome_handle) {
                return;
            }
            DuckPhp::_()->options['skip_404'] = true;
            if ($welcome_handle) {
                $path_info = Route::PathInfo();
                if ($path_info === '/' || $path_info === '') {
                    ($welcome_handle)();
                }
            }
        });
        return $self;
    }
    protected function initComponents(array $options, object $context = null)
    {
        parent::initComponents($options, $context);
        if ($this->is_root) {
            $this->getContainer()->addPublicClasses([
                DbManager::class,
                RedisManager::class,
                GlobalAdmin::class,
                GlobalUser::class,
            ]);
        }
        
        //must be first
        if ($this->options['ext_options_file_enable']) {
            ExtOptionsLoader::_()->loadExtOptions(static::class);
        }
        
        Configer::_()->init($this->options, $this);
        DbManager::_()->init($this->options, $this);
        RedisManager::_()->init($this->options, $this);
        RouteHookRouteMap::_()->init($this->options, $this);
        RouteHookRewrite::_()->init($this->options, $this);
        
        if (PHP_SAPI === 'cli') {
            if ($this->is_root) {
                DuckPhpCommand::_()->init($this->options, $this);
                Console::_()->options['cli_default_command_class'] = DuckPhpCommand::class;
            } else {
                Console::_()->regCommandClass(static::class, $this->options['namespace']);
            }
        }
        if ($this->options['path_info_compact_enable'] ?? false) {
            RouteHookPathInfoCompat::_()->init($this->options, $this);
        }
        if ($this->options['sql_dump_enable'] ?? false) {
            SqlDumper::_()->init($this->options, $this);
        }
        ////////////////////////////////////////
        if ($this->options['exception_reporter'] ?? null) {
            ExceptionManager::_()->assignExceptionHandler(\Exception::class, [$this->options['exception_reporter'], 'OnException']);
        }
        ///////
        return $this;
    }
    ////////////////////////////////////////////
    public function install($options, $parent_options = [])
    {
        /*
        foreach ($exts as $class => $options) {
            if (\is_subclass_of($class, self::class)) {
                if ($class::_()->isInstalled()) {
                    $class::_()->install([], $options);
                }
            }
        }
        //*/
        // force install ?
        if ($this->options['ext_options_file_enable']) {
            return ExtOptionsLoader::_()->installWithExtOptions(static::class, $options);
        }
        // then install me;
    }
    ////////////////////////////////////////////
    public function _Event()
    {
        return EventManager::_();
    }
    public function _Pager($object = null)
    {
        return Pager::_($object);
    }
}
