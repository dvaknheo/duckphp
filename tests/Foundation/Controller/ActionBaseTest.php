<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\Foundation\Controller\ActionBase;

class ActionBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ActionBase::class);
        $obj = new TestActionBase();
        $this->assertSame($obj, TestActionBase::_($obj));
        $this->assertSame($obj, TestActionBase::_());
        \LibCoverage\LibCoverage::End();
    }
}
class TestActionBase extends ActionBase
{
}
