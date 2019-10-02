<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\FacadesAutoLoader;
use DNMVCS\Core\SingletonEx;
use Facades\tests\DNMVCS\Ext\FacadesAutoLoaderTestObject as TestObject;

class FacadesAutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(FacadesAutoLoader::class);
        $options=[
            'facades_namespace'=>'Facades',
            'facades_map'=>[
                'MyFacades\\X' =>FacadesAutoLoaderTestObject::class,
                'MyFacades\\NoG' =>NoG::class,
            ],
        ];
        FacadesAutoLoader::G()->init($options,null);
        TestObject::Foo();
        
        
        \MyFacades\X::foo();
        try{
            \MyFacades\NoG::foo();
        }catch(\Throwable $ex){
        }
        try{
        \MyFacades\NoG::foo();
        }catch(\Throwable $ex){
        }
        $flag=class_exists('Class_not_exists');
        
        FacadesAutoLoader::G()->cleanUp();
        
        
        
        \MyCodeCoverage::G()->end(FacadesAutoLoader::class);
        $this->assertTrue(true);
        /*
        FacadesAutoLoader::G()->init($options=[], $context);
        FacadesAutoLoader::G()->_autoload($class);
        FacadesAutoLoader::G()->getFacadesCallback($class, $name);
        FacadesAutoLoader::G()->__callStatic($name, $arguments);
        //*/
    }
}
class FacadesAutoLoaderTestObject
{
    use SingletonEx;
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