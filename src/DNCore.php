<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\DNSingleton;
use DNMVCS\DNCore_Glue;

use DNMVCS\DNException;
use DNMVCS\DNRoute;
use DNMVCS\DNAutoLoader;
use DNMVCS\DNExceptionManager;
use DNMVCS\DNConfiger;
use DNMVCS\DNView;
use DNMVCS\DNRuntimeState;

class DNCore
{
    use DNSingleton;

    const VERSION = '1.1.0';
    
    use DNCore_Handler;
    use DNCore_Glue;
    use DNCore_Redirect;
    use DNCore_SystemWrapper;
    use DNCore_Helper;
    
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            'skip_app_autoload'=>false,
            //// controller ////
            'namespace_controller'=>'Controller',
            'base_controller_class'=>null,
            'enable_paramters'=>false,
            'disable_default_class_outside'=>false,
            'default_method_for_miss'=>null,
            'enable_post_prefix'=>true,
            'prefix_post'=>'do_',
            
            //// properties ////
            'override_class'=>'Base\App',
            'path_view'=>'view',
            'path_config'=>'config',
            'path_lib'=>'lib',
            'is_dev'=>false,
            'platform'=>'',
            //// actions ////
            'skip_view_notice_error'=>true,
            'enable_cache_classes_in_cli'=>true,
            
            //// config ////
            'all_config'=>[],
            'setting'=>[],
            'setting_file_basename'=>'setting',
            
            //// error handler ////
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
        ];
    const DEFAULT_OPTIONS_EX=[
        ];
    public $skip_override=false;
    public $root_class='';
    public $options=[];
    
    public $is_dev=false;
    public $platform='';
    
    protected $path=null;
    protected $path_lib=null;
    protected $stop_show_exception=false;
    
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
        $options['skip_system_autoload']=true;//isset($options['skip_system_autoload'])?$options['skip_system_autoload']:(class_exists('Composer\Autoload\ClassLoader')?true:false);
        
        $options['on_404_handler']=[static::class,'On404'];
        $options['before_show_handler']=[static::class,'OnBeforeShow'];
        $options['exception_handler']=[static::class,'OnException'];
        $options['dev_error_handler']=[static::class,'OnDevErrorHandler'];
        $options['system_exception_handler']=[static::class,'set_exception_handler'];  // TODO
        
        return $options;
    }
    protected function initOptions($options=[])
    {
        $options=$this->adjustOptions($options);
        $options=array_replace_recursive(static::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS_EX, $options);
        $this->options=$options;
        
        $this->path=$this->options['path'];
        
        $this->path_lib=$this->path.rtrim($this->options['path_lib'], '/').'/';
        $this->is_dev=$this->options['is_dev'];
        $this->platform=$this->options['platform'];
    }
    protected function checkOverride($options)
    {
        if ($this->skip_override) {
            return null;
        }
        $this->root_class=static::class;
        
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

    //@override me
    public function init($options=[], $context=null)
    {
        $options=$this->adjustOptions($options);
        DNAutoLoader::G()->init($options)->run();
        
        $object=$this->checkOverride($options);
        if ($object) {
            $object->skip_override=true;
            $object->root_class=static::class;
            return $object->init($options);
        }
        return $this->initAfterOverride($options);
    }
    protected function initAfterOverride($options)
    {
        $this->initOptions($options);
        DNExceptionManager::G()->init($this->options, $this);
        DNConfiger::G()->init($this->options, $this);
        
        ////[[[[
        $this->is_dev=DNConfiger::G()->_Setting('is_dev')??$this->is_dev;
        $this->platform=DNConfiger::G()->_Setting('platform')??$this->platform;
        if ($this->options['enable_cache_classes_in_cli'] && PHP_SAPI==='cli') {
            DNAutoLoader::G()->cacheClasses();
        }
        ////]]]]
        
        DNView::G()->init($this->options, $this);
        DNRoute::G()->init($this->options, $this);
        return $this;
    }
    public function run()
    {
        $class=get_class(DNRuntimeState::G());  //ReCreateInstance;
        DNRuntimeState::G(new $class)->begin();
        $ret=DNRoute::G()->run();
        DNRuntimeState::G()->end();
        return $ret;
    }
}
trait DNCore_Handler
{
    protected $beforeShowHandlers=[];
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
    public function OnDevErrorHandler($errno, $errstr, $errfile, $errline)
    {
        return static::G()->_OnDevErrorHandler($errno, $errstr, $errfile, $errline);
    }
    public function _OnBeforeShow($data, $view=null)
    {
        if ($view===null) {
            DNView::G()->view=DNRoute::G()->getRouteCallingPath();
        }
        foreach ($this->beforeShowHandlers as $v) {
            ($v)();
        }
        if ($this->options['skip_view_notice_error']) {
            DNRuntimeState::G()->skipNoticeError();
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
        
        $view=DNView::G();
        $view->setViewWrapper(null, null);
        $view->_Show([], $error_view);
        DNRuntimeState::G()->end();
    }
    
    public function _OnException($ex)
    {
        //TODO tell me why
        $flag=DNExceptionManager::G()->checkAndRunErrorHandlers($ex, true);
        if ($flag) {
            return;
        }
        static::header('', true, 500);
        
        $view=DNView::G();
        $data=[];
        $data['is_developing']=static::Developing();
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
        DNRuntimeState::G()->end();
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
        DNView::G()->_ShowBlock($error_view, $data);
    }
    public function addBeforeShowHandler($handler)
    {
        $this->beforeShowHandlers[]=$handler;
    }
}

trait DNCore_SystemWrapper
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
trait DNCore_Redirect
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

trait DNCore_Helper
{
    public static function ThrowOn($flag, $message, $code=0)
    {
        if (!$flag) {
            return;
        }
        throw new DNException($message, $code);
    }
    // system static
    public static function Platform()
    {
        return static::G()->platform;
    }
    public static function Developing()
    {
        return static::G()->is_dev;
    }
    public static function Import($file)
    {
        return static::G()->_Import($file);
    }
    //// Misc Functions
    public function _Import($file)
    {
        $file=rtrim($file, '.php').'.php';
        require_once($this->path_lib.$file);
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
trait DNCore_Glue
{
    //state
    public static function IsRunning()
    {
        return DNRuntimeState::G()->isRunning();
    }
    // route static
    public static function URL($url=null)
    {
        return DNRoute::G()->_URL($url);
    }
    public static function Parameters()
    {
        return DNRoute::G()->_Parameters();
    }
    // view static
    public static function Show($data=[], $view=null)
    {
        return DNView::G()->_Show($data, $view);
    }
    public static function ShowBlock($view, $data=null)
    {
        return DNView::G()->_ShowBlock($view, $data);
    }
    // config static
    public static function Setting($key)
    {
        return DNConfiger::G()->_Setting($key);
    }
    public static function Config($key, $file_basename='config')
    {
        return DNConfiger::G()->_Config($key, $file_basename);
    }
    public static function LoadConfig($file_basename)
    {
        return DNConfiger::G()->_LoadConfig($file_basename);
    }
    
    /////////////////////////////////
    //autoloader
    public function assignPathNamespace($path, $namespace=null)
    {
        return DNAutoLoader::G()->assignPathNamespace($path, $namespace);
    }
    // route
    public function addRouteHook($hook, $prepend=false, $once=true)
    {
        return DNRoute::G()->addRouteHook($hook, $prepend, $once);
    }
    public function getRouteCallingMethod()
    {
        return DNRoute::G()->getRouteCallingMethod();
    }
    
    //view
    public function setViewWrapper($head_file=null, $foot_file=null)
    {
        return DNView::G()->setViewWrapper($head_file, $foot_file);
    }
    public function assignViewData($key, $value=null)
    {
        return DNView::G()->assignViewData($key, $value);
    }
    //exception manager
    public function assignExceptionHandler($classes, $callback=null)
    {
        return DNExceptionManager::G()->assignExceptionHandler($classes, $callback);
    }
    public function setMultiExceptionHandler(array $classes, $callback)
    {
        return DNExceptionManager::G()->setMultiExceptionHandler($classes, $callback);
    }
    public function setDefaultExceptionHandler($callback)
    {
        return DNExceptionManager::G()->setDefaultExceptionHandler($callback);
    }
}
