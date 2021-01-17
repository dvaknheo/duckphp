<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\MyFacadesBase;
use DuckPhp\Ext\MyFacadesAutoLoader;
use DuckPhp\SingletonEx\SingletonExTrait;

class MyFacadesBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(MyFacadesBase::class);
        
        //code here
        MyFacadesAutoLoader::G()->init(['facades_map'=>[
            F::class=>B::class
        ]]);
            F::Z();
        try{
            F2::zz();
        }catch(\Exception $ex){
            echo "EXXXXXXXXXXXXx";
        }
        new MyFacadesBase();
        \LibCoverage\LibCoverage::End();
        /*
        MyFacadesBase::G()->__callStatic($name, $arguments);
        //*/
    }
}
class F extends MyFacadesBase
{
}
class F2 extends MyFacadesBase
{
}
class B
{
    use SingletonExTrait;
    public function Z()
    {
        var_dump("OK");
    }
}