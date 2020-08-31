<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\StrictCheckObjectTrait;
use DuckPhp\DuckPhp;

class StrictCheckObjectTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheckObjectTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
        StrictCheckObjectTraitObject::G();
        
        \MyCodeCoverage::G()->end();
        /*
        StrictServiceTrait::G()->G($object=null);
        //*/
    }
}
class StrictCheckObjectTraitObject
{
    use StrictCheckObjectTrait;
}
