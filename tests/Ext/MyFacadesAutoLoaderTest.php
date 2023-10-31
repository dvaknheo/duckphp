<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\MyFacadesAutoLoader;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
use MyFacades\tests\DuckPhp\Ext\MyFacadesAutoLoaderTestObject as TestObject;

class MyFacadesAutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(MyFacadesAutoLoader::class);
        $options=[
            'facades_namespace'=>'MyFacades',
            'facades_map'=>[
                'MyMyFacades\\X' =>MyFacadesAutoLoaderTestObject::class,
                'MyMyFacades\\NoG' =>NoG::class,
            ],
        ];
        MyFacadesAutoLoader::_()->init($options,null);
        TestObject::Foo();
        
        
        \MyMyFacades\X::foo();
        try{
            \MyMyFacades\NoG::foo();
        }catch(\Throwable $ex){
        }
        try{
        \MyMyFacades\NoG::foo();
        }catch(\Throwable $ex){
        }
        $flag=class_exists('Class_not_exists');
        
        MyFacadesAutoLoader::_()->clear();
        
        
        MyFacadesAutoLoader::_()->isInited();
        \LibCoverage\LibCoverage::End();
        /*
        MyFacadesAutoLoader::_()->init($options=[], $context);
        MyFacadesAutoLoader::_()->_autoload($class);
        MyFacadesAutoLoader::_()->getMyFacadesCallback($class, $name);
        MyFacadesAutoLoader::_()->__callStatic($name, $arguments);
        //*/
    }
}
class MyFacadesAutoLoaderTestObject
{
    use SingletonExTrait;
    public function foo()
    {
        var_dump("OK");
    }
}
class NoG
{
    public function foo()
    {
        var_dump("OK");
    }
}