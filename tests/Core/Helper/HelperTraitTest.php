<?php 
namespace tests\DNMVCS\Core\Helper;
use DNMVCS\Core\Helper\HelperTrait;

class HelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HelperTrait::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(HelperTrait::class);
        $this->assertTrue(true);
        /*
        HelperTrait::G()->IsDebug();
        HelperTrait::G()->Platform();
        //*/
    }
}
