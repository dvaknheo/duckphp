<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;

//@codeCoverageIgnoreStart
class SupporterByMysql extends Supporter
{
    //////////////////
    public function getAllTable(): array
    {
        $tables = [];
        $data = DbManager::Db()->fetchAll('SHOW TABLES');
        foreach ($data as $v) {
            $tables[] = array_values($v)[0];
        }
        return $tables;
    }
    public function getSchemeByTable(string $table): string
    {
        //try {
        $record = DbManager::Db()->fetch("SHOW CREATE TABLE `$table`");
        //} catch (\PDOException $ex) {
        //    return '';
        //}
        $sql = $record['Create Table'] ?? null;
        $sql = preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=1', $sql);
        return $sql;
    }
}//@codeCoverageIgnoreEnd
