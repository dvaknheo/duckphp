<?php declare(strict_types=1);
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
use DNMVCS\Ext\SimpleLogger;

//use DNMVCS\SwooleHttpd\SwooleExtAppInterface;

class DNMVCS extends App //implements SwooleExtAppInterface
{
    const VERSION = '1.1.5';
    
    use DNMVCS_Glue;
    
    const DEFAULT_OPTIONS_EX=[
            'path_lib'=>'lib',
            'log_file'=>'',
            
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
                'DNMVCS\Ext\SimpleLogger'=>true,
                'DNMVCS\Ext\RouteHookOneFileMode'=>false,
                
                'DNMVCS\Ext\RedisManager'=>false,
                'DNMVCS\Ext\RedisSimpleCache'=>false,
                
                'DNMVCS\Ext\RouteHookDirectoryMode'=>false,
                'DNMVCS\Ext\DBReusePoolProxy'=>false,
                'DNMVCS\Ext\FacadesAutoLoader'=>false,
                'DNMVCS\Ext\Lazybones'=>false,
                
            ],
            
        ];
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdInit($SwooleHttpd, $InCoroutine=false, ?callable $RunHandler)
    {
        $this->options['use_super_global']=true;
        if ($InCoroutine) {
            $this::SG($SwooleHttpd::SG());
            return;
        }
        
        $SwooleHttpd->set_http_exception_handler([static::class,'OnException']); // 接管异常处理
        $SwooleHttpd->set_http_404_handler([static::class,'On404']);             // 接管 404 处理。
        
        $flag=$SwooleHttpd->is_with_http_handler_root();                         // 如果还有子文件，做404后处理
        $this->options['skip_404_handler'] = $this->options['skip_404_handler']??false;
        $this->options['skip_404_handler'] = $this->options['skip_404_handler'] || $flag;
        
        $funcs=$SwooleHttpd->system_wrapper_get_providers();
        $this->system_wrapper_replace($funcs);                                   // 替换默认的可用的系统函数。
        
        $this->addBeforeRunHandler($RunHandler);                                 // TODO 这里能否不要
    }
    // @interface SwooleExtAppInterface
    public function getStaticComponentClasses()
    {
        return parent::getStaticComponentClasses();
    }
    // @interface SwooleExtAppInterface
    public function getDynamicComponentClasses()
    {
        return parent::getDynamicComponentClasses();
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
    public function setDBHandler($db_create_handler, $db_close_handler=null, $db_exception_handler=null)
    {
        return DBManager::G()->setDBHandler($db_create_handler, $db_close_handler, $db_exception_handler);
    }
    public static function Pager(?object $replacement_object=null)
    {
        return Pager::G($replacement_object);
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
        return static::G()->checkStrictComponent('DB', 4, ['DNMVCS\Core\Helper\ModelHelper']);
    }
    public function checkStrictComponent($component_name, $trace_level, $parent_classes_to_skip=[])
    {
        return StrictCheck::G()->checkStrictComponent($component_name, $trace_level+1, $parent_classes_to_skip);
    }
    public function checkStrictService($service_class, $trace_level=2)
    {
        return StrictCheck::G()->checkStrictService($service_class, $trace_level+1);
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
    public static function DI($name, $object=null)
    {
        return Misc::G()->_DI($name, $object);
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
    public static function explodeService($object, $namespace="MY\\Service\\")
    {
        return Misc::G()::explodeService($object, $namespace);
    }
}
