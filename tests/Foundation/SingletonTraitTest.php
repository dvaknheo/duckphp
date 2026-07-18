<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SingletonTrait;

class SingletonTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SingletonTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}