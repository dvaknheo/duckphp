<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

//dvaknheo@github.com
//OK，Lazy
namespace DuckPhp;

use DuckPhp\Core\App as CoreApp;
use DuckPhp\Ext\DBManager;
use DuckPhp\Ext\Pager;
use DuckPhp\Ext\RouteHookRouteMap;

class App extends CoreApp
{
    const VERSION = '1.2.4';
    
    public function __construct()
    {
        $this->options['log_sql_query'] = false;
        $this->options['log_sql_level'] = 'debug';
        $this->options['db_before_query_handler'] = null;
        $this->options['ext'][DBManager::class] = true;
        $this->options['ext'][RouteHookRouteMap::class] = true;
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
