<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\FastInstaller\SupporterByMySql;
//use tests_Data_SqlDumper\Model\EmptyModel;

class SupporterByMySqlTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SupporterByMySql::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
