<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\ThrowOn;

use DNMVCS\Core\AutoLoader;
use DNMVCS\Core\Configer;
use DNMVCS\Core\ExceptionManager;
use DNMVCS\Core\Route;
use DNMVCS\Core\RuntimeState;
use DNMVCS\Core\View;
use DNMVCS\Core\SuperGlobal;

class App
{
    use SingletonEx;
    use ThrowOn;

    const VERSION = '1.1.2';
    
    use Core_Handler;
    use Core_Glue;
    use Core_Redirect;
    use Core_SystemWrapper;
    use Core_Helper;
    use Core_ComponentClasses;
    use Core_SuperGlobal;
    
    const DEFAULT_OPTIONS=[
            //// basic config ////
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            
            //// properties ////
            'is_debug'=>false,
            'platform'=>'',
            'ext'=>[],
            
            'override_class'=>'Base\App',
            'reload_for_flags'=>true,
            'use_super_global'=>false,
            'skip_view_notice_error'=>true,
            'skip_404_handler'=>false,
            
            //// error handler ////
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
            
            //// Class Autoloader ////
            // 'path'=>null,
            // 'namespace'=>'MY',
            // 'path_namespace'=>'app',
            // 'skip_system_autoload'=>true,
            'skip_app_autoload'=>false,
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
        ];
    const DEFAULT_OPTIONS_EX=[
        ];
    public $options=[];
    
    public $is_debug=true;
    public $platform='';
    public $path=null;
    public $override_root_class='';
    
    protected $beforeRunHandlers=[];
    protected $error_view_inited=false;
    
    public static function RunQuickly(array $options=[], callable $after_init=null)
    {
        if (!$after_init) {
            return static::G()->init($options)->run();
        }
        static::G()->init($options);
        ($after_init)();
        return static::G()->run();
    }
    public static function _EmptyFunction()
    {
        return;
    }
    protected function adjustOptions($options=[])
    {
        if (!isset($options['path']) || !$options['path']) {
            $path=realpath($_SERVER['SCRIPT_FILENAME'].'/../');
            $options['path']=$path;
        }
        $options['path']=rtrim($options['path'], '/').'/';
        
        $options['exception_handler']=[static::class,'OnException'];
        $options['dev_error_handler']=[static::class,'OnDevErrorHandler'];
        $options['system_exception_handler']=[static::class,'set_exception_handler'];
        
        return $options;
    }
    protected function initOptions($options=[])
    {
        $options=$this->adjustOptions($options);
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS_EX, $options);
        $this->options=$options;
        
        $this->path=$this->options['path'];
        
        $this->is_debug=$this->options['is_debug'];
        $this->platform=$this->options['platform'];
    }
    protected function checkOverride($options)
    {
        $override_class=$options['override_class']??static::DEFAULT_OPTIONS['override_class'];
        $namespace=$options['namespace']??static::DEFAULT_OPTIONS['namespace'];
        
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
    //@override me
    public function init($options=[], $context=null)
    {
        AutoLoader::G()->init($options, $this)->run();
        ExceptionManager::G()->init($options, $this)->run();
        
        $this->override_root_class=static::class;
        $object=$this->checkOverride($options);
        
        $object=$object??$this;
        (self::class)::G($object);
        $object->override_class=static::class;
        
        $options=$this->adjustOptions($options);
        $object->initOptions($options);
        $object->onInit();
        
        return $object->initAfterOverride();
    }
    protected function initAfterOverride()
    {
        Configer::G()->init($this->options, $this);
        $this->reloadFlags();
        
        View::G()->init($this->options, $this);
        $this->error_view_inited=true;
        
        Route::G()->init($this->options, $this);
        $this->initExtentions($this->options['ext']);
        $this->initSuperGlobal();
        
        
        return $this;
    }
    protected function initExtentions($exts)
    {
        $DefaultNamespace='DNMVCS\\';
        
        foreach ($exts as $ext =>$options) {
            if ($options===false) {
                continue;
            }
            $options=($options===true)?$this->options:$options;
            $options=is_string($options)?$this->options[$options]:$options;
            $class='';
            do {
                $class=$DefaultNamespace.$ext;
                if (class_exists($class)) {
                    break;
                }
                $class=$this->options['namespace'].'\\'.$class;
                if (class_exists($class)) {
                    break;
                }
                $class=ltrim($class, '\\');
                if (class_exists($class)) {
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
    protected function initSuperGlobal()
    {
        if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            $func=DNMVCS_SUPER_GLOBAL_REPALACER;
            SuperGlobal::G($func());
            $this->options['use_super_global']=true;
        }
        if ($this->options['use_super_global']) {
            $this->bindServerData(SuperGlobal::G()->_SERVER);
            return;
        }
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
        $this->onRun();
        
        RuntimeState::ReCreateInstance();
        RuntimeState::G()->begin();
        
        $ret=Route::G()->run();
        if (!$ret && !$this->options['skip_404_handler']) {
            static::On404();
        }
        
        $this->cleanUp();
        return $ret;
    }
    protected function onInit()
    {
        // for override;
        return;
    }
    protected function onRun()
    {
        // for override;
        return;
    }
    public function cleanUp()
    {
        //is_before_show_done => RuntimeState ?
        if (! RuntimeState::G()->is_before_show_done) {
            foreach ($this->beforeShowHandlers as $v) {
                ($v)();
            }
            RuntimeState::G()->is_before_show_done=true;
        }
        RuntimeState::G()->end();
        $this->is_in_exception=false;
    }
    protected function reloadFlags()
    {
        if (!$this->options['reload_for_flags']) {
            return;
        }
        $is_debug=Configer::G()->_Setting('is_debug');
        $platform=Configer::G()->_Setting('platform');
        if (isset($is_debug)) {
            $this->is_debug=$is_debug;
        }
        if (isset($platform)) {
            $this->platform=$platform;
        }
    }
}
trait Core_Handler
{
    protected $beforeShowHandlers=[];
    protected $is_in_exception=false;
    protected $error_view_inited=false;
    
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
    public function _On404()
    {
        $error_view=$this->options['error_404'];
        $error_view=$this->error_view_inited?$error_view:null;
        
        static::header('', true, 404);
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)();
            return;
        }
        if (!$error_view) {
            echo "404 File Not Found\n<!--DNMVCS -->\n";
            return;
        }
        
        $this->setViewWrapper(null, null);
        $this->_Show([], $error_view);
        $this->cleanUp();
    }
    
    public function _OnException($ex)
    {
        $this->is_in_exception=true;
        $flag=ExceptionManager::G()->checkAndRunErrorHandlers($ex, true);
        if ($flag) {
            return;
        }
        
        $is_error=is_a($ex, 'Error') || is_a($ex, 'ErrorException')?true:false;
        $error_view=$is_error?$this->options['error_500']:$this->options['error_exception'];
        $error_view=$this->error_view_inited?$error_view:null;
        
        static::header('', true, 500);
        $data=[];
        $data['is_debug']=true;//static::IsDebug();
        $data['ex']=$ex;
        $data['class']=get_class($ex);
        $data['message']=$ex->getMessage();
        $data['code']=$ex->getCode();
        $data['trace']=$ex->getTraceAsString();
        
        if (!is_string($error_view) && is_callable($error_view)) {
            ($error_view)($ex);
            return;
        }
        if (!$error_view) {
            $desc=$is_error?'Internal Error':'Internal Exception';
            echo "$desc \n<!--DNMVCS -->\n";
            
            if ($data['is_debug']) {
                echo "<h3>{$data['class']}({$data['code']}):{$data['message']}</h3>";
                echo "\n<pre>Debug On\n\n";
                echo $data['trace'];
                echo "\n</pre>\n";
            }
            return;
        }
        
        $this->setViewWrapper(null, null);
        $this->_Show($data, $error_view);
        $this->cleanUp();
    }
    public function _OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!$this->is_debug) {
            return;
        }
        $descs=array(
            E_USER_NOTICE=>'E_USER_NOTICE',
            E_NOTICE=>'E_NOTICE',
            E_STRICT=>'E_STRICT',
            E_DEPRECATED=>'E_DEPRECATED',
            E_USER_DEPRECATED=>'E_USER_DEPRECATED',
        );
        if ($this->path) {
            $error_shortfile=(substr($errfile, 0, strlen($this->path))==$this->path)?substr($errfile, strlen($this->path)):$errfile;
        }else{
            $error_shortfile=$errfile;
        }
        $data=array(
            'errno'=>$errno,
            'errstr'=>$errstr,
            'errfile'=>$errfile,
            'errline'=>$errline,
            'error_desc'=>$descs[$errno],
            'error_shortfile'=>$error_shortfile,
        );
        $error_view=$this->options['error_debug']??'';
        $error_view=$this->error_view_inited?$error_view:null;
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
    public $cookie_handler=null;
    public $exit_handler=null;
    public $exception_handler=null;
    public $shutdown_handler=null;
    
    public static function header($output, bool $replace = true, int $http_response_code=0)
    {
        return static::G()->_header($output, $replace, $http_response_code);
    }
    public static function setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        return static::G()->_setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }
    public static function exit_system($code=0)
    {
        return static::G()->_exit_system($code);
    }
    public static function set_exception_handler(callable $exception_handler)
    {
        return static::G()->_set_exception_handler($exception_handler);
    }
    public static function register_shutdown_function(callable $callback, ...$args)
    {
        return static::G()->_register_shutdown_function($callback, ...$args);
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
    public function _setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain  = '', bool $secure = false, bool $httponly = false)
    {
        if ($this->cookie_handler) {
            return ($this->cookie_handler)($key, $value, $expire, $path, $domain, $secure, $httponly);
        }
        return setcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function _exit_system($code=0)
    {
        // TODO CleanUp;
        if ($this->exit_handler) {
            return ($this->exit_handler)($code);
        }
        $this->cleanUp();
        exit($code);
    }
    public function _set_exception_handler(callable $exception_handler)
    {
        if ($this->exception_handler) {
            return ($this->exception_handler)($exception_handler);
        }
        return set_exception_handler($exception_handler);
    }
    public function _register_shutdown_function(callable $callback, ...$args)
    {
        if ($this->shutdown_handler) {
            return ($this->shutdown_handler)($callback, ...$args);
        }
        return register_shutdown_function($callback, ...$args);
    }
    public function system_wrapper_replace(array $funcs=[])
    {
        if (isset($funcs['header'])) {
            $this->header_handler=$funcs['header'];
        }
        if (isset($funcs['setcookie'])) {
            $this->cookie_handler=$funcs['setcookie'];
        }
        if (isset($funcs['exit_system'])) {
            $this->exit_handler=$funcs['exit_system'];
        }
        if (isset($funcs['set_exception_handler'])) {
            $this->exception_handler=$funcs['set_exception_handler'];
        }
        if (isset($funcs['register_shutdown_function'])) {
            $this->shutdown_handler=$funcs['register_shutdown_function'];
        }
        
        return true;
    }
    public static function system_wrapper_get_providers():array
    {
        $ret=[
            'header'                =>[static::class,'header'],
            'setcookie'             =>[static::class,'setcookie'],
            'exit_system'           =>[static::class,'exit_system'],
            'set_exception_handler' =>[static::class,'set_exception_handler'],
            'register_shutdown_function' =>[static::class,'register_shutdown_function'],
        ];
        return $ret;
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
        
        $flag= JSON_UNESCAPED_UNICODE |  JSON_NUMERIC_CHECK;
        if ($this->is_debug) {
            $flag=$flag | JSON_PRETTY_PRINT;
        }
        echo json_encode($ret, $flag);
        static::exit_system();
    }
    public function _ExitRedirect($url, $only_in_site=true)
    {
        if ($only_in_site && parse_url($url, PHP_URL_HOST)) {
            //something  wrong
            static::exit_system();
            return;
        }
        
        static::header('location: '.$url, true, 302);
        static::exit_system();
    }
}

trait Core_Helper
{

    // system static
    public static function Platform()
    {
        return static::G()->platform;
    }
    public static function IsDebug()
    {
        return static::G()->is_debug;
    }
    public static function isInException()
    {
        return static::G()->is_in_exception;
    }
    ////
    
    public static function Show($data=[], $view=null)
    {
        return static::G()->_Show($data, $view);
    }
    public static function H($str)
    {
        return static::G()->_H($str);
    }
    ////
    public function _Show($data=[], $view=null)
    {
        $view=$view??Route::G()->getRouteCallingPath();
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
        RuntimeState::G()->is_before_show_done=true;
        if ($this->options['skip_view_notice_error']) {
            RuntimeState::G()->skipNoticeError();
        }
        return View::G()->_Show($data, $view);
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
trait Core_ComponentClasses
{
    protected $staticComponentClasses=[];
    protected $dynamicComponentClasses=[];
    public function getStaticComponentClasses()
    {
        $ret=[
            AutoLoader::class,
            ExceptionManager::class,
            Configer::class,
            View::class,
            Route::class,
        ];
        $ret=array_merge($ret,$this->staticComponentClasses);
        
        $ret[]=static::class;
        $ret[]=self::class;
        $ret[]=$this->override_root_class;
        $ret=array_values(array_unique($ret));
        return $ret;
    }
    public function getDynamicComponentClasses()
    {
        $ret=[
            RuntimeState::class,
            SuperGlobal::class,
        ];
        $ret=array_merge($ret,$this->dynamicComponentClasses);
        $ret=array_values(array_unique($ret));
        
        return $ret;
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
    public function stopRunDefaultHandler()
    {
        return Route::G()->stopRunDefaultHandler();
    }
    public function getRouteCallingMethod()
    {
        return Route::G()->getRouteCallingMethod();
    }
    public function setRouteCallingMethod($method)
    {
        return Route::G()->getRouteCallingMethod($method);
    }
    protected function bindServerData($data)
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
    // ViewHelper
    public static function DumpTrace()
    {
        return static::G()->_DumpTrace();
    }
    public static function Dump(...$args)
    {
        return static::G()->_Dump(...$args);
    }
    public function _DumpTrace()
    {
        echo "<pre>\n";
        echo (new Exception('',0))->getTraceString();
        echo "</pre>\n";
    }
    public function _Dump(...$args)
    {
        echo "<pre>\n";
        var_dump(...$args);
        echo "</pre>\n";
    }
}
trait Core_SuperGlobal
{
    public static function SG()
    {
        return SuperGlobal::G();
    }
    public static function &GLOBALS($k, $v=null)
    {
        return SuperGlobal::G()->_GLOBALS($k, $v);
    }
    
    public static function &STATICS($k, $v=null)
    {
        return SuperGlobal::G()->_STATICS($k, $v, 1);
    }
    public static function &CLASS_STATICS($class_name, $var_name)
    {
        return SuperGlobal::G()->_CLASS_STATICS($class_name, $var_name);
    }
    public static function session_start(array $options=[])
    {
        return SuperGlobal::G()->session_start($options);
    }
    public function session_id($session_id=null)
    {
        return SuperGlobal::G()->session_id($session_id);
    }
    public static function session_destroy()
    {
        return SuperGlobal::G()->session_destroy();
    }
    public static function session_set_save_handler(\SessionHandlerInterface $handler)
    {
        return SuperGlobal::G()->session_set_save_handler($handler);
    }
}