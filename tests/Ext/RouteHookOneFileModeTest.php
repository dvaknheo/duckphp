<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookOneFileMode;

class RouteHookOneFileModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookOneFileMode::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(RouteHookOneFileMode::class);
        $this->assertTrue(true);
        /*
        RouteHookOneFileMode::G()->init($options=[], $context=null);
        RouteHookOneFileMode::G()->onURL($url=null);
        RouteHookOneFileMode::G()->hook($route);
        //*/
    }
}
