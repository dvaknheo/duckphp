<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleExceptionTrait;

class SimpleExceptionTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleExceptionTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}