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
    public function readSetting($options)
    {
        return parent::readSetting($options);
    }
    public function writeSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);
        
        $dsn = "sqlite:host={$options['host']};port={$options['port']};dbname={$options['dbname']};charset=utf8mb4;";
        
        $options['dsn'] = $dsn;
        unset($options['host']);
        unset($options['port']);
        unset($options['dbname']);
        
        return $options;
    }
    //////////////////
    public function getAllTable()
    {
        $data = DbManager::Db()->fetchAll('SELECT tbl_name from sqlite_master where type ="table"');
        foreach ($data as $v) {
            if(substr($v['tbl_name'],0,strlen('sqlite_')) === 'sqlite_'){ continue;}
            $tables[] = $v['tbl_name'];
        }
        return $tables;
    }
    public function getSchemeByTable($table)
    {
        $sql = '';
        try {
            $sql = DbManager::Db()->fetchColumn("select sql from sqlite_master where tbl_name=? ",$table);
        } catch (\PDOException $ex) {
            return '';
        }
        $sql =preg_replace('/CREATE TABLE "([^"]+)"/','CREATE TABLE `$1`',$sql);
        
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
