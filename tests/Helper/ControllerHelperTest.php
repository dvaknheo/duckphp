<?php
namespace tests\DNMVCS\Helper;

use DNMVCS\Helper\ControllerHelper;
use DNMVCS\DNMVCS;
class ControllerHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ControllerHelper::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DNMVCS::G()->init($options);
        DNMVCS::G()->system_wrapper_replace([
            'exit_system' =>function(){ echo "change!\n";},
        ]);
        
        $data=[];
        $cols_map=[];
        $cols=[];
        ControllerHelper::RecordsetUrl($data, $cols_map=[]);
        ControllerHelper::RecordsetH($data, $cols=[]);
        ControllerHelper::Pager();
        $object=new \stdClass();
        ControllerHelper::explodeService($object, $namespace="MY\\Service\\");
        
        
        //TODO;
        $serviceClass="abc";
        $input=[];
        try {
            
        ControllerHelper::MapToService($serviceClass, $input);
        } catch(\Throwable $ex){
        }

        
        //TODO;
        $file="xx";
        try {
        ControllerHelper::Import($file);
        } catch(\Throwable $ex){
        }
        //*/
        
        \MyCodeCoverage::G()->end(ControllerHelper::class);
        $this->assertTrue(true);
    }
}
