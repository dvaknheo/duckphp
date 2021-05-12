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
use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\RuntimeState;
use DuckPhp\Core\View;

trait KernelTrait
{
    public $options = [];

    //protected $extDynamicComponentClasses = [];

    protected static $options_default = [
            //// not override options ////
            'use_autoloader' => false,
            'skip_plugin_mode_check' => false,
            
            //// basic config ////
            'path' => null,
            'namespace' => null,
            'override_class' => '',
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
        ];
    public $onPrepare;
    public $onInit;
    public $onBeforeRun;
    public $onAfterRun;
    
    protected $default_run_handler = null;
    protected $error_view_inited = false;

    // for app
    protected $handler_for_exception_handler;

    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    {
        $instance = static::G()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    public static function Blank()
    {
        // keep me for callback
    }
    protected function initOptions(array $options)
    {
        $this->options = array_replace_recursive($this->options, $options);
    }
    protected function checkOverride($override_class)
    {
        if (empty($override_class)) {
            return $this;
        }
        if (!class_exists($override_class)) {
            return $this;
        }
        if (static::class === $override_class) {
            return $this;
        }
        
        $object = $override_class::G();
        return $object;
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
    //init
    public function init(array $options, object $context = null)
    {
        if (isset($context) && !($options['skip_plugin_mode_check'] ?? self::$options_default['skip_plugin_mode_check'])) {
            return $this->pluginModeInit($options, $context);
        }
        
        $options['path'] = $options['path'] ?? $this->getDefaultProjectPath();
        $options['namespace'] = $options['namespace'] ?? $this->getDefaultProjectNameSpace($options['override_class'] ?? null);
        
        if (($options['use_autoloader'] ?? self::$options_default['use_autoloader']) || ($options['path_namespace'] ?? false)) {
            AutoLoader::G()->init($options, $this)->run();
        }
        $object = $this->checkOverride($options['override_class'] ?? null);
        $this->saveInstance($object);
        
        return $object->initAfterOverride($options, $context);
    }
    protected function saveInstance($object)
    {
        (self::class)::G($object);
        static::G($object);
    }
    //for override
    protected function pluginModeInit(array $options, object $context = null)
    {
        return $this;
    }
    protected function initAfterOverride(array $options, object $context = null)
    {
        $this->initOptions($options);
        $exception_options = [
            'system_exception_handler' => $this->handler_for_exception_handler, //////TODO
            'default_exception_handler' => [static::class,'OnDefaultException'],
            'dev_error_handler' => [static::class,'OnDevErrorHandler'],
        ];
        ExceptionManager::G()->init($exception_options, $this)->run();
        
        $this->onPrepare();
        
        $this->initDefaultComponents();
        $this->initExtentions($this->options['ext']);
        $this->onInit();
        
        $this->is_inited = true;
        return $this;
    }
    //for override
    protected function initDefaultComponents()
    {
        if ($this->options['use_short_functions']) {
            require_once __DIR__.'/Functions.php';
        }
        Configer::G()->init($this->options, $this);
        $this->reloadFlags();
        
        View::G()->init($this->options, $this);
        $this->error_view_inited = true;
        
        Route::G()->init($this->options, $this);
        RuntimeState::G()->init($this->options, $this);
    }
    protected function reloadFlags(): void
    {
        if (!$this->options['use_flag_by_setting']) {
            return;
        }
        $is_debug = Configer::G()->_Setting('duckphp_is_debug');
        $platform = Configer::G()->_Setting('duckphp_platform');
        $this->options['is_debug'] = $is_debug ?? $this->options['is_debug'];
        $this->options['platform'] = $platform ?? $this->options['platform'];
    }
    protected function initExtentions(array $exts): void
    {
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
        }
        return;
    }
    //for override
    protected function onPrepare()
    {
        if ($this->onPrepare) {
            return ($this->onPrepare)();
        }
    }
    //for override
    protected function onInit()
    {
        if ($this->onInit) {
            return ($this->onInit)();
        }
    }
    //for override
    protected function onBeforeRun()
    {
        if ($this->onBeforeRun) {
            return ($this->onBeforeRun)();
        }
    }
    //for override
    protected function onAfterRun()
    {
        if ($this->onAfterRun) {
            return ($this->onAfterRun)();
        }
    }
    public function run(): bool
    {
        if ($this->default_run_handler) {
            return ($this->default_run_handler)();
        }
        try {
            $this->beforeRun();
            $this->onBeforeRun();
            $ret = Route::G()->run();
            
            if (!$ret && !$this->options['skip_404_handler']) {
                $this->_On404();
            }
        } catch (\Throwable $ex) {
            RuntimeState::G()->toggleInException();
            if ($this->options['skip_exception_check']) {
                RuntimeState::G()->clear();
                throw $ex;
            }
            //$this->onException();
            ExceptionManager::CallException($ex);
            $ret = true;
        }
        $this->onAfterRun();
        RuntimeState::G()->clear();
        return $ret;
    }
    public function beforeRun()
    {
        $classes = $this->getDynamicComponentClasses();
        foreach ($classes as $v) {
            $v::G()->reset();
        }
    }
    public function getDynamicComponentClasses()
    {
        $ret = [
            RuntimeState::class,
            ExceptionManager::class,
            View::class,
            Route::class,
        ];
        $ret = array_merge($ret, $this->extDynamicComponentClasses ?? []);
        return $ret;
    }
    
    //main produce end
    
    public function replaceDefaultRunHandler(callable $handler = null): void
    {
        $this->default_run_handler = $handler;
    }
    ////////////////////////
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
}
