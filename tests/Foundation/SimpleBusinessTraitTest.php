<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleBusinessTrait;
use DuckPhp\DuckPhp;

class SimpleBusinessTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        DuckPhp::_()->init([]);
        \LibCoverage\LibCoverage::Begin(SimpleBusinessTrait::class);
        SimpleBusinessTraitObject::CallInPhase(DuckPhp::class)->foo();
        \LibCoverage\LibCoverage::End();
    }
}
class SimpleBusinessTraitObject
{
    use SimpleBusinessTrait;
    public function foo()
    {
        var_dump("foo!");
    }
}