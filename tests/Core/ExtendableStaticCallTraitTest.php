<?php
namespace tests\DNMVCS\Core;

use DNMVCS\Core\ExtendableStaticCallTrait;

class ExtendableStaticCallTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ExtendableStaticCallTrait::class);
        
        //code here
        ExtendableStaticCallTraitObject::AssignExtendStaticMethod('Foo',[static::class,'Foo']);
        
        ExtendableStaticCallTraitObject::GetExtendStaticStaticMethodList();
        
        ExtendableStaticCallTraitObject::Foo(123);
        
        \MyCodeCoverage::G()->end(ExtendableStaticCallTrait::class);
        $this->assertTrue(true);
        /*

        ExtendableStaticCallTraitObject::__callStatic($name, $arguments);
        //*/
    }
    public static function Foo(...$arg)
    {
        var_dump(DATE(DATE_ATOM),...$arg);
    }
}
class ExtendableStaticCallTraitObject
{
    use ExtendableStaticCallTrait;
}