<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Component\RedisManager;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;

class DatabaseInstaller extends ComponentBase
{
    public $options = [
        //
    ];
    public function callResetDatabase($force = false)
    {
        $ref = RedisManager::_()->getRedisConfigList();
        if (!$force && $ref) {
            echo "redis_ ist configed ,use --force to force\n";
            return false;
        }
        $data = $this->configDatabase($ref);
        $this->changeDatabase($data);
        return true;
    }
    protected function changeDatabase($data)
    {
        $options = ExtOptionsLoader::_()->loadExtOptions(true, App::Root());
        $options['redis_list'] = $data;
        ExtOptionsLoader::_()->saveExtOptions($options, App::Root());
        
        $options = DbManager::_()->options;
        $options['redis_list'] = $data;
        DbManager::_()->reInit($options, App::Root());
    }
    
    protected function configDatabase($ref_database_list = [])
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
    dbname: [{dbname}]
    auth: [{auth}]
    select: [{select}]

EOT;
    
            $options = array_merge($ref_database_list[$j] ?? [], $options);
            $options = array_merge(['host' => '127.0.0.1','port' => '6379',], $options);
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
    protected function checkRedis($settings)
    {
        try {
            var_dump("???");
        } catch (\Exception $ex) {
            return [false, $ex->getMessage()];
        }
        return [true,null];
    }
}
