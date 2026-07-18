<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\BusinessTrait;
use DuckPhp\DuckPhp;

class BusinessTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        DuckPhp::_()->init([]);
        \LibCoverage\LibCoverage::Begin(BusinessTrait::class);
        BusinessTraitObject::_Z(DuckPhp::class)->foo();
        \LibCoverage\LibCoverage::End();
    }
}
class BusinessTraitObject
{
    use BusinessTrait;
    public function foo()
    {
        var_dump("foo!");
    }
}