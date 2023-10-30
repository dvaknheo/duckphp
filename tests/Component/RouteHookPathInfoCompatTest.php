<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\RouteHookPathInfoCompat;
use DuckPhp\Core\App;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;

class RouteHookPathInfoCompatTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookPathInfoCompat::class);
        
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookPathInfoCompatTestMain',

        ];
        
        Route::_(new Route())->init($route_options);
        App::_()->init([]);
        $options=[
            'path_info_compact_enable'=>false,
            'path_info_compact_action_key'=>'',
            'path_info_compact_class_key'=>'',

        ];
        RouteHookPathInfoCompat::_(new RouteHookPathInfoCompat())->init($options, App::_());
        
        $options=[
            'path_info_compact_enable'=>true,
            'path_info_compact_action_key'=>'',
            'path_info_compact_class_key'=>'',
        ];
        RouteHookPathInfoCompat::_(new RouteHookPathInfoCompat())->init($options);
        $options=[
            'path_info_compact_enable'=>true,

            'path_info_compact_action_key'=>'_r',
            'path_info_compact_class_key'=>'',
        ];
        RouteHookPathInfoCompat::_()->init($options, App::_());
        
        

        $_SERVER['REQUEST_URI']='';
        $_SERVER['PATH_INFO']='';


        Route::_()->bind('Missed','POST');
        Route::_()->run();
        
        echo "------------------------------------------------\n";
if(true){
        RouteHookPathInfoCompat::_()->onURL("zzz");
        echo "------------------------------------------------\n";
}
        //x/index.php/init
        $_SERVER['REQUEST_URI']='/x/index.php/model/action';
        $_SERVER['PATH_INFO']='/model/action';
        $_SERVER['SCRIPT_FILENAME']='/test/index.php';
        $options=[
            'path_info_compact_action_key'=>'_r',
            'path_info_compact_class_key'=>'m',
        ];
        var_dump(Route::URL('/Test'));

        RouteHookPathInfoCompat::_()->init($options);
        
        var_dump(Route::URL(''));

        var_dump(RouteHookPathInfoCompat::URL('index.php/bb?cc=dd&m=abc'));
        var_dump(RouteHookPathInfoCompat::URL('aa/bb?cc=dd&m=abc'));
        var_dump(RouteHookPathInfoCompat::URL('aa/bb?cc=dd&m='));

        //------------
        
                        RouteHookPathInfoCompat::_()->isInited();

        \DuckPhp\Core\SuperGlobal::DefineSuperGlobalContext();
Route::_()->bind('Missed','POST');
        Route::_()->run();
        
        var_dump(RouteHookPathInfoCompat::URL('index.php/bb?cc=dd&m=abc'));
        var_dump(RouteHookPathInfoCompat::URL('aa/bb?cc=dd&m=abc'));
        var_dump(RouteHookPathInfoCompat::URL('aa/bb?cc=dd&m='));
        
        
        \LibCoverage\LibCoverage::End();
        /*
        RouteHookPathInfoCompat::_()->init($options=[], $context=null);
        RouteHookPathInfoCompat::_()->onURL($url=null);
        RouteHookPathInfoCompat::_()->hook($route);
        //*/
    }
}
class RouteHookPathInfoCompatTestMain
{    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }
}