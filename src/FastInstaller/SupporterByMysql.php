<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;

class SupporterByMysql extends Supporter
{    
    public function readDsnSetting($options)
    {
        $options = parent::readSetting($options);
        return array_merge(['host' => '127.0.0.1','port' => '3306'],$options);
    }
    public function writeDsnSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);
        
        $dsn = "mysql:host={$options['host']};port={$options['port']};dbname={$options['dbname']};charset=utf8mb4;";
        
        $options['dsn'] = $dsn;
        unset($options['host']);
        unset($options['port']);
        unset($options['dbname']);
        
        return $options;
    }
    //////////////////
    public function getAllTable()
    {
        $tables = [];
        $data = DbManager::Db()->fetchAll('SHOW TABLES');
        foreach ($data as $v) {
            $tables[] = array_values($v)[0];
        }
        return $tables;
    }
    public function getSchemeByTable($table)
    {
        try {
            $record = DbManager::Db()->fetch("SHOW CREATE TABLE `$table`");
        } catch (\PDOException $ex) {
            return '';
        }
        $sql = $record['Create Table'] ?? null;
        $sql = preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=1', $sql);
        return $sql;
    }
    

    public function getInstallDescs()
    {
        $desc = <<<EOT
----
    host: [{host}] 
    port: [{port}]
    dbname: [{dbname}]
    username: [{username}]
    password: [{password}]
EOT;
        return $desc;
    }
}
