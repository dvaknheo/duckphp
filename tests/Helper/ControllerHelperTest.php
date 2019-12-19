<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ControllerHelper;
use DuckPhp\App as DuckPhp;
class ControllerHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ControllerHelper::class);
        
        $options=[
            'skip_setting_file'=>true,
        ];
        DuckPhp::G()->init($options);
        DuckPhp::G()->system_wrapper_replace([
            'exit' =>function(){ echo "change!\n";},
        ]);
        
        $data=[];
        $cols_map=[];
        $cols=[];
        ControllerHelper::RecordsetUrl($data, $cols_map=[]);
        ControllerHelper::RecordsetH($data, $cols=[]);
        ControllerHelper::Pager();
        $object=new \stdClass();
        
        
        //TODO;
        $serviceClass="abc";
        $input=[];
        try {
            
        } catch(\Throwable $ex){
        }

        
        //TODO;
        $file="xx";
        try {
        } catch(\Throwable $ex){
        }
        //*/
        
        \MyCodeCoverage::G()->end(ControllerHelper::class);
        $this->assertTrue(true);
    }
}
