<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */

//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp;

use DuckPhp\Core\App as Core_App;

use DuckPhp\Ext\Pager;

//use SwooleHttpd\SwooleExtAppInterface;

class App extends Core_App //implements SwooleExtAppInterface
{
    const VERSION = '1.2.2';
    
    use DuckPhp_SwooleExt;
    
    protected $options_ex = [
            'log_file' => '',
            
            'use_super_global' => false,
            'rewrite_map' => [],
            'route_map' => [],
            'route_map_append' => [],
            
            'ext' => [
                //'DuckPhp\SwooleHttpd\SwooleExt'=>true,
                'DuckPhp\Ext\Misc' => true,
                'DuckPhp\Ext\SimpleLogger' => true,
                'DuckPhp\Ext\DBManager' => true,
                'DuckPhp\Ext\RouteHookRewrite' => true,
                'DuckPhp\Ext\RouteHookRouteMap' => true,
                
                'DuckPhp\Ext\StrictCheck' => false,
                'DuckPhp\Ext\RouteHookOneFileMode' => false,
                'DuckPhp\Ext\RouteHookDirectoryMode' => false,
                
                'DuckPhp\Ext\RedisManager' => false,
                'DuckPhp\Ext\RedisSimpleCache' => false,
                'DuckPhp\Ext\DBReusePoolProxy' => false,
                'DuckPhp\Ext\FacadesAutoLoader' => false,
                'DuckPhp\Ext\Lazybones' => false,
            ],
            
        ];
    protected $extDynamicComponentClasses = []; // for swoole_ext
    public function __construct()
    {
        $this->options = array_merge($this->options, $this->options_ex);
        parent::__construct();
        if(get_class($this)===self::class){
            $this->componentClassMap = [
                'M' => 'Core\Helper\ModelHelper',
                'V' => 'Core\Helper\ViewHelper',
                'C' => 'Core\Helper\ControllerHelper',
                'S' => 'Core\Helper\ServiceHelper',
            ];
        }
        var_dump(static::class,$this->componentClassMap);
        $this->extendComponents(['Pager' => [static::class,'_Pager'],], ['C']);
    }
    public static function _Pager(object $replacement_object = null)
    {
        return Pager::G($replacement_object);
    }

}
trait DuckPhp_SwooleExt
{
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
        $ret = [
            self::class,
            'DuckPhp\Core\App',
            'DuckPhp\Core\AutoLoader',
            'DuckPhp\Core\ExceptionManager',
            'DuckPhp\Core\Configer',
            'DuckPhp\Core\Route',
        ];
        if (!in_array(static::class, $ret)) {
            $ret[] = static::class; // @codeCoverageIgnore
        }
        return $ret;
    }
    // @interface SwooleExtAppInterface
    public function getDynamicComponentClasses()
    {
        $ret = [
            'DuckPhp\Core\RuntimeState',
            'DuckPhp\Core\SuperGlobal',
            'DuckPhp\Core\View',
        ];
        $ret = array_merge($ret, $this->extDynamicComponentClasses);
        return $ret;
    }
    public function addDynamicComponentClass($class)
    {
        $this->extDynamicComponentClasses[] = $class;
    }
    public function deleteDynamicComponentClass($class)
    {
        array_filter($this->extDynamicComponentClasses, function ($v) use ($class) {
            return $v !== $class?true:false; // @codeCoverageIgnore
        });
    }
    /*
    protected function cleanClass($input_class)
    {
        $current_class = get_class($input_class::G());
        $input_class::G(new $input_class());
        if ($current_class != $input_class) {
            $this->cleanClass($current_class); // @codeCoverageIgnore
        }
    }
    */
}
