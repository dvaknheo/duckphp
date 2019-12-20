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

class App extends Core_App
{
    const VERSION = '1.2.2';
        
    protected $options_ex = [
            'log_file' => '',
            
            'use_super_global' => false,
            'rewrite_map' => [],
            'route_map' => [],
            //'route_map_append' => [],
            
            'ext' => [
                //'DuckPhp\Ext\PluginForSwooleHttpd'=>true,
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
    public function __construct()
    {
        $this->options = array_merge($this->options, $this->options_ex);
        parent::__construct();
        if (get_class($this) === self::class) {
            $this->componentClassMap = [
                'M' => 'Core\Helper\ModelHelper',
                'V' => 'Core\Helper\ViewHelper',
                'C' => 'Core\Helper\ControllerHelper',
                'S' => 'Core\Helper\ServiceHelper',
            ];
        }
    }
    // @override parent
    public function _Pager(object $replacement_object = null)
    {
        return Pager::G($replacement_object);
    }
}
