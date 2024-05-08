<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;

class SupporterBySqlite extends Supporter
{
    /////////////////////////////////
    public function readDsnSetting($options)
    {
        $dsn = $options['dsn'] ?? '';
        $options['file'] = substr($dsn, strlen('sqlite:'));
        return $options;
    }
    public function writeDsnSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);
        
        $dsn = "sqlite:{$options['file']}";
        
        $options['dsn'] = $dsn;
        unset($options['file']);
        
        $options['username'] = '';
        $options['password'] = '';
        
        return $options;
    }
    //////////////////
    public function getAllTable()
    {
        $tables = [];
        $data = DbManager::Db()->fetchAll('SELECT tbl_name from sqlite_master where type ="table"');
        foreach ($data as $v) {
            if (substr($v['tbl_name'], 0, strlen('sqlite_')) === 'sqlite_') {
                continue;
            }
            $tables[] = $v['tbl_name'];
        }
        return $tables;
    }
    public function getSchemeByTable($table)
    {
        $sql = '';
        //try {
        $sql = DbManager::Db()->fetchColumn("SELECT sql FROM sqlite_master WHERE tbl_name=? ", $table);
        //} catch (\PDOException $ex) {
        //    return '';
        //}
        $sql = preg_replace('/CREATE TABLE "([^"]+)"/', 'CREATE TABLE `$1`', $sql);
        
        return $sql;
    }
    public function getInstallDesc()
    {
        $desc = <<<EOT
----
    database filename: [{file}] 
EOT;
        return $desc;
    }
}
