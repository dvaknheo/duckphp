<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\DuckPhp;
use DuckPhp\Helper\HelperTrait;

class HelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HelperTrait::class);
        
        $options=[
            'is_debug'=>true,
            'platform'=>'for_tests',
        ];
        DuckPhp::G()->init($options);
        
        HelperTraitObject::IsDebug();
        HelperTraitObject::IsRealDebug();
        HelperTraitObject::Platform();
        HelperTraitObject::trace_dump();
        HelperTraitObject::var_dump($options);
        HelperTraitObject::debug_log('...',['a'=>'b']);
        HelperTraitObject::Logger();

        try{
            HelperTraitObject::ThrowOn(true,"HH");
        }catch(\Exception $ex){
        }
        \MyCodeCoverage::G()->end();
        /*

        //*/
    }
}
class HelperTraitObject
{
    use HelperTrait;
}