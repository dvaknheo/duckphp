<?php
namespace tests\DNMVCS\Base;

use DNMVCS\Base\StrictServiceTrait;

class StrictServiceTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictServiceTrait::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(StrictServiceTrait::class);
        $this->assertTrue(true);
        /*
        StrictServiceTrait::G()->G($object=null);
        //*/
    }
}
