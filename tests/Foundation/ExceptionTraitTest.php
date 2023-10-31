<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\ExceptionTrait;

class ExceptionTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExceptionTrait::class);
        \LibCoverage\LibCoverage::End();
    }
}