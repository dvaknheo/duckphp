<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Ext\InstallableTrait;

class InstallableTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(InstallableTrait::class);

        \LibCoverage\LibCoverage::End();
    }
}
