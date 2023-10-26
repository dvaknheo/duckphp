<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp\Core;

use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;
use DuckPhp\Core\View;

trait KernelTrait
{
    public $options = [];

    protected $kernel_options = [
            //// not override options ////
            'use_autoloader' => false,
            
            //// basic config ////
            'path' => null,
            'namespace' => null,
            'name' => null,
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
            
            'on_inited' => null,
            'container_only' => false,
            'override_class' => null,
            'override_class_from' => null,
            
            'setting_file' => 'config/setting.php',
            'setting_file_ignore_exists' => true,
            'setting_file_enable' => true,
            'use_env_file' => false,
        ];
    protected $setting = [];
    
    protected $default_run_handler = null;

    protected $is_simple_mode = true;
    protected $is_root = true;

    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    {
        $instance = static::G()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    public static function Current()
    {
        $phase = static::Phase();
        $class = $phase ? $phase : static::class;
        return $class::G();
    }
    public static function Root()
    {
        return (self::class)::G(); // remark ,don't use self::G()!
    }
    public static function InRootPhase()
    {
        $phase = static::Phase();
        if (!$phase) {
            return true;
        }
        return $phase === get_class(static::Root()) ? true:false;
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
        $path = realpath(dirname($_SERVER['SCRIPT_FILENAME']).'/../');
        $path = (string)$path;
        $path = ($path !== '') ? rtrim($path, '/').'/' : '';
        
        return $path;
    }
    public function getProjectPathFromClass($class, $use_parent_namespace = true)
    {
        $ref = new \ReflectionClass($class);
        $file = $ref->getFileName();
        $dir = dirname(dirname(''.$file));
        if ($use_parent_namespace) {
            $dir = dirname($dir);
        }
        return $dir .'/';
    }
    ////////
    public function getContainer()
    {
        return PhaseContainer::GetContainer();
    }
    public static function Phase($new = null)
    {
        return static::G()->_Phase($new);
    }
    public function _Phase($new = null)
    {
        $container = $this->getContainer();
        if (!$container) {
            return '';
        }
        $old = $container->getCurrentContainer();
        if ($new) {
            $container->setCurrentContainer($new);
        }
        return $old;
    }
    protected function checkSimpleMode($context)
    {
        $extApps = [];
        $exts = $this->options['ext'] ?? [];
        foreach ($exts as $class => $options) {
            if (\is_subclass_of($class, self::class)) {
                $this->is_simple_mode = false;
                $extApps[$class] = $class; /** @phpstan-ignore-line */
            }
        }
        $this->is_root = !(\is_a($context, self::class));

        if ($this->is_root && empty($extApps)) {
            $this->is_simple_mode = true;
            (self::class)::G($this); // remark ,don't use self::G()!
            static::G($this);
            if ($this->options['override_class_from'] ?? false) {
                $class = $this->options['override_class_from'];
                $class::G($this);
            }
            //if (true) {
            return true;
            //}
        }
        //////////////////////////////
        $apps = [];
        if ($this->is_root || $this->options['container_only']) {
            //$autoloader = AutoLoader::G();
            $this->onBeforeCreatePhases();
            $flag = PhaseContainer::ReplaceSingletonImplement();
            $this->onAfterCreatePhases();
            
            $container = $this->getContainer();
            $container->setDefaultContainer(static::class);
            $container->setCurrentContainer(static::class);
        } else {
            $flag = PhaseContainer::ReplaceSingletonImplement();
            $container = $this->getContainer();
            $container->setCurrentContainer(static::class);
        }

        /////////////
        $apps[static::class] = $this;
        if ($this->is_root) {
            $apps[self::class] = $this;
        }
        if ($this->options['override_class_from'] ?? null) {
            $class = $this->options['override_class_from'];
            $apps[$class] = $this;
        }
        
        $container->addPublicClasses(array_keys($apps));
        $container->addPublicClasses(array_keys($extApps));
        foreach ($apps as $class => $object) {
            $class = (string)$class;
            $class::G($object);
        }
        return false;
    }
    //init
    public function init(array $options, object $context = null)
    {
        $options['path'] = $options['path'] ?? ($this->options['path'] ?? $this->getDefaultProjectPath());
        $options['namespace'] = $options['namespace'] ?? $this->getDefaultProjectNameSpace($options['override_class'] ?? null);
        $this->initOptions($options);
        if ($this->options['use_short_functions']) {
            require_once __DIR__.'/Functions.php';
        }
        
        //TODO use_autoloader 不再需要。
        if (($options['use_autoloader'] ?? $this->options['use_autoloader']) || ($options['path_namespace'] ?? false)) {
            $options['path'] = $options['path'] ?? $this->getDefaultProjectPath();
            $options['namespace'] = $options['namespace'] ?? $this->getDefaultProjectNameSpace($options['override_class'] ?? null);
            AutoLoader::G()->init($options, $this)->run();
        }
        if ($options['override_class'] ?? false) {
            $class = $options['override_class'];
            unset($options['override_class']);
            $options['override_class_from'] = static::class;
            return $class::G(new $class)->init($options);
        }
        
        $this->checkSimpleMode($context);
        
        $this->onPrepare();
        
        $this->reloadFlags($context);
        
        $exception_options = [
            'default_exception_handler' => [static::class,'OnDefaultException'],
            'dev_error_handler' => [static::class,'OnDevErrorHandler'],
        ];
        if (!$this->is_root) {
            $exception_option['handle_all_dev_error'] = false;
            $exception_option['handle_all_exception'] = false;
        }
        ExceptionManager::G()->init($exception_options, $this);


        $this->initComponents($this->options, $context);
        
        $this->initExtentions($this->options['ext'] ?? []);
        $this->onInit();
        if ($this->options['on_inited']) {
            ($this->options['on_inited'])();
        }
        $this->is_inited = true;
        return $this;
    }
    protected function initComponents(array $options, object $context = null)
    {
        Route::G()->init($this->options, $this);
        Runtime::G()->init($this->options, $this);
        $this->doInitComponents();
    }
    protected function doInitComponents()
    {
        //for override
    }
    protected function reloadFlags($context): void
    {
        if (!$this->options['use_flag_by_setting']) {
            return;
        }
        if ($this->is_root) {
            $this->loadSetting();
            $setting = $this->setting;
        } else {
            $setting = static::Root()->_Setting(null);
        }
        $this->options['is_debug'] = $setting['duckphp_is_debug'] ?? $this->options['is_debug'];
        $this->options['platform'] = $setting['duckphp_platform'] ?? $this->options['platform'];
    }
    protected function loadSetting()
    {
        $this->setting = $this->options['setting'] ?? [];
        
        if ($this->options['use_env_file']) {
            $env_setting = parse_ini_file(realpath($this->options['path']).'/.env');
            $env_setting = $env_setting?:[];
            $this->setting = array_merge($this->setting, $env_setting);
        }
        if ($this->options['setting_file_enable']) {
            $this->dealWithSettingFile();
        }
        return;
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
    public function _Setting($key)
    {
        return $key ? (static::Root()->setting[$key] ?? null) : static::Root()->setting;
    }
    protected function initExtentions(array $exts): void
    {
        try {
            foreach ($exts as $class => $options) {
                $options = ($options === true)?$this->options:$options;
                $options = is_string($options)?$this->options[$options]:$options;
                if ($options === false) {
                    continue;
                }
                $class = (string)$class;
                if (!class_exists($class)) {
                    continue;
                }
                $class::G()->init($options, $this);
                $this->_Phase(static::class);
            }
        } catch (\Throwable $ex) {
            $this->_OnDefaultException($ex);
        }
        return;
    }
    public function run(): bool
    {
        $ret = false;
        $is_exceptioned = false;
        $this->_Phase(static::class);
        if ($this->is_root) {
            (self::class)::G($this); // remark ,don't use self::G()!
        }
        
        try {
            $this->onBeforeRun();
            if (!$this->default_run_handler) {
                Runtime::G()->run();
                if (!($this->options['container_only'] ?? false)) {
                    $ret = Route::G()->run();
                }
                if (!$ret) {
                    $ret = $this->runExtentions();
                    $this->_Phase(static::class);
                    if (!$ret && $this->is_root && !($this->options['skip_404_handler'] ?? false)) {
                        $this->_On404();
                    }
                }
            } else {
                // is_root => $ret = Console::G()->run();
                $ret = ($this->default_run_handler)();
            }
        } catch (\Throwable $ex) {
            Runtime::G()->onException($this->options['skip_exception_check']);
            if ($this->options['skip_exception_check']) {
                throw $ex;
            }
            $last_phase = $this->_Phase(static::class);
            Runtime::G()->lastPhase = $last_phase;
            ExceptionManager::CallException($ex);
            
            Runtime::G()->clear();
            $ret = true;
            $is_exceptioned = true;
        }
        if (!$is_exceptioned) {
            Runtime::G()->clear();
        }
        $this->onAfterRun();
        return $ret;
    }
    protected function runExtentions()
    {
        $flag = false;
        foreach ($this->options['ext'] as $class => $options) {
            if (\is_subclass_of($class, self::class)) {
                $flag = $class::G()->run();
                if ($flag) {
                    break;
                }
            }
        }
        return $flag;
    }
    //main produce end
    
    public function replaceDefaultRunHandler(callable $handler = null): void
    {
        $this->default_run_handler = $handler;
    }
    ////////////////////////
    //for override
    public static function On404(): void
    {
        static::G()->_On404();
    }
    public static function OnDefaultException($ex): void
    {
        static::G()->_OnDefaultException($ex);
    }
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        static::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
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
    }
    protected function onAfterCreatePhases()
    {
    }
    protected function onPrepare()
    {
    }
    protected function onInit()
    {
    }
    protected function onBeforeRun()
    {
    }
    protected function onAfterRun()
    {
    }
}
