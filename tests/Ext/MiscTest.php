<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\Misc;
use DuckPhp\DuckPhp;
use DuckPhp\Core\SingletonEx;

class MiscTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Misc::class);
        $path_lib=\GetClassTestPath(Misc::class);
        $options=[
            'path'=>$path_lib,
            'path_lib'=>'',
        ];
        Misc::G()->init($options,DuckPhp::G());
        $options=[
            'path_lib'=>$path_lib,
            'use_super_global'=>false,
            'skip_setting_file'=>true,
            'error_404'=>null,
        ];
        Misc::G()->init($options,DuckPhp::G());
        
        Misc::Import('file');
        
        $data=[];
        Misc::RecordsetUrl($data);
        Misc::RecordsetH($data);
        
        $data=[['id'=>'1','text'=>'<b>'],['id'=>'2','text'=>'&']];
        Misc::RecordsetUrl($data, []);
        $ret=Misc::RecordsetUrl($data, ['url'=>'edit/{id}']);
        $data=Misc::RecordsetH($data);
        print_r($data);
        
        Misc::explodeService(FakeObject::G(), $namespace=__NAMESPACE__ .'\\');
        
        
        DuckPhp::G()->system_wrapper_replace([
            'exit_system' =>function(){ echo "change!\n";},
        ]);
        DuckPhp::G()->init($options)->run();
        
        Misc::mapToService(FakeService::class, []);
        
        DuckPhp::G()->setRouteCallingMethod('m1');
        Misc::mapToService(FakeService::class, ['id'=>111]);
        Misc::mapToService(FakeService::class, ['id'=>"zz"]);
        
        DuckPhp::G()->setRouteCallingMethod('m1');
        Misc::mapToService(FakeService::class, []);
        
        $object=new \stdClass();
        Misc::DI('a',$object);
        Misc::DI('a');
        

        \MyCodeCoverage::G()->end(Misc::class);
        $this->assertTrue(true);
        /*
        Misc::G()->init($options=[], $context=null);
        Misc::G()->_Import($file);
        Misc::G()->_RecordsetUrl($data, $cols_map=[]);
        Misc::G()->_RecordsetH($data, $cols=[]);
        Misc::G()->callAPI($class, $method, $input);
        Misc::G()->mapToService($serviceClass, $input);
        Misc::G()->explodeService($object, $namespace=null);
        //*/
    }
    
}
class FakeService
{
    use SingletonEx;
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
    
    use SingletonEx;
    public function foo()
    {
        var_dump(DATE(DATE_ATOM));
    }
}
