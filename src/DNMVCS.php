<?php
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use SwooleHttpd\SwooleHttpd;

class DNMVCS extends DNCore
{
    const VERSION = '1.1.0';
    
    use DNClassExt;
    use DNMVCS_Glue;
    use DNMVCS_Misc;
    use DNMVCS_SystemWrapper;
    use DNMVCS_Instance;
    
    const DEFAULT_OPTIONS_EX=[
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
    
    public static function _EmptyFunction()
    {
        return;
    }
    protected function initSwoole($options)
    {
        if (!empty($options['swoole'])) {
            DNSwooleExt::G()->init($options['swoole'], $this);
        }
    }

    protected function initAfterOverride($options)
    {
        $this->initSwoole($options);
        
        parent::initAfterOverride($options);
        
        $this->initDBManager(DNDBManager::G());
        $this->initSystemWrapper();
        
        if (!empty($this->options['ext'])) {
            DNMVCSExt::G()->init($this);
        }
        DNLazybones::G()->init($options);
        return $this;
    }
    public function initDBManager($dbm)
    {
        $configer=DNConfiger::G();
        $db_setting_key=$this->options['db_setting_key']??'database_list';
        $database_list=$configer->_Setting($db_setting_key);
        $database_list=$database_list??[];
        $this->options['database_list']=array_merge($this->options['database_list'], $database_list);
        
        $dbm->init($this->options, $this);
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
       
        if ($this->options['use_super_global']??false || defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
                $func=DNMVCS_SUPER_GLOBAL_REPALACER;
                DNSuperGlobal::G($func());
            }
            $this->dynamicClasses[]=DNSuperGlobal::class;
            DNRoute::G()->bindServerData(DNSuperGlobal::G()->_SERVER);
        }
        
        return parent::run();
    }
    //// RunMode
    public static function RunWithoutPathInfo($options=[])
    {
        $default_options=[
            'ext'=>[
                'mode_onefile'=>true,
                'mode_onefile_key_for_action'=>'_r',
            ],
        ];
        $options=array_replace_recursive($default_options, $options);
        return static::G()->init($options)->run();
    }
    public static function RunOneFileMode($options=[], $init_function=null)
    {
        $path=realpath(getcwd().'/');
        $default_options=[
            'path'=>$path,
            'setting_file_basename'=>'',
            'base_class'=>'',
            'ext'=>[
                'mode_onefile'=>true,
                'mode_onefile_key_for_action'=>'act',
                
                'use_function_dispatch'=>true,
                'use_function_view'=>true,
                
                'use_session_auto_start'=>true,
            ]
        ];
        $options=array_replace_recursive($default_options, $options);
        static::G()->init($options);
        if ($init_function) {
            ($init_function)();
        }
        return static::G()->run();
    }
    public static function RunAsServer($dn_options, $server=null)
    {
        $dn_options['swoole']['swoole_server']=$server;
        return static::G()->init($dn_options)->run();
    }
}
