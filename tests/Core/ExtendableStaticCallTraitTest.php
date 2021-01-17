<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\ExtendableStaticCallTrait;

class ExtendableStaticCallTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExtendableStaticCallTrait::class);
        
        //code here
        ExtendableStaticCallTraitObject::AssignExtendStaticMethod('Foo',[static::class,'Foo']);
        ExtendableStaticCallTraitObject::AssignExtendStaticMethod(['Foo1'=> ExtendableStaticCallTraitObject::class .'@FooX']);
        ExtendableStaticCallTraitObject::AssignExtendStaticMethod(['Foo2'=>ExtendableStaticCallTraitObject::class .'->FooX']);
        //ExtendableStaticCallTraitObject::AssignExtendStaticMethod(['Foo2'=>ExtendableStaticCallTraitObject::class.'::G'.'::'.'FooX']);
        
        ExtendableStaticCallTraitObject::GetExtendStaticMethodList();
        
        ExtendableStaticCallTraitObject::Foo(123);
        ExtendableStaticCallTraitObject::Foo1(123);
        ExtendableStaticCallTraitObject::Foo2(123);

         try{
            ExtendableStaticCallTraitObject::Foo2(123);
        }catch(\Throwable $ex){
        }
        try{
            ExtendableStaticCallTraitObject::NotExists(123);
        }catch(\Throwable $ex){
        }
        
        \LibCoverage\LibCoverage::End();
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
    public static function G($object=null)
    {
        $class=static::class;
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new static));
        return $_instance[$class];
    }
    use ExtendableStaticCallTrait;
    public static function FooX(...$arg)
    {
        var_dump(DATE(DATE_ATOM),...$arg);
    }
}