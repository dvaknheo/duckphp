<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleSingletonTrait;

class SimpleSingletonTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleSingletonTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}