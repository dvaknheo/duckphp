<?php
namespace tests\DuckPhp\Base;

use DuckPhp\Base\StrictServiceTrait;
use DuckPhp\App as DuckPhp;

class StrictServiceTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictServiceTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
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
