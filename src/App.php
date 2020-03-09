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
            // 'rewrite_map' => [],
            // 'route_map_important' => [],
            // 'route_map' => [],
            'db_before_query_handler' => null,
            'log_sql' => false,
            'use_short_functions' => false,
            
            'ext' => [
                // No Use 'DuckPhp\Ext\PluginForSwooleHttpd' => true,
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
                'DuckPhp\Ext\Pager' => false,
            ],
            
        ];
    public function __construct()
    {
        $this->options = array_merge($this->options, $this->options_ex);
        /* no use
                if (PHP_SAPI === 'cli' && extension_loaded('swoole')) {
                    //$t = ['DuckPhp\Ext\PluginForSwooleHttpd' => true];
                    //$this->options['ext'] = array_merge($t, $this->options);
                }
        */
        $this->options['db_before_query_handler'] = [static::class, 'OnQuery'];
        parent::__construct();
    }
    protected function onInit()
    {
        $ret=parent::onInit();
        
        if (!empty($this->options['log_sql'])) {
            $this->options['db_before_query_handler'] = $this->options['db_before_query_handler'] ?? [static::class, 'OnQuery'];
        }
        if($this->options['use_short_functions']){
            require_once __DIR__.'/Ext/ShortFunctions.php';
        }
        return $ret;
    }
    public function _Pager($object = null)
    {
        return Pager::G($object);
    }
    public static function OnQuery($sql, ...$args)
    {
        return static::G()->_OnQuery($sql, ...$args);
    }
    public function _OnQuery($sql, ...$args)
    {
        static::Logger()->info('[sql]: ' . $sql, $args);
    }
}
