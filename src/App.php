<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp;

use DuckPhp\Core\App as Core_App;
use DuckPhp\Ext\Pager;

class App extends Core_App
{
    const VERSION = '1.2.3';
    protected $options_ex = [
            
            //'route_map_important' => [],
            //'route_map' => [],
            'db_before_query_handler' => null,
            'log_sql_query' => false,
            'log_sql_level' => 'debug',
            
            'ext' => [
                'DuckPhp\Ext\DBManager' => true,
                'DuckPhp\Ext\RouteHookRouteMap' => true,
                
                // 'DuckPhp\Ext\PluginForSwooleHttpd' => true,
                // 'DuckPhp\Ext\Misc' => true,
                //'DuckPhp\Ext\RouteHookRewrite' => true,
                
                //'DuckPhp\Ext\StrictCheck' => false,
                //'DuckPhp\Ext\RouteHookOneFileMode' => false,
                //'DuckPhp\Ext\RouteHookDirectoryMode' => false,
                
                //'DuckPhp\Ext\RedisManager' => false,
                //'DuckPhp\Ext\RedisSimpleCache' => false,
                //'DuckPhp\Ext\DBReusePoolProxy' => false,
                //'DuckPhp\Ext\FacadesAutoLoader' => false,
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
        $ret = parent::onInit();
        
        if (!empty($this->options['log_sql_query'])) {
            $this->options['db_before_query_handler'] = $this->options['db_before_query_handler'] ?? [static::class, 'OnQuery'];
        }
        return $ret;
    }
    public function _Pager($object = null)
    {
        $pager = Pager::G($object);
        $pager->options['pager_context_class'] = static::class;
        return $pager;
    }
    public static function OnQuery($sql, ...$args)
    {
        return static::G()->_OnQuery($sql, ...$args);
    }
    public function _OnQuery($sql, ...$args)
    {
        static::Logger()->log($this->options['log_sql_level'], '[sql]: ' . $sql, $args);
    }
}
