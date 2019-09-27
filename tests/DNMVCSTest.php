<?php
namespace tests\DNMVCS;

use DNMVCS\DNMVCS;

class DNMVCSTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DNMVCS::class);
        
        //code here
        //$handler=null;
        //DNMVCS::G()->addBeforeRunHandler($handler);
        DNMVCS::G()->getStaticComponentClasses();
        DNMVCS::G()->getDynamicComponentClasses();
        
        $this->doGlue();
        \MyCodeCoverage::G()->end(DNMVCS::class);
        $this->assertTrue(true);

    }
    public function doGlue()
    {
        //DNMVCS::DB($tag=null);
        //DNMVCS::DB_W();
        //DNMVCS::DB_R();
        DNMVCS::setDBHandler($db_create_handler=null, $db_close_handler=null, $db_excption_handler=null);
        DNMVCS::Pager();
        DNMVCS::assignRewrite($key="abc", $value=null);
        DNMVCS::getRewrites();
        DNMVCS::assignRoute($key="zzz", $value=null);
        DNMVCS::getRoutes();
        DNMVCS::CheckStrictDB();
        DNMVCS::checkStrictComponent($component_name="z", $trace_level=2);
        DNMVCS::checkStrictService($trace_level=2);
        DNMVCS::checkStrictModel($trace_level=2);
        //DNMVCS::Import($file="z");
        $data=[['A'=>'b']];
        //DNMVCS::RecordsetUrl($data, $cols_map=[]);
        //DNMVCS::RecordsetH($data, $cols=[]);
        //DNMVCS::callAPI($class, $method, $input);
        //DNMVCS::MapToService($serviceClass, $input);
        //DNMVCS::explodeService($object, $namespace="MY\\Service\\");
    }
}
