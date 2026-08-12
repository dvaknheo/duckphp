<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;

class SqlDumperSupporterBySqlite extends SqlDumperSupporter
{
    //////////////////
    public function getAllTable(): array
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
    public function getSchemeByTable(string $table): string
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
}
