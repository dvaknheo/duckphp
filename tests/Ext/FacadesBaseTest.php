<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\FacadesBase;
use DNMVCS\Ext\FacadesAutoLoader;
use DNMVCS\Core\SingletonEx;

class FacadesBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(FacadesBase::class);
        
        //code here
        FacadesAutoLoader::G()->init(['facades_map'=>[
            F::class=>B::class
        ]]);
            F::Z();
        try{
            F2::zz();
        }catch(\Exception $ex){
            echo "EXXXXXXXXXXXXx";
        }
        \MyCodeCoverage::G()->end(FacadesBase::class);
        $this->assertTrue(true);
        /*
        FacadesBase::G()->__callStatic($name, $arguments);
        //*/
    }
}
class F extends FacadesBase
{
}
class F2 extends FacadesBase
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