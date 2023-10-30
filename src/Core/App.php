<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\ExceptionManager;
use DuckPhp\Core\KernelTrait;
use DuckPhp\Core\Logger;
use DuckPhp\Core\Route;
use DuckPhp\Core\Runtime;
use DuckPhp\Core\SuperGlobal;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\View;

/**
 * MAIN FILE
 * dvaknheo@github.com
 * OK，Lazy
 *
 */
class App extends ComponentBase
{
    use KernelTrait;
    const VERSION = '1.2.13-dev';

    const HOOK_PREPEND_OUTTER = 'prepend-outter';
    const HOOK_PREPEND_INNER = 'prepend-inner';
    const HOOK_APPPEND_INNER = 'append-inner';
    const HOOK_APPPEND_OUTTER = 'append-outter';
    
    protected $core_options = [
        'path_runtime' => 'runtime',
        'alias' => null,
        
        'default_exception_do_log' => true,
        'close_resource_at_output' => false,
        
        //// error handler ////
        'error_404' => null,          //'_sys/error-404',
        'error_500' => null,          //'_sys/error-500',
        'error_debug' => null,        //'_sys/error-debug',
    ];
    protected $common_options = [];
    protected $beforeShowHandlers = [];
    
    public function __construct()
    {
        parent::__construct();
        $this->options = array_replace_recursive($this->kernel_options, $this->core_options, $this->common_options, $this->options);
        unset($this->kernel_options); // not use again;
        unset($this->core_options); // not use again;
        unset($this->common_options); // not use again;
    }
    public function version()
    {
        return static::VERSION;
    }
    protected function doInitComponents()
    {
        if ($this->is_root) {
            $this->getContainer()->addPublicClasses([Logger::class, SuperGlobal::class]);
        }
        
        Logger::G()->init($this->options, $this);
        View::G()->init($this->options, $this);
        SuperGlobal::G()->init($this->options, $this);
    }
    //////// override KernelTrait ////////
    //@override
    public function _On404(): void
    {
        $error_view = $this->options['error_404'] ?? null;
        $error_view = $this->is_inited?$error_view:null;
        
        SystemWrapper::_()->_header('404 Not Found', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        //// no error_404 setting.
        if (!$error_view) {
            $_SERVER = defined('__SUPERGLOBAL_CONTEXT') ? (__SUPERGLOBAL_CONTEXT)()->_SERVER : $_SERVER;
            $path_info = $_SERVER['PATH_INFO'] ?? '';
            echo "404 File Not Found<!--PATH_INFO: ($path_info) DuckPhp set options ['error_404'] to override me. -->\n";
            if ($this->options['is_debug']) {
                echo "<!-- Route Error Info: ".Route::G()->getRouteError()."-->\n";
            }
            return;
        }
        
        View::G(new View())->init($this->options, $this);
        View::G()->_Show([], $error_view);
    }
    //@override
    public function _OnDefaultException($ex): void
    {
        if ($this->options['default_exception_do_log']) {
            try {
                static::Logger()->error('['.get_class($ex).']('.$ex->getMessage().')'.$ex->getMessage());
            } catch (\Throwable $ex) { // @codeCoverageIgnore
                //do nothing
            } // @codeCoverageIgnore
        }
        $error_view = $this->options['error_500'] ?? null;
        $error_view = $this->is_inited?$error_view:null;
        
        SystemWrapper::_()->_header('Server Error', true, 500);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            return;
        }
        
        $data = [];
        $data['is_debug'] = $this->_IsDebug();
        $data['ex'] = $ex;
        $data['class'] = get_class($ex);
        $data['message'] = $ex->getMessage();
        $data['code'] = $ex->getCode();
        $data['trace'] = $ex->getTraceAsString();
        $data['file'] = $ex->getFile();
        $data['line'] = $ex->getLine();
        
        //// no error_500 setting.
        if (!$error_view) {
            echo "Internal Error \n<!--DuckPhp set options['error_500'] to override me  -->\n";
            if (!$this->is_inited) {
                echo "<div>error trigger before init, options['error_500'] ignore. </div>";
            }
            if ($data['is_debug']) {
                echo "<h3>{$data['class']}({$data['code']}):{$data['message']}</h3>";
                echo "<div>{$data['file']} : {$data['line']}</div>";
                echo "\n<pre>Debug On\n\n";
                echo $data['trace'];
                echo "\n</pre>\n";
            } else {
                echo "<!-- DuckPhp set options ['is_debug'] to show debug info>\n";
            }
            return;
        }
        
        View::G(new View())->init($this->options, $this);
        View::G()->_Show($data, $error_view);
    }
    //@override
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline): void
    {
        if (!$this->_IsDebug()) {
            return;
        }
        $descs = array(
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_NOTICE => 'E_NOTICE',
            E_STRICT => 'E_STRICT',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        );
        $error_shortfile = $errfile;
        if (!empty($this->options['path'])) {
            $path = $this->options['path'];
            $error_shortfile = (substr($errfile, 0, strlen($path)) == $path)?substr($errfile, strlen($path)):$errfile;
        }
        $data = array(
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            'error_desc' => $descs[$errno],
            'error_shortfile' => $error_shortfile,
        );
        $error_view = $this->options['error_debug'] ?? '';
        $error_view = $this->is_inited?$error_view:null;
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        $error_desc = '';
        $ext = ($this->is_inited)? '':"<div>error trigger before init, options['error_debug'] ignore.";
        if (!$error_view) {
            extract($data);
            echo  <<<EOT
<!--DuckPhp  set options ['error_debug']='_sys/error-debug.php' to override me -->
<fieldset class="_DuckPhp_DEBUG">
    <legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
{$ext}
</pre>
</fieldset>

EOT;
            return;
        }
        View::G()->_Display($error_view, $data);
    }
    public function getProjectPath()
    {
        return static::Root()->options['path'];
    }
    public function getRuntimePath()
    {
        $path = static::SlashDir(static::Root()->options['path']);
        $path_runtime = static::SlashDir(static::Root()->options['path_runtime']);
        return static::IsAbsPath($path_runtime) ? $path_runtime : $path.$path_runtime;
    }
    
    public function getOverrideableFile($path_sub, $file)
    {
        if (static::IsAbsPath($file)) {
            return $file;
        }
        if (static::IsAbsPath($path_sub)) {
            return static::SlashDir($path_sub) . $file;
        }
        if (!$this->is_root) {
            $path_main = static::Root()->options['path'];
            $name = $this->options['alias'] ?? str_replace("\\", '/', $this->options['namespace']);
            
            $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub). static::SlashDir($name) . $file;
            if (!file_exists($full_file)) {
                $path_main = $this->options['path'];
                $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub).$file;
            }
        } else {
            $path_main = $this->options['path'];
            $full_file = static::SlashDir($path_main) . static::SlashDir($path_sub) . $file;
        }
        
        return $full_file;
    }
    //////// features

    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[] = $handler;
    }
    public function removeBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers = array_filter($this->beforeShowHandlers, function ($v) use ($handler) {
            return $v != $handler;
        });
    }
    public function skip404Handler()
    {
        $this->options['skip_404_handler'] = true;
    }
    public function onBeforeOutput()
    {
        //if (!$this->options['close_resource_at_output']) {
        //    return;
        //}
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
    }
    public function adjustViewFile($view)
    {
        return $view === '' ? Route::G()->getRouteCallingPath() : $view;
    }
    //}
    //trait Core_Helper
    //{

    

    ///////
    public static function Platform()
    {
        return static::_()->_Platform();
    }
    public function _Platform()
    {
        return $this->options['platform'];
    }
    public static function IsDebug()
    {
        return static::_()->_IsDebug();
    }
    public function _IsDebug()
    {
        return static::_()->options['is_debug'];
    }
    public static function IsRealDebug()
    {
        return static::_()->_IsRealDebug();
    }
    public function _IsRealDebug()
    {
        //you can override this;
        return $this->_IsDebug();
    }

    public static function PhaseCall($phase, $callback, ...$args)
    {
        return static::G()->_PhaseCall($phase, $callback, ...$args);
    }
    public function _PhaseCall($phase, $callback, ...$args)
    {
        $phase = is_object($phase) ? get_class($phase) : $phase;
        $current = $this->_Phase();
        if (!$phase || !$current) {
            return ($callback)(...$args);
        }
        
        $this->_Phase($phase);
        $ret = ($callback)(...$args);
        $this->_Phase($current);
        return $ret;
    }
    // config static
    public static function Setting($key)
    {
        return static::G()->_Setting($key);
    }
    public static function Pager($object = null)
    {
        return static::_()->_Pager($object);
    }
    public function _Pager($object = null)
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public static function Event()
    {
        return static::G()->_Event();
    }
    public function _Event()
    {
        throw new \ErrorException("DuckPhp No Impelement " . __FUNCTION__);
    }
    public static function Logger($object = null)
    {
        return Logger::G($object);
    }
    ///////////////
    //exception manager
    public static function CallException($ex)
    {
        return ExceptionManager::G()->_CallException($ex);
    }
    public static function Display($view, $data = null)
    {
        return View::G()->_Display($view, $data);
    }
    public static function getViewData()
    {
        return View::G()->getViewData();
    }
    public static function isInException()
    {
        return Runtime::G()->isInException();
    }
    public static function isRunning()
    {
        return Runtime::G()->isRunning();
    }
    public static function IsAjax()
    {
        return Helper::_()->_IsAjax();
    }
    /////////////////
    
    // route static

    public static function Res($url = null)
    {
        return Route::G()->_Res($url);
    }
    public static function Domain($use_scheme = false)
    {
        return Route::G()->_Domain($use_scheme);
    }
    ///////
    public static function replaceController($old_class, $new_class)
    {
        return Route::G()->replaceController($old_class, $new_class);
    }
    public static function addRouteHook($callback, $position = 'append-outter', $once = true)
    {
        return Route::G()->addRouteHook($callback, $position, $once);
    }
}
