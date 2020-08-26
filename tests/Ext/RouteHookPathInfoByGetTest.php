<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookPathInfoByGet;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;

class RouteHookPathInfoByGetTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookPathInfoByGet::class);
        
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookPathInfoByGetTestMain',

        ];
        Route::G(new Route())->init($route_options);
        
        $options=[
            'key_for_action'=>'',
            'key_for_module'=>'',
        ];
        RouteHookPathInfoByGet::G()->init($options);
        $options=[
            'key_for_action'=>'_r',
            'key_for_module'=>'',
        ];
        RouteHookPathInfoByGet::G()->init($options, Route::G());
        
        

        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';


        Route::G()->bindServerData([
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        echo "------------------------------------------------\n";
if(true){
        RouteHookPathInfoByGet::G()->onURL("zzz");
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

        RouteHookPathInfoByGet::G()->init($options);
        
        var_dump(Route::URL(''));

        var_dump(RouteHookPathInfoByGet::URL('index.php/bb?cc=dd&m=abc'));
        var_dump(RouteHookPathInfoByGet::URL('aa/bb?cc=dd&m=abc'));
        var_dump(RouteHookPathInfoByGet::URL('aa/bb?cc=dd&m='));

        //------------
        
                        RouteHookPathInfoByGet::G()->isInited();

        \MyCodeCoverage::G()->end();
        /*
        RouteHookPathInfoByGet::G()->init($options=[], $context=null);
        RouteHookPathInfoByGet::G()->onURL($url=null);
        RouteHookPathInfoByGet::G()->hook($route);
        //*/
    }
}
class RouteHookPathInfoByGetTestMain
{    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }
}