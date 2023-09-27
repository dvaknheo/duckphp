<?php 
namespace tests\DuckPhp\Foundation;
use DuckPhp\Foundation\SimpleControllerTrait;

class ControllerFakeSingletonTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(SimpleControllerTrait::class);
        
        ControllerFakeSingletonTraitObject::G(ControllerFakeSingletonTraitObject2::G());
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class ControllerFakeSingletonTraitObject
{
    use SimpleControllerTrait;
}
class ControllerFakeSingletonTraitObject2 extends ControllerFakeSingletonTraitObject
{
    
}
