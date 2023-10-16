<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Component\AdminObject;
use DuckPhp\Component\Cache;
use DuckPhp\Component\Console;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\Component\EventManager;
use DuckPhp\Component\Pager;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Component\UserObject;
use DuckPhp\Core\App;
use DuckPhp\Core\Logger;

class DuckPhp extends App
{
    protected $duckphp_default_options = [
        'ext_options_from_config' => null,
        'database_auto_extend_method' => null,
        'path_info_compact_enable' => null,
        'class_user' => null,
        'class_admin' => null,
        'table_prefix' => null,
        'session_prefix' => null,
    ];
    public function __construct()
    {
        /*
        $this->core_options['ext'] = [
            DbManager::class => true,
            RouteHookRouteMap::class => true,
        ];
        $this->core_options['route_map_auto_extend_method'] = false;
        $this->core_options['database_auto_extend_method'] = false;
        //*/

        parent::__construct();
    }
    public static function RunAsContainerQuickly($options, $skip_404 = false, $welcome_handle = null)
    {
        $options['container_mode'] = true;
        $options['handel_all_exception'] = false;
        $options['skip_404_handler'] = $skip_404;
        
        if ($welcome_handle) {
            $options['skip_404_handler'] = true;
        }
        $ret = DuckPhp::G(new DuckPhp())->init($options)->run(); // remark , not static::class
        if (!$ret && $welcome_handle) {
            $path_info = DuckPhp::PathInfo();
            if ($path_info === '' || $path_info === '/') {
                ($welcome_handle)();
                return true;
            } else {
                DuckPhp::G()->_On404();
            }
        }
        return $ret;
    }
    protected function initComponents(array $options, object $context = null)
    {
        parent::initComponents($options, $context);
        if ($this->options['ext_options_from_config'] ?? false) {
            $this->mergeExtOptions();
        }
        
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
                Logger::class,
                Console::class,
                DbManager::class,
                RedisManager::class,
                EventManager::class,
                ]);
        }

        ////////////////
        if ($this->options['class_user'] ?? null) {
            if ($this->is_child) {
                $this->bumpSingletonToRoot($this->options['class_user'], UserObject::class);
            }
            static::User(($this->options['class_user'])::G());
        }
        if ($this->options['class_admin'] ?? null) {
            if ($this->is_child) {
                $this->bumpSingletonToRoot($this->options['class_admin'], AdminObject::class);
            }
            static::Admin(($this->options['class_admin'])::G());
        }
        ///////
        return $this;
    }
    public function getPath($sub_path = '')
    {
        if (!$sub_path) {
            return $this->options['path'];
        }
        $key = "path_".$sub_path;
        if (isset($this->options[$key])) {
            return $this->options[$key]; //TODO 这里要调整 我们要获得绝对路径
        } elseif (in_array($sub_path, ['config','view','log'])) {
            return $this->options['path']. $sub_path .'/';
        }
        return $this->options['path'].$sub_path .'/';
    }
    //////////////////////
    
    public function isInstalled()
    {
        return $this->options['install'] ?? false;
    }
    protected function bumpSingletonToRoot($oldClass, $newClass)
    {
        $self = static::class;
        $this->_PhaseCall(get_class(App::G()), function () use ($self, $oldClass, $newClass) {
            $newClass::G(new PhaseProxy($self, $oldClass));
        });
    }
    //////////////
    ////[[[[
    protected $file_for_ext_options_from_config = 'DuckPhpOptions';
    protected function installWithExtOptions($options)
    {
        $options['install'] = DATE(DATE_ATOM);
        $this->options = array_replace_recursive($this->options, $options);
        $this->saveExtOptions(static::class, $options);
    }
    
    protected function get_file_for_ext_config()
    {
        $path = $this->_PhaseCall(get_class(App::G()), function () {
            return App::G()->getPath('config');
        });
        $full_file = $path.$this->file_for_ext_options_from_config .'.php';
        return $full_file;
    }
    protected function get_all_ext_config($full_file = null)
    {
        //todo use GetFileFromSubComponent($options, $subkey, $file, false);
        $full_file = $full_file ?? $this->get_file_for_ext_config();
        clearstatcache();
        if (!is_file($full_file)) {
            return [];
        }
        $all_options = include($full_file); //TODO seprate
        return $all_options;
    }
    protected function mergeExtOptions()
    {
        $all_options = $this->get_all_ext_config();
        $options = $all_options[static::class] ?? [];
        $this->options = array_replace_recursive($this->options, $options);
    }
    protected function saveExtOptions($class, $options)
    {
        $full_file = $this->get_file_for_ext_config();
        $all_options = $this->get_all_ext_config($full_file);
        
        $all_options[$class] = $options;
        
        $string = "<"."?php //". "regenerate by " . __CLASS__ . '->'.__METHOD__ ." at ". DATE(DATE_ATOM) . "\n";
        $string .= "return ".var_export($all_options, true) .';';
        file_put_contents($full_file, $string);
    }
    ////]]]] // extOptionsMode
    
    public static function Admin($admin = null)
    {
        return AdminObject::G($admin);
    }
    public static function AdminId()
    {
        return static::Admin()->id();
    }
    public static function User($user = null)
    {
        return UserObject::G($user);
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
