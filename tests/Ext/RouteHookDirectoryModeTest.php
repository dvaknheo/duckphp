<?php
namespace tests\DNMVCS\Ext;

use DNMVCS\Ext\RouteHookDirectoryMode;
use DNMVCS\Core\Route;
use DNMVCS\Core\SuperGlobal;

class RouteHookDirectoryModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookDirectoryMode::class);
        
         $options=[
                'mode_dir_basepath'=>'',
                'mode_dir_index_file'=>'',
                'mode_dir_use_path_info'=>true,
                'mode_dir_key_for_module'=>true,
                'mode_dir_key_for_action'=>true,
        ];
        RouteHookDirectoryMode::G()->init($options, $context=null);
        $options=[
            
        ];
        

        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';

        
        Route::G(new Route())->init($options);
        
        RouteHookDirectoryMode::Hook(Route::G());
        RouteHookDirectoryMode::G()->onURL("zzz");

        Route::G()->bindServerData([
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
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
