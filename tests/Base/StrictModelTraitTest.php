<?php
namespace tests\DuckPhp\Base;

use DuckPhp\Base\StrictModelTrait;
use DuckPhp\DuckPhp;

class StrictModelTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictModelTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
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
