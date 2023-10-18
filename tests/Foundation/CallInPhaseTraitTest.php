<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\CallInPhaseTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

class CallInPhaseTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(CallInPhaseTrait::class);
        MyCallInPhase::CallInPhase(DuckPhp::class)->foo();
        \LibCoverage\LibCoverage::End();
    }
}
class MyCallInPhase
{
    use CallInPhaseTrait;
    use SingletonExTrait;
    public function foo(){
        var_dump("foo");
    }
}
