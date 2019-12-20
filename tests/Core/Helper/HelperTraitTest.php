<?php
namespace tests\DuckPhp\Core\Helper;

use DuckPhp\Core\Helper\HelperTrait;

class HelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HelperTrait::class);
        
        $options=[
            'skip_setting_file'=>true,
            'is_debug'=>true,
            'is_debug'=>'for_tests',
        ];
        \DuckPhp\Core\APP::G()->init($options);
        
        HelperTraitObject::IsDebug();
        HelperTraitObject::IsRealDebug();
        HelperTraitObject::Platform();
        HelperTraitObject::trace_dump();
        HelperTraitObject::var_dump($options);
        HelperTraitObject::Logger();

        \MyCodeCoverage::G()->end(HelperTrait::class);
        $this->assertTrue(true);
        /*

        //*/
    }
}
class HelperTraitObject
{
    use HelperTrait;
}