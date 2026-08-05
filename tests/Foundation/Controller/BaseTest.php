<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\Foundation\Controller\Base;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Base::class);
        $obj = new TestControllerBase();
        $this->assertSame($obj, TestControllerBase::_($obj));
        $this->assertSame($obj, TestControllerBase::_());
        \LibCoverage\LibCoverage::End();
    }
}
class TestControllerBase extends Base
{
}
