<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */

//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp;

use DuckPhp\Core\App as Core_App;

use DuckPhp\Ext\StrictCheck;
use DuckPhp\Ext\DBManager;
use DuckPhp\Ext\RouteHookRewrite;
use DuckPhp\Ext\RouteHookRouteMap;
use DuckPhp\Ext\Pager;
use DuckPhp\Ext\Misc;
use DuckPhp\Ext\SimpleLogger;

//use DuckPhp\SwooleHttpd\SwooleExtAppInterface;

class App extends Core_App //implements SwooleExtAppInterface
{
    const VERSION = '1.2.1';
    
    use DuckPhp_Glue;
    
    const DEFAULT_OPTIONS_EX = [

            'path_lib' => 'lib',
            'log_file' => '',
            
            'use_super_global' => false,
            'rewrite_map' => [],
            'route_map' => [],
            
            'ext' => [
                //'DuckPhp\SwooleHttpd\SwooleExt'=>true,
                'DuckPhp\Ext\Misc' => true,
                'DuckPhp\Ext\SimpleLogger' => true,
                'DuckPhp\Ext\DBManager' => true,
                'DuckPhp\Ext\RouteHookRewrite' => true,
                'DuckPhp\Ext\RouteHookRouteMap' => true,
                'DuckPhp\Ext\StrictCheck' => false,
                'DuckPhp\Ext\RouteHookOneFileMode' => false,
                
                'DuckPhp\Ext\RedisManager' => false,
                'DuckPhp\Ext\RedisSimpleCache' => false,
                
                'DuckPhp\Ext\RouteHookDirectoryMode' => false,
                'DuckPhp\Ext\DBReusePoolProxy' => false,
                'DuckPhp\Ext\FacadesAutoLoader' => false,
                'DuckPhp\Ext\Lazybones' => false,
            ],
            
        ];
    public function __construct()
    {
        parent::__construct();
        $this->extendComponents(['Pager' => [static::class,'_Pager'],], ['C']);
    }
    // @interface SwooleExtAppInterface
    public function onSwooleHttpdInit($SwooleHttpd, $InCoroutine = false, ?callable $RunHandler)
    {
        $this->options['use_super_global'] = true;
        if ($InCoroutine) {
            $this::SG($SwooleHttpd::SG());
            return;
        }
        
        $SwooleHttpd->set_http_exception_handler([static::class,'OnException']); // 接管异常处理
        $SwooleHttpd->set_http_404_handler([static::class,'On404']);             // 接管 404 处理。
        
        $flag = $SwooleHttpd->is_with_http_handler_root();                         // 如果还有子文件，做404后处理
        $this->options['skip_404_handler'] = $this->options['skip_404_handler'] ?? false;
        $this->options['skip_404_handler'] = $this->options['skip_404_handler'] || $flag;
        
        $funcs = $SwooleHttpd->system_wrapper_get_providers();
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

trait DuckPhp_Glue
{
    public static function _Pager(object $replacement_object = null)
    {
        return Pager::G($replacement_object);
    }
    /////
    public function assignRewrite($key, $value = null)
    {
        return RouteHookRewrite::G()->assignRewrite($key, $value);
    }
    public function getRewrites()
    {
        return RouteHookRewrite::G()->getRewrites();
    }
    public function assignRoute($key, $value = null)
    {
        return RouteHookRouteMap::G()->assignRoute($key, $value);
    }
    public function getRoutes()
    {
        return RouteHookRouteMap::G()->getRoutes();
    }
    public function checkStrictComponent($component_name, $trace_level, $parent_classes_to_skip = [])
    {
        return StrictCheck::G()->checkStrictComponent($component_name, $trace_level + 1, $parent_classes_to_skip);
    }
    public function checkStrictService($service_class, $trace_level = 2)
    {
        return StrictCheck::G()->checkStrictService($service_class, $trace_level + 1);
    }
    public function checkStrictModel($trace_level = 2)
    {
        return StrictCheck::G()->checkStrictModel($trace_level + 1);
    }
}
