<?php 
namespace tests\DNMVCS\Core;
use DNMVCS\Core\ThrowOn;

class ThrowOnTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ThrowOn::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(ThrowOn::class);
        $this->assertTrue(true);
        /*
        ThrowOn::G()->ThrowOn($flag, $message, $code=0, $exception_class=null);
        //*/
    }
}
