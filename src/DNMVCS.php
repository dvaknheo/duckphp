<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

class DNMVCS
{
    const VERSION = '1.1.0';
    
    use DNSingleton;
    
    use DNMVCS_Glue;
    use DNMVCS_Handler;
    use DNMVCS_Misc;
    use DNMVCS_SystemWrapper;
    use DNMVCS_RunMode;
    use DNMVCS_Instance;
    use DNClassExt;
    
    const DEFAULT_OPTIONS=[

            'path'=>null,
            'namespace'=>'MY',
            'path_namespace'=>'app',
            
            'skip_system_autoload'=>false,
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
            'use_super_global'=>false,
            
            'all_config'=>[],
            'setting'=>[],
            'setting_file_basename'=>'setting',
            
            'error_404'=>'_sys/error-404',
            'error_500'=>'_sys/error-500',
            'error_exception'=>'_sys/error-exception',
            'error_debug'=>'_sys/error-debug',
            
            'use_db'=>true,
            'db_create_handler'=>'',
            'db_close_handler'=>'',
            'db_setting_key'=>'database_list',
            'database_list'=>[],
            
            'rewrite_map'=>[],
            'route_map'=>[],
            
            'ext'=>[],
            'swoole'=>[],
        ];
    public $options=[];
    
    public $isDev=false;
    public $platform='';
    
    protected $path=null;
    protected $path_lib=null;
    
    protected $has_run_once=false;
    
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
        $options['skip_system_autoload']=class_exists('Composer\Autoload\ClassLoader')?true:false;
        return $options;
    }
    protected function initOptions($options=[])
    {
        $options=array_replace_recursive(DNRoute::DEFAULT_OPTIONS, static::DEFAULT_OPTIONS, $options);
        
        $this->options=$options;
        
        $this->path=$this->options['path'];
        $this->path_lib=$this->path.rtrim($this->options['path_lib'], '/').'/';
        
        $this->isDev=$this->options['is_dev'];
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
    protected function initSwoole($options)
    {
        if (empty($options['swoole'])) {
            return;
        }
        static::ThrowOn(!class_exists(SwooleHttpd::class), "DNMVCS: You Need SwooleHttpd");
        DNSwooleExt::Server(SwooleHttpd::G());
        DNSwooleExt::G()->onAppBoot(self::class, $options['swoole']);
        $this->toggleStop404Handler();
    }
    public $skip_override=false;
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
        $this->initSwoole($options);
        
        $this->initOptions($options);
        $this->initExceptionManager(DNExceptionManager::G());
        $this->initConfiger(DNConfiger::G());
        $this->initView(DNView::G());
        $this->initRoute(DNRoute::G());
        
        $this->initDBManager(DNDBManager::G());
        $this->initSystemWrapper();
        $this->initMisc();
        DNLazybones::G()->init($options);
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
        
        $this->isDev=DNConfiger::G()->_Setting('is_dev')??$this->isDev;
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
    public function initDBManager($dbm)
    {
        if (!$this->options['use_db']) {
            return;
        }
        $configer=DNConfiger::G();
        $db_setting_key=$this->options['db_setting_key']??'database_list';
        $database_list=$configer->_Setting($db_setting_key);
        $database_list=$database_list??[];
        $database_list=array_merge($this->options['database_list'], $database_list);
        
        if (empty($database_list)) {
            return;
        }
        
        $dbm->init($database_list);
        
        $db_create_handler=$this->options['db_create_handler']?:[DB::class,'CreateDBInstance'];
        $db_close_handler=$this->options['db_close_handler']?:[DB::class,'CloseDBInstance'];
        $dbm->setDBHandler($db_create_handler, $db_close_handler);
        $this->beforeShowHandlers[]=[$dbm,'closeAllDB'];
    }
    public function initMisc()
    {
        if ($this->options['enable_cache_classes_in_cli'] && PHP_SAPI==='cli') {
            DNAutoLoader::G()->cacheClasses();
        }
        
        if (!empty($this->options['ext'])) {
            DNMVCSExt::G()->init($this);
        }
    }
    protected function initSystemWrapper()
    {
        if (!defined('DNMVCS_SYSTEM_WRAPPER_INSTALLER')) {
            return;
        }
        $callback=DNMVCS_SYSTEM_WRAPPER_INSTALLER;
        $funcs=($callback)();
        $this->system_wrapper_replace($funcs);
        
        if (isset($funcs['set_exception_handler'])) {
            static::set_exception_handler([static::class,'OnException']); //install oexcpetion again;
        }
    }
    protected function runOnce()
    {
        if ($this->options['rewrite_map'] || $this->options['route_map']) {
            DNMVCSExt::G()->dealMapAndRewrite($this->options['rewrite_map'], $this->options['route_map']);
        }
        if (!empty($this->options['swoole'])) {
            DNSwooleExt::G()->onAppBeforeRun();
        }
    }
    public function run()
    {
        if (!$this->has_run_once) {
            $this->has_run_once=true;
            $this->runOnce();
        }
        
        if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            $func=DNMVCS_SUPER_GLOBAL_REPALACER;
            DNSuperGlobal::G($func());
        }
        if ($this->options['use_super_global']??false || defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            $this->dynamicClasses[]=DNSuperGlobal::class;
            DNRoute::G()->bindServerData(DNSuperGlobal::G()->_SERVER);
        }
        
        $class=get_class(DNRuntimeState::G());  //ReCreateInstance;
        DNRuntimeState::G(new $class)->begin();
        
        $ret=DNRoute::G()->run();
        DNRuntimeState::G()->end();
        return $ret;
    }
}
