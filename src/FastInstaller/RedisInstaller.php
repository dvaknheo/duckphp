<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Component\RedisManager;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;

class RedisInstaller extends ComponentBase
{
    public $options = [
        //
    ];
    public function callResetRedis($force = false)
    {
        $ref = RedisManager::_()->getRedisConfigList();
        if (!$force && $ref) {
            echo "redis is configed ,use --force to force\n";
            return false;
        }
        $data = $this->configRedis($ref);
        $this->changeRedis($data);
        return true;
    }
    protected function changeRedis($data)
    {
        $is_local = App::Current()->options['local_redis'] ?? false;
        $app = $is_local ? App::Current() : App::Root();
        
        $options = ExtOptionsLoader::_()->loadExtOptions(true, $app);
        $options['redis_list'] = $data;
        $t = App::Root()->options['installing_data'] ?? null;
        unset(App::Root()->options['installing_data']);
        ExtOptionsLoader::_()->saveExtOptions($options, $app);
        App::Root()->options['installing_data'] = $t;
        
        $options = RedisManager::_()->options;
        $options['redis_list'] = $data;
        RedisManager::_()->reInit($options, $app);
    }
    
    protected function configRedis($ref_database_list = [])
    {
        $ret = [];
        
        $options = [];
        while (true) {
            $j = count($ret);
            echo "Setting Redis[$j]:\n";
            $desc = <<<EOT
----
    host: [{host}] 
    port: [{port}]
    auth: [{auth}]
    select: [{select}]

EOT;
    
            $options = array_merge($ref_database_list[$j] ?? [], $options);
            $options = array_merge(['host' => '127.0.0.1','port' => '6379','select' => 1], $options);
            $options = Console::_()->readLines($options, $desc);
            
            list($flag, $error_string) = $this->checkRedis($options);
            if ($flag) {
                $ret[] = $options;
            }
            if (!$flag) {
                echo "Connect redis error: $error_string \n";
            }
            if (empty($ret)) {
                continue;
            }
            $sure = Console::_()->readLines(['sure' => 'N'], "Setting more redis (Y/N)[{sure}]?");
            if (strtoupper($sure['sure']) === 'Y') {
                continue;
            }
            break;
        }
        return $ret;
    }
    protected function checkRedis($config)
    {
        try {
            $redis = new \Redis();
            $redis->connect($config['host'], (int)$config['port']);
            if (isset($config['auth'])) {
                $redis->auth($config['auth']);
            }
            if (isset($config['select'])) {
                $redis->select((int)$config['select']);
            }
        } catch (\Exception $ex) {
            return [false, $ex->getMessage()];
        }
        return [true,null];
    }
}
