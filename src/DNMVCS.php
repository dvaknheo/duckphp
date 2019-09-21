<?php
// MAIN FILE
//dvaknheo@github.com
//OK，Lazy
namespace DNMVCS;

use DNMVCS\Core\App;

use DNMVCS\Ext\StrictCheck;
use DNMVCS\Ext\DBManager;
use DNMVCS\Ext\RouteHookRewrite;
use DNMVCS\Ext\RouteHookRouteMap;
use DNMVCS\Ext\Pager;
use DNMVCS\Ext\Misc;

//use DNMVCS\SwooleHttpd\SwooleExtAppInterface;

class DNMVCS extends App //implements SwooleExtAppInterface
{
    const VERSION = '1.1.2';
    
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
    protected $staticComponentClasses=[
        'DNMVCS\Core\AutoLoader',
        'DNMVCS\Core\ExceptionManager',
        'DNMVCS\Core\Configer',
        'DNMVCS\Core\View',
        'DNMVCS\Core\Route',
    ];
    protected $dynamicComponentClasses=[
        'DNMVCS\Core\RuntimeState',
        'DNMVCS\Core\SuperGlobal',
    ];
    public function onSwooleHttpdInit($SwooleHttpd,$inCoroutine=false)
    {
        $this->options['use_super_global']=true;
        if ($inCoroutine) {
            $this::SG($SwooleHttpd::SG());
            return;
        }
        
        $SwooleHttpd->set_http_exception_handler([static::class,'OnException']);
        $SwooleHttpd->set_http_404_handler([static::class,'On404']);
        
        if ($SwooleHttpd->is_with_http_handler_root()) {
            $this->options['skip_404_handler']=true;
        }
        $this->system_wrapper_replace($SwooleHttpd->system_wrapper_get_providers());
    }


    // @interface SwooleExtAppInterface
    public function getStaticComponentClasses()
    {
        $ext=array_values(array_unique([ static::class,self::class,$this->override_root_class]));
        $ret=$this->staticComponentClasses + $ext;
        return $ret;
    }
    public function getDynamicComponentClasses()
    {
        return $this->dynamicComponentClasses;
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
    public function setDBHandler($db_create_handler, $db_close_handler=null, $db_excption_handler=null)
    {
        return DBManager::G()->setDBHandler($db_create_handler, $db_close_handler, $db_excption_handler);
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
    public static function CheckStrictDB()
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
