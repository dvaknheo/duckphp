<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Ext\Installer;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Installer::class);

        \LibCoverage\LibCoverage::End();
    }
}
