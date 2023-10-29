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
use DuckPhp\Component\Configer;
use DuckPhp\Component\DbManager;
use DuckPhp\Component\DuckPhpCommand;
use DuckPhp\Component\EventManager;
use DuckPhp\Component\Pager;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\RedisManager;
use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Component\UserObject;
use DuckPhp\Core\App;
use DuckPhp\Core\Console;

class DuckPhp extends App
{
    protected $common_options = [
        'ext_options_file_enable' => false,
        'ext_options_file' => 'config/DuckPhpApps.config.php',
        
        'cli_enable' => true,
        
        'database_auto_extend_method' => null,
        'path_info_compact_enable' => null,
        
        'class_user' => null,
        'class_admin' => null,
        
        'session_prefix' => null,
        'table_prefix' => null,
    ];
    public static function InitAsContainer($options)
    {
        $options['container_only'] = true;
        $options['handle_all_exception'] = false;
        $options['handle_all_dev_error'] = false;
        $options['skip_404_handler'] = true;
        
        return DuckPhp::G(new DuckPhp())->init($options);
    }
    public function thenRunAsContainer($skip_404 = false, $welcome_handle = null)
    {
        $ret = $this->run();
        if ($ret) {
            return $ret;
        }
        if ($welcome_handle) {
            $path_info = static::PathInfo();
            if ($path_info === '' || $path_info === '/') {
                ($welcome_handle)();
                return true;
            }
        }
        if (!$skip_404) {
            $this->_On404();
        }
        return false;
    }
    protected function initComponents(array $options, object $context = null)
    {
        parent::initComponents($options, $context);
        if ($this->options['ext_options_file_enable']) {
            $this->loadExtOptions();
        }
        
        Configer::G()->init($this->options, $this);
        DbManager::G()->init($this->options, $this);
        RouteHookRouteMap::G()->init($this->options, $this);
        
        if (PHP_SAPI === 'cli') {
            if ($this->is_root) {
                DuckPhpCommand::G()->init($this->options, $this);
                Console::G()->options['cli_default_command_class'] = DuckPhpCommand::class;
            } else {
                Console::G()->regCommandClass(static::class, $this->options['namespace']);
            }
        }
        if ($this->options['path_info_compact_enable'] ?? false) {
            RouteHookPathInfoCompat::G()->init($this->options, $this);
        }
        $phase = $this->_Phase();
        if ($this->is_root && $phase) {
            $this->getContainer()->addPublicClasses([
                Console::class, // TODO
                DbManager::class,
                RedisManager::class,
                EventManager::class,
                ]);
        }

        ////////////////
        if ($this->options['class_user'] ?? null) {
            if (!$this->is_root) {
                $this->bumpSingletonToRoot($this->options['class_user'], UserObject::class);
            }
            static::User(($this->options['class_user'])::G());
        }
        if ($this->options['class_admin'] ?? null) {
            if (!$this->is_root) {
                $this->bumpSingletonToRoot($this->options['class_admin'], AdminObject::class);
            }
            static::Admin(($this->options['class_admin'])::G());
        }
        if ($this->options['exception_reporter'] ?? null) {
            static::assignExceptionHandler(\Exception::class, [$this->options['exception_reporter'], 'OnException']);
        }
        ///////
        return $this;
    }
    //////////////////////
    
    public function isInstalled()
    {
        return $this->options['install'] ?? false;
    }
    protected function bumpSingletonToRoot($oldClass, $newClass)
    {
        $self = static::class;
        $this->_PhaseCall(get_class(App::_()), function () use ($self, $oldClass, $newClass) {
            $newClass::G(new PhaseProxy($self, $oldClass));
        });
    }
    //////////////
    ////[[[[
    protected function installWithExtOptions($options)
    {
        $options['install'] = DATE(DATE_ATOM);
        $this->options = array_replace_recursive($this->options, $options);
        $this->saveExtOptions(static::class, $options);
    }
    protected function get_all_ext_options()
    {
        $full_file = $this->options['ext_options_file'];
        $full_file = static::IsAbsPath($full_file) ? $full_file : realpath($this->options['path']).'/'.$full_file;
        if (!is_file($full_file)) {
            return [];
        }
        $all_options = (function ($file) {
            return require $file;
        })($full_file);
        return $all_options;
    }
    protected function loadExtOptions()
    {
        $all_options = $this->get_all_ext_options();
        $options = $all_options[static::class] ?? [];
        $this->options = array_replace_recursive($this->options, $options);
    }
    protected function saveExtOptions($class, $options)
    {
        $full_file = $this->options['ext_options_file'];
        $full_file = static::IsAbsPath($full_file) ? $full_file : realpath($this->options['path']).'/'.$full_file;
        
        $all_options = $this->get_all_ext_options();
        $all_options[$class] = $options;
        
        $string = "<"."?php //". "regenerate by " . __CLASS__ . '->'.__METHOD__ ." at ". DATE(DATE_ATOM) . "\n";
        $string .= "return ".var_export($all_options, true) .';';
        file_put_contents($full_file, $string);
        clearstatcache();
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
    /////////////////////////
    public static function Config($file_basename, $key = null, $default = null)
    {
        return Configer::G()->_Config($file_basename, $key, $default);
    }
    
    //@override
    public function _Db($tag)
    {
        return DbManager::G()->_Db($tag);
    }
    //@override
    public function _DbCloseAll()
    {
        return DbManager::G()->_DbCloseAll();
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
    public static function Redis($tag = 0)
    {
        return RedisManager::Redis($tag);
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
    public static function assignRewrite($key, $value = null)
    {
        return RouteHookRewrite::G()->assignRewrite($key, $value);
    }
    public static function getRewrites()
    {
        return RouteHookRewrite::G()->getRewrites();
    }
}
