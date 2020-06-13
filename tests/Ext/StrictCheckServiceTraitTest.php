<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\StrictCheckServiceTrait;
use DuckPhp\DuckPhp;

class StrictServiceTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheckServiceTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
        StrictCheckServiceTraitObject::G();
        
        \MyCodeCoverage::G()->end();
        /*
        StrictServiceTrait::G()->G($object=null);
        //*/
    }
}
class StrictCheckServiceTraitObject
{
    use StrictCheckServiceTrait;
}
