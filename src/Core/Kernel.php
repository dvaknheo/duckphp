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
use DuckPhp\Core\ComponentInterface;
use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Logger;
use DuckPhp\Core\Route;
use DuckPhp\Core\RuntimeState;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\View;

trait Kernel
{
    // use SingletonEx; OK ，turn on this for full work;
    
    public $options = [];
    protected static $options_default = [
    
            //// not override options ////
            'use_autoloader' => true,
            'skip_plugin_mode_check' => false,
            'handle_all_dev_error' => true,
            'handle_all_exception' => true,
            'override_class' => 'System\App',
            'path_namespace' => 'app',
            
            //// basic config ////
            'path' => '',
            'namespace' => 'LazyToChange',
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'use_flag_by_setting' => true,
            'use_super_global' => true,
            'use_short_functions' => true,
            
            'skip_404_handler' => false,
            'skip_exception_check' => false,
            'skip_fix_path_info' => false,
            
            //// error handler ////
            'error_404' => null,          //'_sys/error-404',
            'error_500' => null,          //'_sys/error-500',
            'error_debug' => null,        //'_sys/error-debug',
            
            
        ];
    public $onPrepare;
    public $onInit;
    public $onRun;
    
    protected $default_run_handler = null;
    protected $error_view_inited = false;

    // for app
    protected $hanlder_for_exception_handler;
    protected $hanlder_for_exception;
    protected $hanlder_for_develop_exception;
    protected $hanlder_for_404;

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
    protected function initOptions($options = [])
    {
        $this->options = array_replace_recursive($this->options, $options);
        if (empty($this->options['path'])) {
            $path = realpath($_SERVER['SCRIPT_FILENAME'].'/../');
            $this->options['path'] = (string)$path;
        }
        $this->options['path'] = ($this->options['path'] !== '') ? rtrim($this->options['path'], '/').'/' : '';
    }
    protected function checkOverride($options)
    {
        $override_class = $options['override_class'] ?? self::$options_default['override_class'];
        $namespace = $options['namespace'] ?? self::$options_default['namespace'];
        
        if (substr($override_class, 0, 1) !== '\\') {
            $override_class = $namespace.'\\'.$override_class;
        }
        $override_class = ltrim($override_class, '\\');
        
        $object = null;
        if (!$override_class || !class_exists($override_class)) {
            $object = $this;
        } elseif (static::class === $override_class) {
            $object = $this;
        } else {
            $object = $override_class::G();
        }
        
        (self::class)::G($object);
        static::G($object);
        
        return $object;
    }
    //init
    public function init(array $options, object $context = null)
    {
        if (!($options['skip_plugin_mode_check'] ?? self::$options_default['skip_plugin_mode_check']) && isset($context)) {
            return $this->pluginModeInit($options, $context);
        }
        
        if ($options['use_autoloader'] ?? self::$options_default['use_autoloader']) {
            AutoLoader::G()->init($options, $this)->run();
        }
        
        $handle_all_dev_error = $options['handle_all_dev_error'] ?? self::$options_default['handle_all_dev_error'];
        $handle_all_exception = $options['handle_all_exception'] ?? self::$options_default['handle_all_exception'];
        $exception_options = [
            'handle_all_dev_error' => $handle_all_dev_error,
            'handle_all_exception' => $handle_all_exception,
            
            'system_exception_handler' => $this->hanlder_for_exception_handler,
            'default_exception_handler' => $this->hanlder_for_exception,
            'dev_error_handler' => $this->hanlder_for_develop_exception,
        ];
        ExceptionManager::G()->init($exception_options, $this)->run();
        
        $object = $this->checkOverride($options);
        return $object->initAfterOverride($options, $context);
    }
    //for override
    protected function pluginModeInit(array $options, object $context = null)
    {
        return $this;
    }
    protected function initAfterOverride(array $options, object $context = null)
    {
        $this->initOptions($options);
        
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
        Logger::G()->init($this->options, $this);
    }
    protected function reloadFlags(): void
    {
        if (!$this->options['use_flag_by_setting']) {
            return;
        }
        $is_debug = Configer::G()->_Setting('duckphp_is_debug');
        $platform = Configer::G()->_Setting('duckphp_platform');
        if (isset($is_debug)) {
            $this->options['is_debug'] = $is_debug;
        }
        if (isset($platform)) {
            $this->options['platform'] = $platform;
        }
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
    protected function onRun()
    {
        if ($this->onRun) {
            return ($this->onRun)();
        }
    }
    public function run(): bool
    {
        if ($this->default_run_handler) {
            return ($this->default_run_handler)();
        }
        try {
            $this->beforeRun();
            $this->onRun();
            $ret = Route::G()->run();
            
            if (!$ret && !$this->options['skip_404_handler']) {
                ($this->hanlder_for_404)();
            }
        } catch (\Throwable $ex) {
            RuntimeState::G()->toggleInException();
            if ($this->options['skip_exception_check']) {
                RuntimeState::G()->clear();
                throw $ex;
            }
            ExceptionManager::CallException($ex);
            $ret = true;
        }
        $this->clear();
        return $ret;
    }
    public function beforeRun()
    {
        RuntimeState::ReCreateInstance()->init($this->options, $this)->run();
        View::G()->reset();
        
        $serverData = ($this->options['use_super_global'] ?? false) ? SuperGlobal::G()->_SERVER : $_SERVER;
        if (!$this->options['skip_fix_path_info'] && PHP_SAPI != 'cli') {
            $serverData = $this->fixPathInfo($serverData); // @codeCoverageIgnore
        }
        Route::G()->bindServerData($serverData);
    }
    public function clear(): void
    {
        RuntimeState::G()->clear();
    }
    protected function fixPathInfo(&$serverData)
    {
        if (!empty($serverData['PATH_INFO'])) {
            $serverData['PATH_INFO'] = $serverData['PATH_INFO'] ?? '';
            return $serverData;
        }
        if (!isset($serverData['REQUEST_URI'])) {
            $serverData['PATH_INFO'] = $serverData['PATH_INFO'] ?? '';
            return $serverData;
        }
        $request_path = (string)parse_url($serverData['REQUEST_URI'], PHP_URL_PATH);
        $request_file = substr($serverData['SCRIPT_FILENAME'], strlen($serverData['DOCUMENT_ROOT']));
        
        if ($request_file === '/index.php' && substr($request_path, 0, strlen($request_file)) !== '/index.php') {
            $path_info = $request_path;
        } else {
            $path_info = substr($request_path, strlen($request_file));
        }
        
        $serverData['PATH_INFO'] = $path_info;
        return $serverData;
    }
    //main produce end
    
    ////////////////////////
    public function replaceDefaultRunHandler(callable $handler = null): void
    {
        $this->default_run_handler = $handler;
    }
}
