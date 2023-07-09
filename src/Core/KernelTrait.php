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
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
        ];
    
    protected $default_run_handler = null;
    protected $error_view_inited = false;

    protected $isPluginMode = false;
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
    protected function switchContextContainer()
    {
        if($this->isSimpleMode){
            return false;
        }
        
        
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
            AutoLoader::G()->init($options, $this)->run();
            // 切换 context 之后，要
        }
        
        if (empty($context) && empty($this->options['ext'])) {
            $this->isSimpleMode = true;
        }
        
        // 这里要检测一下 self::class, 设置为共享模式，
        // 要加个 设置为共享类的入口
        $this->switchContainerContext(static::class);

        
        $this->onPrepare();
        $this->initComponents($this->options, $context);
        $this->initExtentions($this->options['ext']);
        
        $this->onInit();
        
        $this->is_inited = true;
        return $this;
    }
    protected function initComponents(array $options, object $context = null)
    {
        
        $exception_options = [
            'system_exception_handler' => $this->handler_for_exception_handler,
            'default_exception_handler' => [static::class,'OnDefaultException'],
            'dev_error_handler' => [static::class,'OnDevErrorHandler'],
        ];
        // 错误报告方面，只处理自己的，不接管
        if (is_a($this, self::class)) {
            $this->options['skip_404_handler'] = true;
            $exception_option['handle_all_dev_error'] = false;
            $exception_option['handle_all_exception'] = false;
        }
        ExceptionManager::G()->init($exception_options, $this)->run();
        
        // configer 和 view 的 path_override 处理
        // path_override
        
        
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
            $this->switchContextContainer();
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
        if ($this->onAfterRun) {
            return ($this->onAfterRun)();
        }
    }
    public function run(): bool
    {
        //TODO 命令行模式，和扩展的命令行处理
        $this->switchContextContainer();
        if ($this->default_run_handler) {
            return ($this->default_run_handler)();
        }
        try {
            $this->beforeRun();
            $this->onBeforeRun();
            $ret = Route::G()->run();
            
            if (!$ret) {
                $this->runExtentsion();
                if(!$this->options['skip_404_handler']){
                    $this->_On404();
                }
                
            }
        } catch (\Throwable $ex) {
            $this->switchContextContainer();
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
                if($flag){
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
