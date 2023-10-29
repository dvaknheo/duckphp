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
use DuckPhp\Component\ExtOptionsLoader;
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
        
        'path_info_compact_enable' => null,
        
        'class_user' => null,
        'class_admin' => null,
        
        'session_prefix' => null,
        'table_prefix' => null,
        
        'exception_reporter' => null,
        //'install'
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
        if ($this->is_root) {
            $this->getContainer()->addPublicClasses([
                DbManager::class,
                RedisManager::class,
                EventManager::class,
                ]);
        }
        
        if ($this->options['ext_options_file_enable']) {
            ExtOptionsLoader::_()->loadExtOptions(static::class);
        }
        
        Configer::G()->init($this->options, $this);
        DbManager::G()->init($this->options, $this);
        
        RouteHookRouteMap::G()->init($this->options, $this);
        RouteHookRewrite::G()->init($this->options, $this);
        
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
    public function install($options)
    {
        if ($this->options['ext_options_file_enable']) {
            return ExtOptionsLoader::_()->installWithExtOptions(static::class, $options);
        }
    }
    protected function bumpSingletonToRoot($oldClass, $newClass)
    {
        $self = static::class;
        $this->_PhaseCall(get_class(App::_()), function () use ($self, $oldClass, $newClass) {
            $newClass::_(new PhaseProxy($self, $oldClass));
        });
    }
    //////////////
    public static function Admin($admin = null)
    {
        return AdminObject::_($admin);
    }
    public static function AdminId()
    {
        return static::Admin()->id();
    }
    public static function User($user = null)
    {
        return UserObject::_($user);
    }
    public static function UserId()
    {
        return static::User()->id();
    }
    //@override
    public function _Pager($object = null)
    {
        return Pager::G($object);
    }
    /////////////////////////
    //@override
    public function _Event()
    {
        return EventManager::G();
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
