<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp\Core;

use DuckPhp\Core\Console;
use DuckPhp\Core\EventManager;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;

trait KernelTrait
{
    public $options = [];

    protected $kernel_options = [
        'path' => null,
        'override_class' => null,
        'override_class_from' => null,
        'cli_enable' => true,
        'is_debug' => false,
        'ext' => [],
        'app' => [],
        
        'skip_404' => false,
        'skip_exception_check' => false,
        
        'on_init' => null,
        'namespace' => null,
        
        'setting_file' => 'config/DuckPhpSettings.config.php',
        'setting_file_ignore_exists' => true,
        'setting_file_enable' => true,
        'use_env_file' => false,
        
        'exception_reporter' => null,
        'exception_for_project' => null,
        
        'cli_command_classes' => [],
        'cli_command_prefix' => null,
        'cli_command_method_prefix' => 'command_',
        //*/
        // 'namespace' => '',
        // 'namespace_controller' => 'Controller',
        
        // 'controller_path_ext' => '',
        // 'controller_welcome_class' => 'Main',
        // 'controller_welcome_class_visible' => false,
        // 'controller_welcome_method' => 'index',
        
        // 'controller_class_base' => '',
        // 'controller_class_postfix' => 'Controller',
        // 'controller_method_prefix' => 'action_',
        // 'controller_prefix_post' => 'do_', //TODO remove it
        
        // 'controller_class_map' => [],
        
        // 'controller_resource_prefix' => '',
        // 'controller_url_prefix' => '',
        
        // 'use_output_buffer' => false,
        // 'path_runtime' => 'runtime',
        
        // 'cli_command_alias' => [],
        // 'cli_default_command_class' => '',
        // 'cli_command_method_prefix' => 'command_',
        // 'cli_command_default' => 'help',
         //*/
    ];
    public $setting = [];
    public $overriding_class = null;
    protected $is_root = true;
    
    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    {
        $instance = static::_()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    public static function Current()
    {
        $phase = static::Phase();
        $class = $phase ? $phase : static::class;
        return $class::_();
    }
    public static function Root()
    {
        return (self::class)::_(); // remark ,don't use self::_()!
    }
    public static function Phase($new = null)
    {
        return static::_()->_Phase($new);
    }
    public static function Setting($key = null, $default = null)
    {
        return static::_()->_Setting($key, $default);
    }
    public static function IsRoot()
    {
        return static::Current()->_IsRoot();
    }
    protected function initOptions(array $options)
    {
        $this->options = array_replace_recursive($this->options, $options);
    }
    protected function getDefaultProjectNameSpace($class)
    {
        $a = explode('\\', $class ?? static::class);
        array_pop($a);
        array_pop($a);
        $namespace = implode('\\', $a);
        return $namespace;
    }
    protected function getDefaultProjectPath()
    {
        $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
        $path = realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/../');
        $path = (string)$path;
        $path = ($path !== '') ? rtrim($path, '/').'/' : '';
        
        return $path;
    }
    ////////
    public function _Phase($new = null)
    {
        $container = PhaseContainer::GetContainer();
        $old = $container->getCurrentContainer();
        if ($new) {
            $container->setCurrentContainer($new);
        }
        return $old;
    }
    public function _IsRoot()
    {
        return $this->is_root;
    }
    public function getOverridingClass()
    {
        return $this->overriding_class;
    }
    protected function initContainer($context)
    {
        $this->is_root = !(\is_a($context, self::class));
        //////////////////////////////
        
        if ($this->is_root) {
            $this->onBeforeCreatePhases();
            $flag = PhaseContainer::ReplaceSingletonImplement();
            $container = PhaseContainer::GetContainer();
            $container->setDefaultContainer($this->overriding_class);
            $container->setCurrentContainer($this->overriding_class);
            //TODO Move public containers to this;
            $this->onAfterCreatePhases();
        } else {
            $container = PhaseContainer::GetContainer();
            $container->setCurrentContainer($this->overriding_class);
        }
        /////////////
        $apps = [];
        $apps[static::class] = $this;
        $apps[$this->overriding_class] = $this;
        if ($this->is_root) {
            $apps[self::class] = $this;
        }
        if ($this->options['override_class_from'] ?? null) {
            $class = $this->options['override_class_from'];
            $apps[$class] = $this;
        }
        
        $container->addPublicClasses(array_keys($apps));
        $container->addPublicClasses(array_keys($this->options['app'] ?? []));
        
        /////////////
        foreach ($apps as $class => $object) {
            $class = $class ? (string)$class: static::class;
            $class::_($object);
        }
        return false;
    }
    protected function addPublicClassesInRoot($classes)
    {
        if (!$this->is_root) {
            return;
        }
        PhaseContainer::GetContainer()->addPublicClasses($classes);
        foreach ($classes as $class) {
            $class::_();
        }
    }
    protected function createLocalObject($class, $object = null)
    {
        return PhaseContainer::GetContainer()->createLocalObject($class, $object);
    }
    protected function initException($options)
    {
        //initException();
        $exception_options = $options;
        $exception_options ['default_exception_handler' ] = [self::class,'OnDefaultException']; // must be self,be root
        $exception_options ['dev_error_handler'] = [self::class,'OnDevErrorHandler'];        //be self, be root
        if (!$this->is_root) {
            $exception_option['handle_all_dev_error'] = false;
            $exception_option['handle_all_exception'] = false;
        }
        ExceptionManager::_()->init($exception_options, $this);
        if ($this->options['exception_reporter'] ?? null) {
            $exception_class = $this->options['exception_for_project'] ?? \Exception::class;
            ExceptionManager::_()->assignExceptionHandler($exception_class, [$this->options['exception_reporter'], 'OnException']);
        }
    }
    //init
    public function init(array $options, object $context = null)
    {
        $options['path'] = $options['path'] ?? ($this->options['path'] ?? $this->getDefaultProjectPath());
        $options['namespace'] = $options['namespace'] ?? ($this->options['namespace'] ?? ($this->getDefaultProjectNameSpace($this->overriding_class ?? null)));
        
        require_once __DIR__.'/Functions.php';
        $this->initOptions($options);
        
        if ($options['override_class'] ?? false) {
            $class = $options['override_class'];
            unset($options['override_class']);
            $options['override_class_from'] = $this->overriding_class;
            $this->overriding_class = $options['override_class_from'];
            
            return $class::_(new $class)->init($options);
        }
        
        $this->initContainer($context);
        $this->initException($options);
        $this->onPrepare();
        
        $this->prepareComponents();
        $this->initComponents($this->options, $context);
        $this->initExtentions($this->options['ext'] ?? [], true);
        $this->onInit();
        if ($this->options['on_init']) {
            ($this->options['on_init'])();
        }
        $this->onBeforeChildrenInit();
        $this->initExtentions($this->options['app'] ?? [], false);
        
        $this->onInited();
        $this->is_inited = true;
        return $this;
    }
    protected function prepareComponents()
    {
        //return; // for override
    }
    protected function initComponents(array $options, object $context = null)
    {
        $this->addPublicClassesInRoot([
            Console::class,
            EventManager::class,
        ]);
        if ($this->is_root) {
            $this->loadSetting();
            Console::_()->init($this->options, $this);
        }
        Route::_()->init($this->options, $this);
        Runtime::_()->init($this->options, $this);
        
        if (PHP_SAPI === 'cli') {
            $cli_namespace = $this->options['cli_command_prefix'] ?? $this->options['namespace'];
            $cli_namespace = $this->is_root ? '' : ($cli_namespace ? $cli_namespace : $this->overriding_class);
            $phase = $this->overriding_class;
            $classes = $this->options['cli_command_classes'] ?? [];
            $method_prefix = $this->options['cli_command_method_prefix'] ?? 'command_';
            Console::_()->regCommandClass($cli_namespace, $phase, $classes, $method_prefix);
        }
        $this->doInitComponents();
    }
    protected function doInitComponents()
    {
        //for override
    }
    protected function loadSetting()
    {
        $this->setting = $this->options['setting'] ?? [];
        if ($this->options['use_env_file']) {
            $this->dealWithEnvFile();
        }
        if ($this->options['setting_file_enable']) {
            $this->dealWithSettingFile();
        }
        return;
    }
    protected function dealWithEnvFile()
    {
        $env_setting = parse_ini_file(realpath($this->options['path']).'/.env');
        $env_setting = $env_setting?:[];
        $this->setting = array_merge($this->setting, $env_setting);
    }
    protected function dealWithSettingFile()
    {
        $path = $this->options['setting_file'];
        $is_abs = (DIRECTORY_SEPARATOR === '/') ? (substr($path, 0, 1) === '/') : preg_match('/^(([a-zA-Z]+:(\\|\/\/?))|\\\\|\/\/)/', $path);
        if ($is_abs) {
            $full_file = $this->options['setting_file'];
        } else {
            $full_file = realpath($this->options['path']).'/'.$this->options['setting_file'];
        }
        if (!is_file($full_file)) {
            if (!$this->options['setting_file_ignore_exists']) {
                throw new \ErrorException('DuckPhp: no Setting File');
            }
            return;
        }
        $setting = (function ($file) {
            return require $file;
        })($full_file);
        $this->setting = array_merge($this->setting, $setting);
    }
    public function _Setting($key = null, $default = null)
    {
        return $key ? (static::Root()->setting[$key] ?? $default) : static::Root()->setting;
    }
    protected function initExtentions(array $exts, $use_main_options): void
    {
        foreach ($exts as $class => $options) {
            //try {
            if ($options === false) {
                continue;
            }
            if ($options === true) {
                $options = ($use_main_options) ? $this->options : [];
            }
            $class = (string)$class;
            if (!class_exists($class)) {
                continue;
            }
            $class::_()->init($options, $this);
            if (!$use_main_options) {
                $this->_Phase($this->overriding_class);
            }
            //} catch (\Throwable $ex) {
            //    $phase = $this->_Phase($class);
            //    throw $ex;
            //}
        }

        return;
    }
    public function run(): bool
    {
        $ret = false;
        $is_exceptioned = false;
        $this->_Phase($this->overriding_class);
        if ($this->is_root) {
            (self::class)::_($this); // remark ,don't use self::_()!
        }
        
        $this->onBeforeRun();
        try {
            Runtime::_()->run();
            if (PHP_SAPI === 'cli' && $this->is_root && $this->options['cli_enable']) {
                $ret = Console::_()->run();
            } else {
                $ret = Route::_()->run();
                if (!$ret) {
                    $ret = $this->runExtentions();
                    $this->_Phase($this->overriding_class);
                    if (!$ret) {
                        EventManager::FireEvent([$this->overriding_class, 'On404']);
                    }
                    if (!$ret && $this->is_root && !($this->options['skip_404'] ?? false)) {
                        $this->_On404();
                    }
                }
            }
        } catch (\Throwable $ex) {
            $this->runException($ex);
            $ret = true;
            $is_exceptioned = true;
        }
        if (!$is_exceptioned) {
            Runtime::_()->clear();
        }
        $this->onAfterRun();
        return $ret;
    }
    protected function runException($ex)
    {
        $phase = $this->_Phase();
        Runtime::_()->onException($this->options['skip_exception_check']);
        if ($this->options['skip_exception_check']) {
            throw $ex;
        }
        ExceptionManager::CallException($ex);
        if ($phase !== $this->overriding_class) {
            Runtime::_()->clear();
            $this->_Phase($this->overriding_class);
        }
        Runtime::_()->last_phase = $phase;
        Runtime::_()->clear();
    }
    protected function runExtentions()
    {
        $flag = false;
        foreach ($this->options['app'] as $class => $options) {
            $flag = $class::_()->run();
            if ($flag) {
                break;
            }
        }
        return $flag;
    }
    //main produce end
    ////////////////////////
    //for override
    public static function On404(): void
    {
        static::_()->_On404();
    }
    public static function OnDefaultException($ex): void
    {
        static::_()->_OnDefaultException($ex);
    }
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        static::_()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
    }
    public function _On404(): void
    {
        echo "no found";
    }
    public function _OnDefaultException($ex): void
    {
        echo "_OnDefaultException";
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        echo "_OnDevErrorHandler";
    }
    protected function onBeforeCreatePhases()
    {
        //EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onAfterCreatePhases()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onPrepare()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onBeforeChildrenInit()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onInit()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onInited()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onBeforeRun()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
    protected function onAfterRun()
    {
        EventManager::FireEvent([$this->overriding_class, __FUNCTION__]);
    }
}
