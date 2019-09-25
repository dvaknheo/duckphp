<?php 
namespace tests\DNMVCS\Base;
use DNMVCS\Base\StrictModelTrait;

class StrictModelTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictModelTrait::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(StrictModelTrait::class);
        $this->assertTrue(true);
        /*
        StrictModelTrait::G()->G($object=null);
        //*/
    }
}
