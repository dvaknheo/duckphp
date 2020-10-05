<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookPathInfoByGet;
use DuckPhp\Core\App;
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
        App::G()->init(['skip_setting_file'=>true]);
        $options=[
            'use_path_info_by_get'=>false,
            'key_for_action'=>'',
            'key_for_module'=>'',

        ];
        RouteHookPathInfoByGet::G(new RouteHookPathInfoByGet())->init($options, App::G());
        
        $options=[
            'use_path_info_by_get'=>true,
            'key_for_action'=>'',
            'key_for_module'=>'',
        ];
        RouteHookPathInfoByGet::G(new RouteHookPathInfoByGet())->init($options);
        $options=[
            'use_path_info_by_get'=>true,

            'key_for_action'=>'_r',
            'key_for_module'=>'',
        ];
        RouteHookPathInfoByGet::G()->init($options, App::G());
        
        

        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';


        Route::G()->prepare([
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