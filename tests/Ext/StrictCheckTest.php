<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\StrictCheck;

class StrictCheckTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(StrictCheck::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(StrictCheck::class);
        $this->assertTrue(true);
        /*
        StrictCheck::G()->init($options=[], $context=null);
        StrictCheck::G()->initContext($options=[], $context=null);
        StrictCheck::G()->getCallerByLevel($level);
        StrictCheck::G()->checkEnv();
        StrictCheck::G()->checkStrictComponent($component_name, $trace_level);
        StrictCheck::G()->checkStrictModel($trace_level);
        StrictCheck::G()->checkStrictService($trace_level);
        StrictCheck::G()->checkStrictParentCaller($trace_level, $parent_class);
        //*/
    }
}
