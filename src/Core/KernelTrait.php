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
    use ContainerTrait;
    public $options = [];

    protected static $options_default = [
            //// not override options ////
            'use_autoloader' => false,
            'skip_plugin_mode_check' => false,
            
            //// basic config ////
            'path' => null,
            'namespace' => null,
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
            //'after_init_handler' => null,
        ];
    
    protected $default_run_handler = null;
    protected $error_view_inited = false;

    protected $isSimpleMode = true;
    protected $isChild = false;
    // for app
    protected $handler_for_exception_handler;

    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    {
        $instance = static::G()->init($options);
        if ($after_init) {
            ($after_init)();
        }
        if (!$instance) {
            return false;
        }
        return $instance->run();
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
    protected function switchContainerContext($class)
    {
        if ($this->isSimpleMode) {
            return false;
        }
        $class = __SINGLETONEX_REPALACER_CLASS; /** @phpstan-ignore-line */
        $class::SwitchContainer($class);
    }
    protected function initContainerContext()
    {
        if ($this->isSimpleMode) {
            return false;
        }
        $flag = static::ReplaceSingletonImplement();
        // if false ,do something?
        $class = __SINGLETONEX_REPALACER_CLASS; /** @phpstan-ignore-line */
        $class::SetDefaultContainer(static::class);
        $class::SwitchContainer(static::class);
    }
    protected function addSharedInstances($classes)
    {
        if ($this->isSimpleMode) {
            return false;
        }
        
        $class = __SINGLETONEX_REPALACER_CLASS; /** @phpstan-ignore-line */
        $class::AddPublicClasses($classes);
    }
    public static function Root()
    {
        $class = self::class;
        return $class::G();
    }
    protected function checkSimpleMode($context)
    {
        $extApps = [];
        foreach ($this->options['ext'] as $class => $options) {
            $t = $class;
            if (is_a($t, self::class)) {
                $this->isSimpleMode = false;
                //$class = is_string($class)?$class:get_class($class);
                $extApps[$class] = $class;
            }
        }
        $this->isChild = is_a($context, self::class);
        
        if (!$this->isChild && empty($extApps)) {
            $this->isSimpleMode = true;
            (self::class)::G($this); // remark ,don't use self::G()!
            static::G($this);
            if ($this->options['override_class_from'] ?? false) {
                $class = $this->options['override_class_from'];
                $class::G($this);
            }
            return true;
        }
        if (!$this->isChild) {
            $this->initContainerContext();
        }
        ///////////// 这里测试的时候有问题
        $apps = [];
        $apps[AutoLoader::class] = AutoLoader::G();
        $apps[static::class] = $this;
        if (!$this->isChild) {
            $apps[self::class] = $this;
        }
        if ($this->options['override_class_from'] ?? null) {
            $class = $this->options['override_class_from'];
            $apps[$class] = $this;
        }
        
        $this->switchContainerContext(static::class);
        
        $this->addSharedInstances(array_keys($apps));
        foreach ($apps as $class => $object) {
            $class = (string)$class;
            $class::G($object);
        }
        $this->addSharedInstances(array_keys($extApps));
        return false;
    }
    //init
    public function init(array $options, object $context = null)
    {
        $options['path'] = $options['path'] ?? $this->getDefaultProjectPath();
        $options['namespace'] = $options['namespace'] ?? $this->getDefaultProjectNameSpace($options['override_class'] ?? null);
        $this->initOptions($options);
        if ($this->options['use_short_functions']) {
            require_once __DIR__.'/Functions.php';
        }
        if (($options['use_autoloader'] ?? self::$options_default['use_autoloader']) || ($options['path_namespace'] ?? false)) {
            $options['path'] = $options['path'] ?? $this->getDefaultProjectPath();
            $options['namespace'] = $options['namespace'] ?? $this->getDefaultProjectNameSpace($options['override_class'] ?? null);
            AutoLoader::G()->init($options, $this)->run();
        }
        if ($options['override_class'] ?? false) {
            $class = $options['override_class'];
            unset($options['override_class']);
            $options['override_class_from'] = static::class;
            if (\class_exists($class)) {
                return null;
            }
            return $class::G(new $class)->init($options);
        }
        
        $this->checkSimpleMode($context);
        
        $this->onPrepare();
        $this->initComponents($this->options, $context);
        $this->initExtentions($this->options['ext']);
        
        $this->onInit();
        
        $this->is_inited = true;
        return $this;
    }
    protected function getProjectPathFromClass($class, $use_parent_namespace = true)
    {
        $ref = new \ReflectionClass(static::class);
        $file = $ref->getFileName();
        $dir = dirname(dirname(''.$file));
        if ($use_parent_namespace) {
            $dir = dirname($dir);
        }
        return $dir .'/';
    }
    protected function initComponents(array $options, object $context = null)
    {
        $exception_options = [
            'system_exception_handler' => $this->handler_for_exception_handler,
            'default_exception_handler' => [static::class,'OnDefaultException'],
            'dev_error_handler' => [static::class,'OnDevErrorHandler'],
        ];
        
        //
        if ($context && is_a($context, self::class)) {
            $this->options['skip_404_handler'] = true;
            
            $exception_option['handle_all_dev_error'] = false;
            $exception_option['handle_all_exception'] = false;
            
            // deal with path
            $this->options['path'] = $context->options['path'];
            
            $this->options['namespace'] = $this->options['namespace'] ?? $this->getDefaultProjectNameSpace($options['override_class'] ?? null);
            $postfix = str_replace("\\", '/', $this->options['namespace']);
            $this->options['path_config'] = $this->options['path_config'] ?? ($context->options['path_config'] ?? 'config') . $postfix;
            $this->options['path_view'] = $this->options['path_view'] ?? ($context->options['path_view'] ?? 'view') . $postfix;
            if (!isset($this->options['path_override_from'])) {
                $this->options['path_override_from'] = $this->getProjectPathFromClass(static::class);
            }
            $this->options['path_config_override_from'] = $this->options['path_override_from']. 'config/';
            $this->options['path_view_override_from'] = $this->options['path_override_from']. 'view/';
        }
        
        ExceptionManager::G()->init($exception_options, $this)->run();
        
        
        
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
            $this->switchContainerContext(static::class);
        }
        return;
    }
    //for override
    protected function onPrepare()
    {
        //
    }
    //for override
    protected function onInit()
    {
    }
    //for override
    protected function onBeforeRun()
    {
    }
    //for override
    protected function onAfterRun()
    {
    }
    public function run(): bool
    {
        //TODO 命令行模式，和扩展的命令行处理
        $this->switchContainerContext(static::class);
        
        try {
            $this->onBeforeRun();
            if (!$this->default_run_handler) {
                $ret = Route::G()->run();
                if (!$ret) {
                    $this->runExtentions();
                    if (!$this->options['skip_404_handler']) {
                        $this->_On404();
                    }
                }
            } else {
                return ($this->default_run_handler)();
            }
        } catch (\Throwable $ex) {
            $this->switchContainerContext(static::class);
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
    protected function runExtentions()
    {
        $flag = false;
        foreach ($this->options['ext'] as $class => $options) {
            if (is_a($class, self::class)) {
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
