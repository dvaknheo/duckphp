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
        if (PHP_SAPI === 'cli') {
            Console::_()->regCommandClass2($this->is_root? '':$this->options['namespace'], static::class, $this->options['cli_command_class']?? static::class, $this->options['cli_command_method_prefix']??'command_', 'help');
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
        $is_debug = !$off;
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
        //echo "Override this to use to show your project routes .\n";
        echo $this->getCommandListInfo();
    }
    public function getCommandListInfo()
    {
        $str = '';
        $group = Console::_()->options['cli_command_group'];
        
        foreach ($group as $namespace => $v) {
            if ($namespace === '') {
                $str .= "System default commands:\n";
            } else {
                $str .= "\e[32;7m{$namespace}\033[0m is in phase '{$v['phase']}' power by '{$v['class']}' :\n";
            }
            /////////////////
            $descs = $this->getCommandsByClass($v['class'],$v['method_prefix']);
            foreach ($descs as $method => $desc) {
                $cmd = !$namespace ? $method : $namespace.':'.$method;
                $cmd = "\e[32;1m".str_pad($cmd, 20)."\033[0m";
                $str .= "  $cmd\t$desc\n";
            }
        }
        return $str;
    }
    protected function getCommandsByClass($class, $method_prefix)
    {
        $class = new \ReflectionClass($class);
        $methods = $class->getMethods();
        $ret = [];
        foreach ($methods as $v) {
            $name = $v->getName();
            if (substr($name, 0, strlen($method_prefix)) !== $method_prefix) {
                continue;
            }
            $command = substr($name, strlen($method_prefix));
            $doc = $v->getDocComment();
            
            // first line;
            $desc = ltrim(''.substr(''.$doc, 3));
            $pos = strpos($desc, "\n");
            $pos = ($pos !== false)?$pos:255;
            $desc = trim(substr($desc, 0, $pos), "* \t\n");
            $ret[$command] = $desc;
        }
        return $ret;
    }
}
