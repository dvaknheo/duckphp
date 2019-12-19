<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\StrictCheckModelTrait;
use DuckPhp\App as DuckPhp;

class StrictCheckModelTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheckModelTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
        StrictCheckModelTraitObject::G();
        
        \MyCodeCoverage::G()->end(StrictCheckModelTrait::class);
        $this->assertTrue(true);
        /*
        StrictCheckModelTrait::G()->G($object=null);
        //*/
    }
}
class StrictCheckModelTraitObject
{
    use StrictCheckModelTrait;
}
