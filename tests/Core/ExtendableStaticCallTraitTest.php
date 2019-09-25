<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\ExtendableStaticCallTrait;

class ExtendableStaticCallTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ExtendableStaticCallTrait::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ExtendableStaticCallTrait::class);
        $this->assertTrue(true);
        /*
        ExtendableStaticCallTrait::G()->AssignExtendStaticMethod($key, $value=null);
        ExtendableStaticCallTrait::G()->GetExtendStaticStaticMethodList();
        ExtendableStaticCallTrait::G()->CallExtendStaticMethod($name, $arguments);
        ExtendableStaticCallTrait::G()->__callStatic($name, $arguments);
        //*/
    }
}
