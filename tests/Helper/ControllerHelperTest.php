<?php 
namespace tests\DNMVCS\Helper;
use DNMVCS\Helper\ControllerHelper;

class ControllerHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ControllerHelper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ControllerHelper::class);
        $this->assertTrue(true);
        /*
        ControllerHelper::G()->Import($file);
        ControllerHelper::G()->RecordsetUrl($data, $cols_map=[]);
        ControllerHelper::G()->RecordsetH($data, $cols=[]);
        ControllerHelper::G()->Pager();
        ControllerHelper::G()->MapToService($serviceClass, $input);
        ControllerHelper::G()->explodeService($object, $namespace="MY\\Service\\");
        //*/
    }
}
