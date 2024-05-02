<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\FastInstaller\SupporterBySqlite;
//use tests_Data_SqlDumper\Model\EmptyModel;

class SupporterBySqliteTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SupporterBySqlite::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
