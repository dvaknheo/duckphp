<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhpAllInOne;

class DuckPhpAllInOneTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpAllInOne::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        new DuckPhpAllInOne();
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End(DuckPhpAllInOne::class);

    }

}
