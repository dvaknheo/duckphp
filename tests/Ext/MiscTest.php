<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\Misc;

class MiscTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Misc::class);
        
        //code here
        
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
