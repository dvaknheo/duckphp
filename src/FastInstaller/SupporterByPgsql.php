<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\FastInstaller;

use DuckPhp\Component\DbManager;

//@codeCoverageIgnoreStart
class SupporterByPgsql extends Supporter
{
    public function readDsnSetting($options)
    {
        $options = parent::readDsnSetting($options);
        return array_merge(['host' => '127.0.0.1','port' => '5432'], $options);
    }
    public function writeDsnSetting($options)
    {
        $options = array_map('trim', $options);
        $options = array_map('addslashes', $options);

        $dsn = "pgsql:host={$options['host']};port={$options['port']};dbname={$options['dbname']};";

        $options['dsn'] = $dsn;
        unset($options['host']);
        unset($options['port']);
        unset($options['dbname']);

        return $options;
    }
    //////////////////
    public function getAllTable(): array
    {
        $tables = [];
        $data = DbManager::Db()->fetchAll("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        foreach ($data as $v) {
            $tables[] = $v['tablename'];
        }
        return $tables;
    }
    public function getSchemeByTable(string $table): string
    {
        $table = str_replace('"', '""', $table);
        $db = DbManager::Db();
        $columns = $db->fetchAll(
            "SELECT column_name, data_type, is_nullable, column_default, character_maximum_length
             FROM information_schema.columns
             WHERE table_name = ? AND table_schema = 'public'
             ORDER BY ordinal_position",
            $table
        );
        $pk_rows = $db->fetchAll(
            "SELECT kcu.column_name
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON tc.constraint_name = kcu.constraint_name
              AND tc.table_schema = kcu.table_schema
             WHERE tc.table_name = ? AND tc.constraint_type = 'PRIMARY KEY'
             ORDER BY kcu.ordinal_position",
            $table
        );
        $pk_cols = array_column($pk_rows, 'column_name');

        $defs = [];
        foreach ($columns as $col) {
            $type = $col['data_type'];
            if ($type === 'character varying' && $col['character_maximum_length']) {
                $type .= '(' . $col['character_maximum_length'] . ')';
            }
            $def = '"' . $col['column_name'] . '" ' . $type;
            if ($col['is_nullable'] === 'NO') {
                $def .= ' NOT NULL';
            }
            if ($col['column_default'] !== null) {
                $def .= ' DEFAULT ' . $col['column_default'];
            }
            $defs[] = $def;
        }
        if ($pk_cols) {
            $defs[] = 'PRIMARY KEY ("' . implode('","', $pk_cols) . '")';
        }
        return 'CREATE TABLE "' . $table . '" (' . implode(', ', $defs) . ')';
    }


    public function getInstallDesc(): string
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
}//@codeCoverageIgnoreEnd
