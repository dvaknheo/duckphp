<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp\Core;

use DuckPhp\Core\SingletonEx;

use DuckPhp\Core\AutoLoader;
use DuckPhp\Core\Configer;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\Route;
use DuckPhp\Core\RuntimeState;
use DuckPhp\Core\View;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\Logger;

trait Kernel
{
    use SingletonEx;
    
    public $options = [
            //// basic config ////
            'path' => null,
            'namespace' => 'MY',
            'path_namespace' => 'app',
            
            //// properties ////
            'is_debug' => false,
            'platform' => '',
            'ext' => [],
            
            'override_class' => 'Base\App',
            
            'use_flag_by_setting' => true,
            'use_super_global' => false,
            'use_short_functions' => false,
            
            'log_errors' => true,
            
            'skip_404_handler' => false,
            'skip_plugin_mode_check' => false,
            'skip_exception_check' => false,
            'skip_fix_path_info' => false,
            
            //// error handler ////
            'handle_all_dev_error' => true,
            'handle_all_exception' => true,
            'error_404' => null,          //'_sys/error-404',
            'error_500' => null,          //'_sys/error-500',
            'error_debug' => null,        //'_sys/error-debug',
            
            //// Class Autoloader ////
            // 'path'=>null,
            // 'namespace'=>'MY',
            // 'path_namespace'=>'app',
            // 'skip_system_autoload'=>true,
            'skip_app_autoload' => false,
            //'enable_cache_classes_in_cli'=>true,

            //// Class Configer ////
            // 'path'=>null,
            // 'path_config'=>'config',
            // 'all_config'=>[],
            // 'setting'=>[],
            // 'setting_file'=>'setting',
            // 'skip_setting_file'=>false,
            
            //// Class View Class ////
            // 'path'=>null,
            // 'path_view'=>'view',
            

            //// Class Route ////
            // 'path'=>null,
            // 'namespace'=>'MY',
            // 'namespace_controller'=>'Controller',
            // 'controller_base_class'=>null,
            // 'controller_welcome_class'=>'Main',
            // 'controller_hide_boot_class'=>false,
            // 'controller_methtod_for_miss'=>null,
            // 'controller_prefix_post'=>'do_',
            // 'controller_postfix'=>'',
        ];
    public $is_debug = true;
    public $platform = '';
    protected $defaultRunHandler = null;
    protected $error_view_inited = false;

    // for kernel
    protected $hanlder_for_exception_handler;
    protected $hanlder_for_exception;
    protected $hanlder_for_develop_exception;
    protected $hanlder_for_404;
    
    public static function RunQuickly(array $options = [], callable $after_init = null): bool
    {
        $instance = static::G()->init($options);
        if (!$instance) {
            return true;
        }
        if ($after_init) {
            ($after_init)();
        }
        return $instance->run();
    }
    protected function initOptions($options = [])
    {
        if (!isset($options['path']) || !$options['path']) {
            $path = realpath($_SERVER['SCRIPT_FILENAME'].'/../');
            $options['path'] = (string)$path;
        }
        $options['path'] = rtrim($options['path'], '/').'/';
        $this->options = array_replace_recursive($this->options, $options);
        
        $this->is_debug = $this->options['is_debug'];
        $this->platform = $this->options['platform'];
    }
    protected function checkOverride($options)
    {
        $override_class = $options['override_class'] ?? $this->options['override_class'];
        $namespace = $options['namespace'] ?? $this->options['namespace'];
        
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
        if (!($options['skip_plugin_mode_check'] ?? false) && isset($context)) {
            return $this->pluginModeInit($options, $context);
        }
        AutoLoader::G()->init($options, $this)->run();
        
        $handle_all_dev_error = $options['handle_all_dev_error'] ?? $this->options['handle_all_dev_error'];
        $handle_all_exception = $options['handle_all_exception'] ?? $this->options['handle_all_exception'];

        $exception_options = [
            'handle_all_dev_error' => $handle_all_dev_error,
            'handle_all_exception' => $handle_all_exception,
            
            'system_exception_handler' => $this->hanlder_for_exception_handler,
            'default_exception_handler' => $this->hanlder_for_exception,
            'dev_error_handler' => $this->hanlder_for_develop_exception,
        ];
        ExceptionManager::G()->init($exception_options, $this)->run();
        
        $object = $this->checkOverride($options);
        $object->initOptions($options);
        return $object->onInit();
    }
    //for override
    protected function pluginModeInit(array $options, object $context = null)
    {
        return $this;
    }
    //for override
    protected function onInit()
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
        
        $this->initExtentions($this->options['ext']);
        
        return $this;
    }
    protected function reloadFlags(): void
    {
        if (!$this->options['use_flag_by_setting']) {
            return;
        }
        $is_debug = Configer::G()->_Setting('duckphp_is_debug');
        $platform = Configer::G()->_Setting('duckphp_platform');
        if (isset($is_debug)) {
            $this->is_debug = $is_debug;
        }
        if (isset($platform)) {
            $this->platform = $platform;
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
    protected function onRun()
    {
        return;
    }
    public function run(): bool
    {
        if ($this->defaultRunHandler) {
            return ($this->defaultRunHandler)();
        }
        $this->beforeRun();
        try {
            $this->onRun();
            $ret = Route::G()->run();
            
            if (!$ret && !$this->options['skip_404_handler']) {
                ($this->hanlder_for_404)();// = static::On404();
            }
        } catch (\Throwable $ex) {
            RuntimeState::G()->toggleInException();
            if ($this->options['skip_exception_check']) {
                throw $ex;
            }
            ExceptionManager::OnException($ex);
            $ret = true;
        }
        $this->clear();
        return $ret;
    }
    protected function beforeRun()
    {
        RuntimeState::ReCreateInstance()->begin();
        View::G()->setViewWrapper(null, null);
        
        $serverData = ($this->options['use_super_global'] ?? false) ? SuperGlobal::G()->_SERVER : $_SERVER;
        if (!$this->options['skip_fix_path_info'] && PHP_SAPI != 'cli') {
            $serverData = $this->fixPathInfo($serverData); // @codeCoverageIgnore
        }
        Route::G()->bindServerData($serverData);
        
        if (!empty($this->beforeShowHandlers)) {
            //header_register_callback([static::class,'OnOutputBuffering']);
            ob_start([static::class,'OnOutputBuffering']);
        }
    }
    public function clear(): void
    {
        RuntimeState::G()->end();
        if (!empty($this->beforeShowHandlers)) {
            ob_end_flush();
        }
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
        $request_path = parse_url($serverData['REQUEST_URI'], PHP_URL_PATH) ?? '';
        $request_file = substr($serverData['SCRIPT_FILENAME'], strlen($serverData['DOCUMENT_ROOT']));
        
        if ($request_file === '/index.php' && substr($request_path, 0, strlen($request_file)) !== '/index.php') {
            $path_info = $request_path;
        } else {
            $path_info = substr($request_path, strlen($request_file));
            $path_info = (string)$path_info;  //shit phpstan
        }
        
        $serverData['PATH_INFO'] = $path_info;
        return $serverData;
    }
    //main produce end
    
    ////////////////////////
    public function replaceDefaultRunHandler(callable $handler = null): void
    {
        $this->defaultRunHandler = $handler;
    }
    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[] = $handler;
    }
    public static function OnOutputBuffering($str = '')
    {
        return static::G()->_OnOutputBuffering($str);
    }
    public function _OnOutputBuffering($str)
    {
        $flag = RuntimeState::G()->isOutputed();
        if ($flag) {
            return $str;
        }
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
        RuntimeState::G()->toggleOutputed();
        return $str;
    }
}
