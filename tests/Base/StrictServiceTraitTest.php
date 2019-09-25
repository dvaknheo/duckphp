<?php
namespace tests\DNMVCS\Base;

use DNMVCS\Base\StrictServiceTrait;
use DNMVCS\DNMVCS;

class StrictServiceTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictServiceTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DNMVCS::G()->init($options);
        StrictServiceTraitObject::G();
        
        \MyCodeCoverage::G()->end(StrictServiceTrait::class);
        $this->assertTrue(true);
        /*
        StrictServiceTrait::G()->G($object=null);
        //*/
    }
}
class StrictServiceTraitObject
{
    use StrictServiceTrait;
}
