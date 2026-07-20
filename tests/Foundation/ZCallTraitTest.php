<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Foundation\ZCallTrait;
use DuckPhp\Foundation\BusinessTrait;
use DuckPhp\DuckPhp as App;
class ZCallTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ZCallTrait::class);
        App::_()->init([]);
        MyZCallObject::_Z(App::Phase())->foo();
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyZCallObject
{
    use BusinessTrait;
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}