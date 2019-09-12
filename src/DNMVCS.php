<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\ExtendStaticCallTrait;
use DNMVCS\SuperGlobal;
use DNMVCS\Ext\StrictCheck;
use DNMVCS\Ext\DBManager;
use DNMVCS\Ext\RouteHookRewrite;
use DNMVCS\Ext\RouteHookRouteMap;
use DNMVCS\Ext\Pager;
use DNMVCS\Ext\Misc;

use DNMVCS\Core\App;
//use DNMVCS\SwooleHttpd\SwooleExtAppInterface;

class DNMVCS extends App //implements SwooleExtAppInterface
{
    const VERSION = '1.1.2';
    use ExtendStaticCallTrait;
    
    use DNMVCS_Glue;
    use DNMVCS_SystemWrapper;
    
    const DEFAULT_OPTIONS_EX=[
            'path_lib'=>'lib',
            'use_super_global'=>false,
            'rewrite_map'=>[],
            'route_map'=>[],
            'swoole'=>[],
            
            'ext'=>[
                'SwooleHttpd\SwooleExt'=>true,
                'Ext\Misc'=>true,

                'Ext\DBManager'=>[
                    'before_get_db_handler'=>[null,'CheckStrictDB'],
                ],
                'Ext\RouteHookRewrite'=>true,
                'Ext\RouteHookRouteMap'=>true,
                'Ext\StrictCheck'=>true,
                
                'Ext\Lazybones'=>false,
                'Ext\DBReusePoolProxy'=>false,
                'Ext\FacadesAutoLoader'=>false,
                'Ext\RouteHookDirectoryMode'=>false,
                'Ext\RouteHookOneFileMode'=>false,
            ],
            
        ];
    protected $componentClassMap=[
            'M'=>'ModelHelper',
            'V'=>'ViewHelper',
            'C'=>'ControllerHelper',
            'S'=>'ServiceHelper',
    ];
    //// RunMode
    /*
    public static function RunWithoutPathInfo($options=[])
    {
        $default_options=[
            'ext'=>[
                'Ext\RouteHookOneFileMode'=>[
                    'mode_onefile'=>true,
                    'mode_onefile_key_for_action'=>'_',
                ],
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
            'skip_setting_file'=>true,
            'ext'=>[
                'Ext\RouteHookOneFileMode'=>[
                    'mode_onefile'=>true,
                    'mode_onefile_key_for_action'=>'act',
                    'use_function_dispatch'=>true,
                    'use_function_view'=>true,

                    'use_session_auto_start'=>true,
                ],
            ],
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
    */
    protected function onInit()
    {
    }
    protected function onRun()
    {
        if (defined('DNMVCS_SUPER_GLOBAL_REPALACER')) {
            $func=DNMVCS_SUPER_GLOBAL_REPALACER;
            SuperGlobal::G($func());
            $this->options['use_super_global']=true;
        }
        if ($this->options['use_super_global']??false) {
            $this->bindServerData(SuperGlobal::G()->_SERVER);
            ////////////var_dump("AAAAAAAAAAAAAAA",SuperGlobal::G()->_GET);
            return;
        }
    }
    
    public function extendComponents($class,$methods,$components)
    {
        
        $methods=is_array($methods)?$methods:[$methods];
        $components=is_array($components)?$components:explode(',',$components);
        $maps=[];
        foreach($methods as $method){
            $maps[$method]=[$class,$method];
        }
        
        static::AssignStaticMethod($maps);
        
        $a=explode('\\',get_class($this));
        array_pop($a);
        $namespace=implode('\\',$a);
        
        foreach($components as $component){
            $class=$this->componentClassMap[strtoupper($component)]??null;
            if($class===null){
                continue;
            }
            $full_class=trim($namespace."\\".$class,"\\");
            if(!class_exists($full_class)){
                $full_class=trim("DNMVCS\\".$class,"\\");
            }
            if(!class_exists($full_class)){
                continue;
            }
            $full_class::AssignStaticMethod($maps);
        }
    }
    
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdInit($SwooleHttpd)
    {
        $this->options['use_super_global']=true;
        
        $SwooleHttpd->set_http_exception_handler([static::class,'OnException']);        
        $SwooleHttpd->set_http_404_handler([static::class,'On404']);
        
        if ($SwooleHttpd->is_with_http_handler_root()) {
            $this->options['skip_404_handler']=true;
        }
        $this->system_wrapper_replace($SwooleHttpd->system_wrapper_get_providers());
    }
    
    // @override
    public function getStaticComponentClasses()
    {
        return parent::getStaticComponentClasses();
    }
    // @override
    public function getDynamicComponentClasses()
    {
        $ret=parent::getDynamicComponentClasses();
        $ret=array_merge($ret,[SuperGlobal::class]);
        $ret=array_values(array_unique($ret));
        return $ret;
    }
}

trait DNMVCS_Glue
{
    //////////////
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
    /////
    public static function DB($tag=null)
    {
        return DBManager::G()->_DB($tag);
    }
    public static function DB_W()
    {
        return DBManager::G()->_DB_W();
    }
    public static function DB_R()
    {
        return DBManager::G()->_DB_R();
    }
    public static function Pager()
    {
        return Pager::G();
    }
    /////
    public function assignRewrite($key, $value=null)
    {
        return RouteHookRewrite::G()->assignRewrite($key, $value);
    }
    public function getRewrites()
    {
        return RouteHookRewrite::G()->getRewrites();
    }
    public function assignRoute($key, $value=null)
    {
        return RouteHookRouteMap::G()->assignRoute($key, $value);
    }
    public function getRoutes()
    {
        return RouteHookRouteMap::G()->getRoutes();
    }
    /////
    public static function CheckStrictDB($tag)
    {
        //3 = DB,_DB,CheckStrictDB
        return static::G()->checkStrictComponent('DB', 3);
    }
    public function checkStrictComponent($component_name, $trace_level)
    {
        return StrictCheck::G()->checkStrictComponent($component_name, $trace_level+1);
    }
    public function checkStrictService($trace_level=2)
    {
        return StrictCheck::G()->checkStrictService($trace_level+1);
    }
    public function checkStrictModel($trace_level=2)
    {
        return StrictCheck::G()->checkStrictModel($trace_level+1);
    }
    public static function Import($file)
    {
        return Misc::G()->_Import($file);
    }
    public static function RecordsetUrl(&$data, $cols_map=[])
    {
        return Misc::G()->_RecordsetUrl($data, $cols_map);
    }
    
    public static function RecordsetH(&$data, $cols=[])
    {
        return Misc::G()->_RecordsetH($data, $cols);
    }
    /////////////////////

    public function callAPI($class, $method, $input)
    {
        return Misc::G()->callAPI($class, $method, $input);
    }
    
    public static function MapToService($serviceClass, $input)
    {
        return Misc::G()::MapToService($serviceClass, $input);
    }
    //TODO
    public static function explodeService($object, $namespace="MY\\Service\\")
    {
        return Misc::G()::explodeService($object, $namespace);
    }
}
trait DNMVCS_SystemWrapper
{
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
