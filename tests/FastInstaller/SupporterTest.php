<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\FastInstaller\Supporter;
//use tests_Data_SqlDumper\Model\EmptyModel;

class SupporterTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Supporter::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
