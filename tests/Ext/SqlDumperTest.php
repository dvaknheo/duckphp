<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SqlDumper;

class SqlDumperTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumper::class);
        
        //SqlDumper::G()->run();
        //SqlDumper::G()->install();
        
        \LibCoverage\LibCoverage::End();
    }
}