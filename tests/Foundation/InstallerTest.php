<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\Installer;

class InstallerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Installer::class);

        \LibCoverage\LibCoverage::End();
    }
}