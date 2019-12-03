<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookOneFileMode;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;

class RouteHookOneFileModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookOneFileMode::class);
        
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookOneFileModeTestMain',

        ];
        Route::G(new Route())->init($route_options);
        
        $options=[
            'key_for_action'=>'',
            'key_for_module'=>'',
        ];
        RouteHookOneFileMode::G()->init($options);
        $options=[
            'key_for_action'=>'_r',
            'key_for_module'=>'',
        ];
        RouteHookOneFileMode::G()->init($options, Route::G());
        
        

        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';


        Route::G()->bindServerData([
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        echo "------------------------------------------------\n";
if(true){
        RouteHookOneFileMode::G()->onURL("zzz");
        echo "------------------------------------------------\n";
}
        //x/index.php/init
        SuperGlobal::G()->_SERVER['REQUEST_URI']='/x/index.php/model/action';
        SuperGlobal::G()->_SERVER['PATH_INFO']='/model/action';
        SuperGlobal::G()->_SERVER['SCRIPT_FILENAME']='/test/index.php';
        $options=[
            'key_for_action'=>'_r',
            'key_for_module'=>'m',
        ];
        var_dump(Route::URL('/Test'));

        RouteHookOneFileMode::G()->init($options);
        
        var_dump(Route::URL(''));

        var_dump(RouteHookOneFileMode::URL('index.php/bb?cc=dd&m=abc'));
        var_dump(RouteHookOneFileMode::URL('aa/bb?cc=dd&m=abc'));
        var_dump(RouteHookOneFileMode::URL('aa/bb?cc=dd&m='));

        //------------
        
        
        \MyCodeCoverage::G()->end(RouteHookOneFileMode::class);
        $this->assertTrue(true);
        /*
        RouteHookOneFileMode::G()->init($options=[], $context=null);
        RouteHookOneFileMode::G()->onURL($url=null);
        RouteHookOneFileMode::G()->hook($route);
        //*/
    }
}
class RouteHookOneFileModeTestMain
{    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }
}