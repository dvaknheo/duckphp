<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\App;

class SupporterBySqlite extends Supporter
{
    /////////////////////////////////
    public function getRuntimePath()
    {
        //TODO to helper ,PathOfRuntime
        $path = static::SlashDir(App::Root()->options['path']);
        $path_runtime = static::SlashDir(App::Root()->options['path_runtime']);
        return static::IsAbsPath($path_runtime) ? $path_runtime : $path.$path_runtime;
    }
    
    public function readDsnSetting($options)
    {
        $dsn = $options['dsn'] ?? '';
        $file = substr($dsn, strlen('sqlite:'));
        
        if (!$file) {
            $flag = App::Current()->options['local_database'] ?? false;
            if ($flag) {
                $file = str_replace("\\", '-', App::Current()->options['namespace']) . '.db';
            } else {
                $file = 'database.db';
            }
        }
        $options['file'] = $file;
        return $options;
    }
    public function writeDsnSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);
        $file = $options['file'];
        
        $dsn = "sqlite:$file";
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
        $path = $this->getRuntimePath();
        $desc = <<<EOT
----
    base dir: [$path]
    database filename: [{file}] 
EOT;
        return $desc;
    }
}
