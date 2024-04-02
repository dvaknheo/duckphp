<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\ZCallTrait;
use DuckPhp\Foundation\SimpleBusinessTrait;
use DuckPhp\Core\App;
class ZCallTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ZCallTrait::class);
        App::_()->init([]);
        MyZCallObject::ZCall(App::Phase())->foo();
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyZCallObject
{
    use SimpleBusinessTrait;
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}