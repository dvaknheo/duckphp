<?php 
namespace tests\DuckPhp\Ext;
use DuckPhp\Ext\MyFacadesBase;
use DuckPhp\Ext\MyFacadesAutoLoader;
use DuckPhp\SingletonEx\SingletonEx;

class MyFacadesBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(MyFacadesBase::class);
        
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
        \MyCodeCoverage::G()->end();
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
    use SingletonEx;
    public function Z()
    {
        var_dump("OK");
    }
}