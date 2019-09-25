<?php 
namespace tests\DNMVCS\Ext;
use DNMVCS\Ext\RouteHookDirectoryMode;

class RouteHookDirectoryModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookDirectoryMode::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(RouteHookDirectoryMode::class);
        $this->assertTrue(true);
        /*
        RouteHookDirectoryMode::G()->init($options=[], $context=null);
        RouteHookDirectoryMode::G()->adjustPathinfo($path_info, $document_root);
        RouteHookDirectoryMode::G()->onURL($url=null);
        RouteHookDirectoryMode::G()->hook($route);
        //*/
    }
}
