<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\ControllerFakeSingletonTrait;

class ControllerFakeSingletonTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(ControllerFakeSingletonTrait::class);
        
        ControllerFakeSingletonTraitObject::G(ControllerFakeSingletonTraitObject2::G());
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class ControllerFakeSingletonTraitObject
{
    use ControllerFakeSingletonTrait;
}
class ControllerFakeSingletonTraitObject2 extends ControllerFakeSingletonTraitObject
{
    
}
