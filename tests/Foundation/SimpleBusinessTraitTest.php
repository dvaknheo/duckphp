<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Component\SimpleBusinessTrait;
use DuckPhp\DuckPhp;

class SimpleBusinessTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleBusinessTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}