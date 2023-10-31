<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhpAllInOne;
use PHPUnit\Framework\Assert;

class DuckPhpAllInOneTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpAllInOne::class);
        
        Assert::assertTrue(true);
        Assert::assertTrue(true);
        
        $LibCoverage = \LibCoverage\LibCoverage::G();
        new DuckPhpAllInOne();
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End(DuckPhpAllInOne::class);

    }

}
