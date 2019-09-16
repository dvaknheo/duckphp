<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\ExtendStaticCallTrait;
use DNMVCS\Core\SuperGlobal;
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
    
    const DEFAULT_OPTIONS_EX=[
            'path_lib'=>'lib',
            'use_super_global'=>false,
            'rewrite_map'=>[],
            'route_map'=>[],
            'swoole'=>[],
            
            'key_for_action'=>'',
            'key_for_module'=>'',
            
            'ext'=>[
                'DNMVCS\SwooleHttpd\SwooleExt'=>true,
                'DNMVCS\Ext\Misc'=>true,

                'DNMVCS\Ext\DBManager'=>[
                    'before_get_db_handler'=>[null,'CheckStrictDB'],
                ],
                'DNMVCS\Ext\RouteHookRewrite'=>true,
                'DNMVCS\Ext\RouteHookRouteMap'=>true,
                'DNMVCS\Ext\StrictCheck'=>true,
                
                'DNMVCS\Ext\Lazybones'=>false,
                'DNMVCS\Ext\DBReusePoolProxy'=>false,
                'DNMVCS\Ext\FacadesAutoLoader'=>false,
                'DNMVCS\Ext\RouteHookDirectoryMode'=>false,
                'DNMVCS\Ext\RouteHookOneFileMode'=>true,
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
///
    }
    public static function RunOneFileMode($options=[], $init_function=null)
    {
       ///
    }
    public static function RunAsServer($dn_options, $server=null)
    {
        $dn_options['swoole']['swoole_server']=$server;
        return static::G()->init($dn_options)->run();
    }
    */
    // @provide output.
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
    
    // @override interface SwooleExtAppInterface
    public function getStaticComponentClasses()
    {
        return parent::getStaticComponentClasses();
    }
    // @override interface SwooleExtAppInterface
    public function getDynamicComponentClasses()
    {
        $ret=parent::getDynamicComponentClasses();
        $ret=array_merge($ret,[SuperGlobal::class]);
        $ret=array_values(array_unique($ret));
        return $ret;
    }
    // @override
    public function _DumpTrace()
    {
        return parent::_DumpTrace();
    }
    public function _Dump(...$args)
    {
        return _Dump(...$args);
    }
}

trait DNMVCS_Glue
{
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