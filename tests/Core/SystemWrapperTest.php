<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\SystemWrapper;

class SystemWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SystemWrapper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(SystemWrapper::class);
        $this->assertTrue(true);
        /*
        SystemWrapper::G()->system_wrapper_replace(array $funcs);
        SystemWrapper::G()->system_wrapper_get_providers();
        SystemWrapper::G()->system_wrapper_call_check($func);
        SystemWrapper::G()->system_wrapper_call($func, $input_args);
        //*/
    }
}
