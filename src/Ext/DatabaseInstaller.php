<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Component\ExtOptionsLoader;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Console;

class DatabaseInstaller extends ComponentBase
{
    public $options = [
        'database_input_driver' => 'mysql',
    ];
    public function callResetDatabase($force = false)
    {
        $ref = DbManager::_()->getDatabaseConfigList();
        if (!$force && $ref) {
            echo "database configed ,use --force to force\n";
            return false;
        }
        $data = $this->configDatabase($ref);
        $this->changeDatabase($data);
        return true;
    }
    protected function changeDatabase($data)
    {
        $options = ExtOptionsLoader::_()->loadExtOptions(true, App::Root());
        $options['database_list'] = $data;
        ExtOptionsLoader::_()->saveExtOptions($options, App::Root());
        
        $options = DbManager::_()->options;
        $options['database_list'] = $data;
        DbManager::_()->reInit($options, App::Root());
    }
    
    protected function configDatabase($ref_database_list = [])
    {
        $ret = [];
        
        $options = [];
        while (true) {
            $j = count($ret);
            echo "Setting MySQL database[$j]:\n";
            $desc = <<<EOT
----
    host: [{host}] 
    port: [{port}]
    dbname: [{dbname}]
    username: [{username}]
    password: [{password}]
EOT;
    
            $options = array_merge($ref_database_list[$j] ?? [], $options);
            
            $options = $this->makeFromDsn($options);
            
            $options = array_merge(['host' => '127.0.0.1','port' => '3306',], $options);
            $options = Console::_()->readLines($options, $desc);
            list($flag, $error_string) = $this->checkDb($options);
            if ($flag) {
                $options['dsn'] = $this->dsnFromSetting($options);
                unset($options['host']);
                unset($options['port']);
                unset($options['dbname']);
                
                $ret[] = $options;
            }
            if (!$flag) {
                echo "Connect database error: $error_string \n";
            }
            if (empty($ret)) {
                continue;
            }
            $sure = Console::_()->readLines(['sure' => 'N'], "Setting More Database(Y/N)[{sure}]?");
            if (strtoupper($sure['sure']) === 'Y') {
                continue;
            }
            break;
        }
        return $ret;
    }
    private function makeFromDsn($options)
    {
        if (!isset($options['dsn'])) {
            return $options;
        }
        $dsn = $options['dsn'];
        $data = substr($dsn, strlen($this->options['driver'].':'));
        $a = explode(';', trim($data, ';'));
        
        $t = array_map(function ($v) {
            return explode("=", $v);
        }, $a);
        $new = array_column($t, 1, 0);
        $new = array_map('trim', $new);
        $new = array_map('stripslashes', $new);
        $options = array_merge($options, $new);
        return $options;
    }
    private function dsnFromSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);
        
        $dsn = "mysql:host={$options['host']};port={$options['port']};dbname={$options['dbname']};charset=utf8mb4;";
        return $dsn;
    }
    protected function checkDb($database)
    {
        $dsn = $this->dsnFromSetting($database);
        try {
            $db = new \DuckPhp\Db\Db();
            $db->init([
                'dsn' => $dsn,
                'username' => $database['username'],
                'password' => $database['password'],
            ]);
        } catch (\Exception $ex) {
            return [false, $ex->getMessage()];
        }
        return [true,null];
    }
}
