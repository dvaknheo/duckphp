<?php
namespace tests\DNMVCS\Core\Helper;

use DNMVCS\Core\Helper\HelperTrait;

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
        \DNMVCS\Core\APP::G()->init($options);
        
        HelperTraitObject::IsDebug();
        HelperTraitObject::Platform();
        HelperTraitObject::DumpTrace();
        HelperTraitObject::var_dump($options);
        
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