<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\DB;

interface DBInterface
{
    public function close();
    public function PDO($object = null);
    public function quote($string);
    public function fetchAll($sql, ...$args);
    public function fetch($sql, ...$args);
    public function fetchColumn($sql, ...$args);
    public function execute($sql, ...$args);
    public function rowCount();
    public function lastInsertId();
}
