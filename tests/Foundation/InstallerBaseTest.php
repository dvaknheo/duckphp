<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\InstallerBase;

class InstallerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(InstallerBase::class);

        \LibCoverage\LibCoverage::End();
    }
}
