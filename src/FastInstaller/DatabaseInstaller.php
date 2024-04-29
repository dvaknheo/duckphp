<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;
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
        $ref = DbManager::_()->getDatabaseConfigList();
        
        $data = $this->configDatabase($ref);
        $this->changeDatabase($data);
        return true;
    }
    protected function changeDatabase($data)
    {
        $is_local = (App::Current()->options['local_db'] ?? false) || App::Root()->options['database_driver'] != App::Current()->options['database_driver'];
        
        $app = $is_local ? App::Current() : App::Root();
        
        $options = ExtOptionsLoader::_()->loadExtOptions(true, $app);
        $options['database_list'] = $data;
        $t = App::Root()->options['installing_data'] ?? null;
        unset(App::Root()->options['installing_data']);
        ExtOptionsLoader::_()->saveExtOptions($options, $app);
        App::Root()->options['installing_data'] = $t;
        
        $options = DbManager::_()->options;
        $options['database_list'] = $data;
        DbManager::_()->reInit($options, $app);
    }
    
    protected function configDatabase($ref_database_list = [])
    {
        $driver = App::Current()->options['database_driver']??'';
        $supporter = Supporter::_()->fromDriver(App::Current()->options['database_driver']??'');
        $ret = [];
        
        $options = [];
        while (true) {
            $j = count($ret);
            echo "Setting $driver database[$j]:\n";
            $desc = Supporter::Current()->getInstallDesc();
            $options = array_merge($ref_database_list[$j] ?? [], $options);
            
            $options = Supporter::Current()->readDsnSetting($options);
            
            /////////////////////////////////////////
            $options = Console::_()->readLines($options, $desc);
            list($flag, $error_string) = $this->checkDb($options);
            if ($flag) {
                $options = Supporter::Current()->writeDsnSetting($options);
                $ret[] = $options;
            }
            if (!$flag) {
                echo "Connect database error: $error_string \n";
            }
            if (empty($ret)) {
                continue;
            }
            $sure = Console::_()->readLines(['sure' => 'N'], "Setting more database(Y/N)[{sure}]?");
            if (strtoupper($sure['sure']) === 'Y') {
                continue;
            }
            break;
        }
        return $ret;
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
