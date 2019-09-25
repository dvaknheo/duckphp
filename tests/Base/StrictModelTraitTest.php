<?php
namespace tests\DNMVCS\Base;

use DNMVCS\Base\StrictModelTrait;
use DNMVCS\DNMVCS;

class StrictModelTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictModelTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DNMVCS::G()->init($options);
        StrictModelTraitObject::G();
        
        \MyCodeCoverage::G()->end(StrictModelTrait::class);
        $this->assertTrue(true);
        /*
        StrictModelTrait::G()->G($object=null);
        //*/
    }
}
class StrictModelTraitObject
{
    use StrictModelTrait;
}
