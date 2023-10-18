<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\CallInPhaseTrait;

class CallInPhaseTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(CallInPhaseTrait::class);
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyCallInPhase
{
    use CallInPhaseTrait;
    //
}
