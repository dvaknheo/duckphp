<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\Misc;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;

class MiscTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Misc::class);
        $path_lib=\LibCoverage\LibCoverage::G()->getClassTestPath(Misc::class);
        $options=[
            'path'=>$path_lib,
            'path_lib'=>'',
        ];
        Misc::_()->init($options,DuckPhp::_());
        Misc::Import('file');
        $options=[
            'path_lib'=>$path_lib,
            'use_super_global'=>false,
            'error_404'=>null,
        ];
        Misc::_()->init($options,DuckPhp::_());
        
        Misc::Import('file');
        
        $data=[];
        Misc::RecordsetUrl($data);
        Misc::RecordsetH($data);
        
        $data=[['id'=>'1','text'=>'<b>'],['id'=>'2','text'=>'&']];
        Misc::RecordsetUrl($data, []);
        $ret=Misc::RecordsetUrl($data, ['url'=>'edit/{id}']);
        $data=Misc::RecordsetH($data);
        print_r($data);
        
        
        
        SystemWrapper::_()->_system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        DuckPhp::_()->init($options)->run();
        
        
        Route::_()->setRouteCallingMethod('m1');

        Route::_()->setRouteCallingMethod('m1');
        
        $object=new \stdClass();
        Misc::DI('a',$object);
        Misc::DI('a');
        
        try{
            Misc::CallAPI(FakeService::class,'m1',['id'=>'1'],FakeInterface::class);
        }catch(\Exception $ex){
        }
        try{
            Misc::CallAPI(FakeService::class,'m2',['id'=>[]],"");
        }catch(\Exception $ex){
        }
        try{
            Misc::CallAPI(FakeService::class,'m1',[]);
        }catch(\Exception $ex){
        }
        Misc::CallAPI(FakeService::class,'m1',['id'=>'1']);
        Misc::_()->isInited();

        \LibCoverage\LibCoverage::End();
        /*
        Misc::_()->init($options=[], $context=null);
        Misc::_()->_Import($file);
        Misc::_()->_RecordsetUrl($data, $cols_map=[]);
        Misc::_()->_RecordsetH($data, $cols=[]);
        Misc::_()->callAPI($class, $method, $input);
        Misc::_()->mapToService($serviceClass, $input);
        Misc::_()->explodeService($object, $namespace=null);
        //*/
    }
    
}
interface FakeInterface
{
    public function foo();
}
class FakeService
{
    use SingletonExTrait;

    public function m1(int $id,string $name="xx")
    {
        return DATE(DATE_ATOM);
    }
public function m2(int $id)
    {
        return DATE(DATE_ATOM);
    }
}
class FakeObject 
{
    public $fakeService=null;
    public $notServcieVar=null;
    
    use SingletonExTrait;
    public function foo()
    {
        var_dump(DATE(DATE_ATOM));
    }
}
