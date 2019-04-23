<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;

use DNMVCS\Core\Route;
use DNMVCS\Core\AutoLoader;
use DNMVCS\Core\ExceptionManager;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;
use DNMVCS\Core\RuntimeState;

class App
{
    use SingletonEx;

    const VERSION = '1.1.0';
    
    use Core_Handler;
    use Core_Glue;
    use Core_Redirect;
    use Core_SystemWrapper;
    use Core_Helper;
    use Core_Instance;
    
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            'skip_app_autoload'=>false,
            
            //// properties ////
            'override_class'=>'Base\App',
            'is_dev'=>false,
            'platform'=>'',
            'path_view'=>'view',
            'path_config'=>'config',
            'skip_view_notice_error'=>true,
            'use_inner_error_view'=>false,
            'enable_cache_classes_in_cli'=>true,
            
            //// config ////
            'setting_file_basename'=>'setting',
            'all_config'=>[],
            'setting'=>[],
            'reload_for_flags'=>true,
            
            //// error handler ////
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
            
            //// controller ////
            'namespace_controller'=>'Controller',
            'controller_base_class'=>null,
            'controller_prefix_post'=>'do_',
                'controller_enable_paramters'=>false,
                'controller_methtod_for_miss'=>null,
                'controller_hide_boot_class'=>false,
                'controller_welcome_class'=>'Main',
                'controller_index_method'=>'index',
            'ext'=>[],
        ];
    const DEFAULT_OPTIONS_EX=[
        ];
    public $options=[];
    
    public $is_dev=false;
    public $platform='';
    public $path=null;
    public $override_root_class='';
    protected $beforeRunHandlers=[];
    
    public static function RunQuickly(array $options=[], callable $after_init=null)
    {
        if (!$after_init) {
            return static::G()->init($options)->run();
        }
        static::G()->init($options);
        ($after_init)();
        static::G()->run();
    }
    protected function adjustOptions($options=[])
    {
        if (!isset($options['path']) || !$options['path']) {
            $path=realpath(getcwd().'/../');
            $options['path']=$path;
        }
        $options['path']=rtrim($options['path'], '/').'/';
        
        return $options;
    }
    protected function initOptions($options=[])
    {
        $options=$this->adjustOptions($options);
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS_EX, $options);
        
        $options['on_404_handler']=[static::class,'On404'];
        $options['before_show_handler']=[static::class,'OnBeforeShow'];
        $options['exception_handler']=[static::class,'OnException'];
        $options['dev_error_handler']=[static::class,'OnDevErrorHandler'];
        //$options['system_exception_handler']=[static::class,'set_exception_handler'];
        
        $this->options=$options;
        $this->path=$this->options['path'];
        
        $this->is_dev=$this->options['is_dev'];
        $this->platform=$this->options['platform'];
        if ($this->options['use_inner_error_view']) {
            $this->options['error_404']=null;
            $this->options['error_500']=null;
            $this->options['error_exception']=null;
            $this->options['error_debug']=null;
        }
        if (method_exists(static::class, 'set_exception_handler')) {
            $this->options['system_exception_handler']=[static::class,'set_exception_handler'];
        }
    }
    protected function checkOverride($options)
    {
        if ($this->override_root_class) {
            return null;
        }
        $this->override_root_class=static::class;
        
        $override_class=isset($options['override_class'])?$options['override_class']:static::DEFAULT_OPTIONS['override_class'];
        $namespace=isset($options['namespace'])?$options['namespace']:static::DEFAULT_OPTIONS['namespace'];
        
        if (substr($override_class, 0, 1)!=='\\') {
            $override_class=$namespace.'\\'.$override_class;
        }
        $override_class=ltrim($override_class, '\\');
        
        if (!$override_class || !class_exists($override_class)) {
            return null;
        }
        if (static::class===$override_class) {
            return null;
        }
        return static::G($override_class::G());
    }
    public function getOverrideRootClass()
    {
        return $this->override_root_class;
    }
    //@override me
    public function init($options=[], $context=null)
    {
        $options=$this->adjustOptions($options);
        AutoLoader::G()->init($options, $this)->run();
        
        $object=$this->checkOverride($options);
        if ($object) {
            $object->override_root_class=static::class;
            if (!defined('DNMVCS_CLASS')) {
                define('DNMVCS_CLASS', static::class);
            }
            return $object->init($options);
        }
        $this->initOptions($options);
        return $this->initAfterOverride();
    }
    protected function initAfterOverride()
    {
        if ($this->options['enable_cache_classes_in_cli'] && PHP_SAPI==='cli') {
            AutoLoader::G()->cacheClasses();
        }
        
        ExceptionManager::G()->init($this->options, $this);
        Configer::G()->init($this->options, $this);
        View::G()->init($this->options, $this);
        Route::G()->init($this->options, $this);
        
        $this->initExtentions($this->options['ext']);
        return $this;
    }
    protected function initExtentions($exts)
    {
        $t=explode('\\', $this->override_root_class);
        array_pop($t);
        $ns=implode('\\', $t).'\\';
        
        
        foreach ($exts as $ext =>$options) {
            if ($options===false) {
                continue;
            }
            $options=($options===true)?$this->options:$options;
            $options=is_string($options)?$this->options[$options]:$options;
            $class='';
            do {
                $class=$ns.$ext;
                if (class_exists($ns)) {
                    break;
                }
            } while (false);
            
            if (!$class) {
                continue;
            }
            $class::G()->init($options, $this);
        }
        return;
    }
    public function addBeforeRunHandler($handler)
    {
        $this->beforeRunHandlers[]=$handler;
    }
    public function run()
    {
        foreach ($this->beforeRunHandlers as $v) {
            ($v)();
        }
        $class=get_class(RuntimeState::G());  //ReCreateInstance;
        RuntimeState::G(new $class)->begin();
        $ret=Route::G()->run();
        RuntimeState::G()->end();
        return $ret;
    }
}
trait Core_Handler
{
    protected $beforeShowHandlers=[];
    protected $is_in_exception=false;
    public static function OnBeforeShow($data, $view=null)
    {
        return static::G()->_OnBeforeShow($data, $view);
    }
    public static function On404()
    {
        return static::G()->_On404();
    }
    public static function OnException($ex)
    {
        return static::G()->_OnException($ex);
    }
    public static function OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        return static::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
    }
    public function _OnBeforeShow($data, $view=null)
    {
        if ($view===null) {
            View::G()->view=Route::G()->getRouteCallingPath();
        }
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
        if ($this->options['skip_view_notice_error']) {
            RuntimeState::G()->skipNoticeError();
        }
    }
    public function _On404()
    {
        $error_view=$this->options['error_404'];
        static::header('', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        if (!$error_view) {
            echo "404 File Not Found\n<!--DNMVCS -->\n";
            return;
        }
        
        $view=View::G();
        $view->setViewWrapper(null, null);
        $view->_Show([], $error_view);
        RuntimeState::G()->end();
    }
    
    public function _OnException($ex)
    {
        $this->is_in_exception=true;
        //TODO tell me why
        $flag=ExceptionManager::G()->checkAndRunErrorHandlers($ex, true);
        if ($flag) {
            return;
        }
        static::header('', true, 500);
        
        $view=View::G();
        $data=[];
        $data['is_developing']=static::IsDebug();
        $data['ex']=$ex;
        $data['message']=$ex->getMessage();
        $data['code']=$ex->getCode();
        $data['trace']=$ex->getTraceAsString();

        $is_error=is_a($ex, 'Error') || is_a($ex, 'ErrorException')?true:false;
        if ($this->options) {
            $error_view=$is_error?$this->options['error_500']:$this->options['error_exception'];
        } else {
            $error_view=null;
        }
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            return;
        }
        if (!$error_view) {
            $desc=$is_error?'Error':'Exception';
            echo "Internal $desc \n<!--DNMVCS -->\n";
            if ($this->is_dev) {
                echo "<hr />";
                echo "\n<pre>Debug On\n\n";
                echo $data['trace'];
                echo "\n</pre>\n";
            }
            return;
        }
        $view->setViewWrapper(null, null);
        $view->_Show($data, $error_view);
        RuntimeState::G()->end();
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!$this->is_dev) {
            return;
        }
        $descs=array(
            E_USER_NOTICE=>'E_USER_NOTICE',
            E_NOTICE=>'E_NOTICE',
            E_STRICT=>'E_STRICT',
            E_DEPRECATED=>'E_DEPRECATED',
            E_USER_DEPRECATED=>'E_USER_DEPRECATED',
        );
        $error_shortfile=(substr($errfile, 0, strlen($this->path))==$this->path)?substr($errfile, strlen($this->path)):$errfile;
        $data=array(
            'errno'=>$errno,
            'errstr'=>$errstr,
            'errfile'=>$errfile,
            'errline'=>$errline,
            'error_desc'=>$descs[$errno],
            'error_shortfile'=>$error_shortfile,
        );
        $error_view=$this->options['error_debug'];
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($data);
            return;
        }
        if (!$error_view) {
            extract($data);
            echo  <<<EOT
<!--DNMVCS  use view/_sys/error-debug.php to override me -->
<fieldset class="_DNMVC_DEBUG">
	<legend>$error_desc($errno)</legend>
<pre>
{$error_shortfile}:{$errline}
{$errstr}
</pre>
</fieldset>

EOT;
            return;
        }
        View::G()->_ShowBlock($error_view, $data);
    }
    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[]=$handler;
    }
}

trait Core_SystemWrapper
{
    public $header_handler=null;
    public $exit_handler=null;

    public static function header($output, bool $replace = true, int $http_response_code=0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public function _header($output, bool $replace = true, int $http_response_code=0)
    {
        if ($this->header_handler) {
            return ($this->header_handler)($output, $replace, $http_response_code);
        }
        if (PHP_SAPI==='cli') {
            return;
        }
        if (headers_sent()) {
            return;
        }
        return header($output, $replace, $http_response_code);
    }
    public static function exit_system($code=0)
    {
        return static::G()->_exit_system($code);
    }
    public function _exit_system($code=0)
    {
        if ($this->exit_handler) {
            return ($this->exit_handler)($code);
        }
        exit($code);
    }
}
trait Core_Redirect
{
    public static function ExitJson($ret)
    {
        return static::G()->_ExitJson($ret);
    }
    public static function ExitRedirect($url, $only_in_site=true)
    {
        return static::G()->_ExitRedirect($url, $only_in_site);
    }
    public static function ExitRouteTo($url)
    {
        return static::G()->_ExitRedirect(static::URL($url), true);
    }
    public static function Exit404()
    {
        static::On404();
        static::exit_system();
    }
    ////
    public function _ExitJson($ret)
    {
        static::header('Content-Type:text/json');
        // DNMVCS::G()->onBeforeShow([],'');
        echo json_encode($ret, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        static::exit_system();
    }
    public function _ExitRedirect($url, $only_in_site=true)
    {
        if ($only_in_site && parse_url($url, PHP_URL_HOST)) {
            //something  wrong
            static::exit_system();
            return;
        }
        // DNMVCS::G()->onBeforeShow([],'');
        static::header('location: '.$url, true, 302);
        static::exit_system();
    }
}

trait Core_Helper
{
    public static function ThrowOn($flag, $message, $code=0, $exception_class='')
    {
        if (!$flag) {
            return;
        }
        $exception_class=$exception_class?:\Exception::class;
        throw new $exception_class($message, $code);
    }
    // system static
    public static function Platform()
    {
        return static::G()->platform;
    }
    public static function IsDebug()
    {
        return static::G()->is_dev;
    }
    public static function InException()
    {
        return static::G()->is_in_exception;
    }
    ////
    public static function H($str)
    {
        return static::G()->_H($str);
    }

    public function _H(&$str)
    {
        if (is_string($str)) {
            $str=htmlspecialchars($str, ENT_QUOTES);
            return $str;
        }
        if (is_array($str)) {
            foreach ($str as $k =>&$v) {
                static::_H($v);
            }
            return $str;
        }
        
        if (is_object($str)) {
            $arr=get_object_vars($str);
            foreach ($arr as $k =>&$v) {
                static::_H($v);
            }
            return $arr;
        }
        return $str;
    }
}
trait Core_Glue
{
    //state
    public static function IsRunning()
    {
        return RuntimeState::G()->isRunning();
    }
    // route static
    public static function URL($url=null)
    {
        return Route::G()->_URL($url);
    }
    public static function Parameters()
    {
        return Route::G()->_Parameters();
    }
    // view static
    public static function Show($data=[], $view=null)
    {
        return View::G()->_Show($data, $view);
    }
    public static function ShowBlock($view, $data=null)
    {
        return View::G()->_ShowBlock($view, $data);
    }
    // config static
    public static function Setting($key)
    {
        return Configer::G()->_Setting($key);
    }
    public static function Config($key, $file_basename='config')
    {
        return Configer::G()->_Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return Configer::G()->_LoadConfig($file_basename);
    }
    
    /////////////////////////////////
    //autoloader
    public function assignPathNamespace($path, $namespace=null)
    {
        return AutoLoader::G()->assignPathNamespace($path, $namespace);
    }
    // route
    public function addRouteHook($hook, $prepend=false, $once=true)
    {
        return Route::G()->addRouteHook($hook, $prepend, $once);
    }
    public function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
    }
    public function bindServerData($data)
    {
        return Route::G()->bindServerData($data);
    }
    
    //view
    public function setViewWrapper($head_file=null, $foot_file=null)
    {
        return View::G()->setViewWrapper($head_file, $foot_file);
    }
    public function assignViewData($key, $value=null)
    {
        return View::G()->assignViewData($key, $value);
    }
    //exception manager
    public function assignExceptionHandler($classes, $callback=null)
    {
        return ExceptionManager::G()->assignExceptionHandler($classes, $callback);
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        return ExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
    }
    public function setDefaultExceptionHandler($callback)
    {
        return ExceptionManager::G()->setDefaultExceptionHandler($callback);
    }
}
trait Core_Instance
{
    protected $staticClasses=[];
    protected $dynamicClasses=[];
    
    public function getStaticClasses()
    {
        $ret=[
            AutoLoader::class,
            ExceptionManager::class,
            Configer::class,
            View::class,
            Route::class,
        ];
        return $ret;
    }
    public function getDynamicClasses()
    {
        return $this->dynamicClasses;
    }
    public function addDynamicClass($class)
    {
        return $this->dynamicClasses[]=$class;
    }
}
