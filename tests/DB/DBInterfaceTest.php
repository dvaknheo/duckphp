<?php 
namespace tests\DNMVCS\DB;
use DNMVCS\DB\DBInterface;

class DBInterfaceTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBInterface::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(DBInterface::class);
        $this->assertTrue(true);
        /*
        DBInterface::G()->close();
        DBInterface::G()->getPDO();
        DBInterface::G()->quote($string);
        DBInterface::G()->fetchAll($sql, ...$args);
        DBInterface::G()->fetch($sql, ...$args);
        DBInterface::G()->fetchColumn($sql, ...$args);
        DBInterface::G()->execQuick($sql, ...$args);
        //*/
    }
}
