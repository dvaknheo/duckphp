<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookOneFileMode;
use DNMVCS\Core\Route;
use DNMVCS\Core\SuperGlobal;

class RouteHookOneFileModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookOneFileMode::class);
        
        $options=[
            'key_for_action'=>'_r',
            'key_for_module'=>'',
        ];
        RouteHookOneFileMode::G()->init($options, $context=null);
        $options=[
            
        ];
        

        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';

        
        Route::G(new Route())->init($options);
        
        RouteHookOneFileMode::Hook(Route::G());
        RouteHookOneFileMode::G()->onURL("zzz");

        Route::G()->bindServerData([
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        \MyCodeCoverage::G()->end(RouteHookOneFileMode::class);
        $this->assertTrue(true);
        /*
        RouteHookOneFileMode::G()->init($options=[], $context=null);
        RouteHookOneFileMode::G()->onURL($url=null);
        RouteHookOneFileMode::G()->hook($route);
        //*/
    }
}
