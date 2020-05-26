<?php
namespace tests\DuckPhp\DB;

use DuckPhp\DB\DBInterface;

class DBInterfaceTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBInterface::class);
        
        //code here
        
        \MyCodeCoverage::G()->end();
        /*
        DBInterface::G()->close();
        DBInterface::G()->getPDO();
        DBInterface::G()->quote($string);
        DBInterface::G()->fetchAll($sql, ...$args);
        DBInterface::G()->fetch($sql, ...$args);
        DBInterface::G()->fetchColumn($sql, ...$args);
        DBInterface::G()->execute($sql, ...$args);
        //*/
    }
}
