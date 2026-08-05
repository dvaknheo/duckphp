<?php
namespace tests\DuckPhp\Foundation\Business;

use DuckPhp\Foundation\Business\Base;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Base::class);
        $obj = new TestBusinessBase();
        $this->assertSame($obj, TestBusinessBase::_($obj));
        $this->assertSame($obj, TestBusinessBase::_());
        \LibCoverage\LibCoverage::End();
    }
}
class TestBusinessBase extends Base
{
}
