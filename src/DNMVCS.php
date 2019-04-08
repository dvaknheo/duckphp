<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

class DNMVCS extends DNCore
{
    const VERSION = '1.1.0';
    
    use DNClassExt;
    use DNMVCS_Glue;
    use DNMVCS_Misc;
    use DNMVCS_SystemWrapper;
    use DNMVCS_Instance;
    
    const DEFAULT_OPTIONS=[

            'path'=>null,
            'namespace'=>'MY',
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

    
    protected $has_run_once=false;


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

    protected function initAfterOverride($options)
    {
        $this->initSwoole($options);
        
        parent::initAfterOverride($options);
        
        $this->initDBManager(DNDBManager::G());
        $this->initSystemWrapper();
        $this->initMisc();
        DNLazybones::G()->init($options);
        return $this;
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
        $this->addBeforeShowHandler([$dbm,'closeAllDB']);
    }
    public function initMisc()
    {
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
            static::set_exception_handler([static::class,'OnException']); //install excpetion again;
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
        
        return parent::run();
    }
}
