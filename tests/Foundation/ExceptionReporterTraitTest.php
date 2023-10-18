<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporterTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExceptionReporterTrait::class);
        ////
        \LibCoverage\LibCoverage::End();
    }
}
class MyExceptionReporter
{
    use ExceptionReporterTrait;
    //
}
