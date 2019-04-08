<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

class DNCore
{
    const VERSION = '1.1.0';
    
    use DNSingleton;
    
    use DNCore_Glue;
    use DNCore_Handler;
    
    const DEFAULT_OPTIONS=[
            'path'=>null,
            'namespace'=>'MyProject',
            'path_namespace'=>'app',
            
            'skip_system_autoload'=>true,
            'skip_app_autoload'=>false,
            
            ////////
            
            'namespace_controller'=>'Controller',
            
            'base_controller_class'=>null,
            'enable_paramters'=>false,
            'disable_default_class_outside'=>false,
            'default_method_for_miss'=>null,
            
            'enable_post_prefix'=>true,
            'prefix_post'=>'do_',
            ////////
            
            'base_class'=>'Base\App',
            'path_view'=>'view',
            'path_config'=>'config',
            'path_lib'=>'lib',
            'is_dev'=>false,
            'platform'=>'',
            
            'skip_view_notice_error'=>true,
            'enable_cache_classes_in_cli'=>true,
            
            'all_config'=>[],
            'setting'=>[],
            'setting_file_basename'=>'setting',
            
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
        ];
    public $options=[];
    
    public $is_dev=false;
    public $platform='';
    
    protected $path=null;
    protected $path_lib=null;
    public $skip_override=false;
    
    public static function RunQuickly(array $options=[], callable $after_init=null)
    {
        if (!$after_init) {
            return static::G()->init($options)->run();
        }
        static::G()->init($options);
        ($after_init)();
        static::G()->run();
    }
    public static function ThrowOn($flag, $message, $code=0)
    {
        if (!$flag) {
            return;
        }
        throw new DNException($message, $code);
    }
    
    protected function adjustOptions($options=[])
    {
        if (!isset($options['path']) || !$options['path']) {
            $path=realpath(getcwd().'/../');
            $options['path']=$path;
        }
        $options['path']=rtrim($options['path'], '/').'/';
        $options['skip_system_autoload']=class_exists('Composer\Autoload\ClassLoader')?true:false;
        return $options;
    }
    protected function initOptions($options=[])
    {
        $options=array_replace_recursive(DNRoute::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS, $options);
        
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
        $base_class=isset($options['base_class'])?$options['base_class']:self::DEFAULT_OPTIONS['base_class'];
        $namespace=isset($options['namespace'])?$options['namespace']:self::DEFAULT_OPTIONS['namespace'];
        
        if (substr($base_class, 0, 1)!=='\\') {
            $base_class=$namespace.'\\'.$base_class;
        }
        $base_class=ltrim($base_class, '\\');
        
        if (!$base_class || !class_exists($base_class)) {
            return null;
        }
        if (static::class===$base_class) {
            return null;
        }
        return static::G($base_class::G());
    }

    //@override me
    public function init($options=[])
    {
        $options=$this->adjustOptions($options);
        DNAutoLoader::G()->init($options)->run();
        
        $object=$this->checkOverride($options);
        if ($object) {
            $object->skip_override=true;
            return $object->init($options);
        }
        return $this->initAfterOverride($options);
    }
    protected function initAfterOverride($options)
    {
        $this->initOptions($options);
        $this->initExceptionManager(DNExceptionManager::G());
        $this->initConfiger(DNConfiger::G());
        $this->initView(DNView::G());
        $this->initRoute(DNRoute::G());
        
        return $this;
    }
    public function initExceptionManager($exception_manager)
    {
        $exception_manager->init([static::class,'OnException'], [static::class,'OnDevErrorHandler'], [static::class,'set_exception_handler']);
    }
    public function initConfiger($configer)
    {
        $path=$this->path.rtrim($this->options['path_config'], '/').'/';
        $configer->init($path, $this->options);
        
        $this->is_dev=DNConfiger::G()->_Setting('is_dev')??$this->is_dev;
        $this->platform=DNConfiger::G()->_Setting('platform')??$this->platform;
    }
    public function initView($view)
    {
        $path_view=$this->path.rtrim($this->options['path_view'], '/').'/';
        $view->init($path_view);
        $view->setBeforeShowHandler([static::class,'OnBeforeShow']);
    }
    public function initRoute(DNRoute $route)
    {
        $route->init($this->options);
        $route->set404([static::class,'On404']);
    }

    public function initMisc()
    {
        if ($this->options['enable_cache_classes_in_cli'] && PHP_SAPI==='cli') {
            DNAutoLoader::G()->cacheClasses();
        }
    }
    public function run()
    {
        $class=get_class(DNRuntimeState::G());  //ReCreateInstance;
        DNRuntimeState::G(new $class)->begin();
        
        $ret=DNRoute::G()->run();
        DNRuntimeState::G()->end();
        return $ret;
    }
    //// Misc Functions
    public function _Import($file)
    {
        $file=rtrim($file, '.php').'.php';
        require_once($this->path_lib.$file);
    }
}
