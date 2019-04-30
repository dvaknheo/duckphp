<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS\Core;

use DNMVCS\Core\SingletonEx;
use DNMVCS\Core\ThrowOn;

use DNMVCS\Core\Route;
use DNMVCS\Core\AutoLoader;
use DNMVCS\Core\ExceptionManager;
use DNMVCS\Core\Configer;
use DNMVCS\Core\View;
use DNMVCS\Core\RuntimeState;

class App
{
    use SingletonEx;
    use ThrowOn;

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
            'is_debug'=>false,
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
    
    public $is_debug=true;
    public $platform='';
    public $path=null;
    public $override_root_class='';
    protected $beforeRunHandlers=[];
    protected $error_view_inited=false;
    protected $is_before_show_done=false;
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
        
        $options['on_404_handler']=[static::class,'On404'];
        $options['exception_handler']=[static::class,'OnException'];
        $options['dev_error_handler']=[static::class,'OnDevErrorHandler'];
        
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
        $override_class::G()->override_root_class=static::class;
        return static::G($override_class::G());
    }
    public function getOverrideRootClass()
    {
        return $this->override_root_class;
    }
    //@override me
    public function init($options=[], $context=null)
    {
        if (!$this->override_root_class) {
            $options=$this->adjustOptions($options);
            AutoLoader::G()->init($options, $this)->run();
            ExceptionManager::G()->init($options, $this);
            $object=$this->checkOverride($options);
            $this->is_debug=true;
            if ($object) {
                self::G($object);
                $object->initOptions($options);
                return $object->init($options);
            }
            self::G($this);
        } else {
            $this->initOptions($options);
        }
        $this->is_debug=$this->options['is_debug'];
        
        return $this->initAfterOverride();
    }
    protected function initAfterOverride()
    {
        if ($this->options['enable_cache_classes_in_cli'] && PHP_SAPI==='cli') {
            AutoLoader::G()->cacheClasses();
        }
        
        
        Configer::G()->init($this->options, $this);
        $this->reloadFlags();
        
        View::G()->init($this->options, $this);
        $this->error_view_inited=true;
        
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
        
        $this->cleanUp();
        return $ret;
    }
    public function cleanUp()
    {
        if (!$this->is_before_show_done) {
            foreach ($this->beforeShowHandlers as $v) {
                ($v)();
            }
            $this->is_before_show_done=true;
        }
        RuntimeState::G()->end();
    }
    public function reloadFlags()
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
        $data['is_debug']=static::IsDebug();
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
            $desc=$is_error?'Error':'Exception';
            echo "Internal $desc \n<!--DNMVCS -->\n";
            if ($this->is_debug) {
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
        $error_shortfile=(substr($errfile, 0, strlen($this->path))==$this->path)?substr($errfile, strlen($this->path)):$errfile;
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
        // TODO CleanUp;
        if ($this->exit_handler) {
            return ($this->exit_handler)($code);
        }
        $this->cleanUp();
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
        $this->is_before_show_done=true;
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
trait Core_Instance
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
        $ret[]=static::class;
        $ret[]=self::class;
        $ret[]=$this->getOverrideRootClass();
        return $ret;
    }
    public function getDynamicComponentClasses()
    {
        return $this->dynamicComponentClasses;
    }
    public function addDynamicComponentClass($class)
    {
        return $this->dynamicComponentClasses[]=$class;
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
