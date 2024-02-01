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
use DuckPhp\Component\RouteHookResource;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Component\SqlDumper;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;

class DuckPhp extends App
{
    protected $common_options = [
        'ext_options_file_enable' => true,
        'ext_options_file' => 'config/DuckPhpApps.config.php',
        
        'session_prefix' => null,
        'table_prefix' => null,
        
        'path_info_compact_enable' => false,
        'sql_dump_enable' => false,
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
    ];

    protected function initComponents(array $options, object $context = null)
    {
        //must be first
        if ($this->options['ext_options_file_enable']) {
            ExtOptionsLoader::_()->loadExtOptions(static::class);
        }
        parent::initComponents($options, $context);
        if ($this->is_root) {
            $this->getContainer()->addPublicClasses([
                DbManager::class,
                RedisManager::class,
                GlobalAdmin::class,
                GlobalUser::class,
            ]);
            DbManager::_()->init($this->options, $this);
            RedisManager::_()->init($this->options, $this);
        }
        Configer::_()->init($this->options, $this);
        RouteHookRouteMap::_()->init($this->options, $this);
        RouteHookRewrite::_()->init($this->options, $this);
        RouteHookResource::_()->init($this->options, $this);
        
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
        if ($this->options['class_admin']) {
            GlobalAdmin::_(PhaseProxy::CreatePhaseProxy(static::class, $this->options['class_admin']));
        }
        if ($this->options['class_user']) {
            GlobalUser::_(PhaseProxy::CreatePhaseProxy(static::class, $this->options['class_user']));
        }
        
        return $this;
    }
    /**
     * switch debug mode
     */
    public function command_debug($off = false)
    {
        $is_debug = !$is_off;
        $ext_options = ExtOptionsLoader::_()->loadExtOptions(true, $this);
        $ext_options['is_debug'] = $is_debug;
        ExtOptionsLoader::_()->saveExtOptions($ext_options, $this);
        $this->options['is_debug'] = $is_debug;
        if ($is_debug) {
            echo "Debug mode has turn on. us --off to off\n";
        } else {
            echo "Debug mode has turn off.\n";
        }
    }
    /**
     * show version
     */
    public function command_version()
    {
        echo $this->version();
        echo "\n";
    }

    /**
     * call a function. e.g. namespace/class@method arg1 --parameter arg2
     */
    public function command_call()
    {
        //call to service
        // full namespace , service AAService;
        $args = func_get_args();
        $cmd = array_shift($args);
        list($class, $method) = explode('@', $cmd);
        $class = str_replace('/', '\\', $class);
        echo "calling $class::_()->$method\n";
        $ret = Console::_()->callObject($class, $method, $args, Console::_()->getCliParameters());
        echo "--result--\n";
        echo json_encode($ret);
    }
    /**
     * show all routes
     */
    public function command_routes()
    {
        echo "Override this to use to show your project routes .\n";
    }
}
