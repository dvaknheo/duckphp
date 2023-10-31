<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleHelperTrait;

class SimpleHelperTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleHelperTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}