<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookDirectoryMode;
use DuckPhp\Core\Route;
use DuckPhp\Core\SuperGlobal;

class RouteHookDirectoryModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(RouteHookDirectoryMode::class);
        
        $base_path=\GetClassTestPath(RouteHookDirectoryMode::class);
        $route_options=[
            'namespace'=>__NAMESPACE__,
            'namespace_controller'=>'\\'.__NAMESPACE__,
            'controller_welcome_class'=> 'RouteHookDirectoryModeTesttMain',

        ];
        Route::G(new Route())->init($route_options);
        $options=[
                'mode_dir_basepath'=>$base_path,
                'mode_dir_index_file'=>'',
                'mode_dir_use_path_info'=>true,
                'mode_dir_key_for_module'=>true,
                'mode_dir_key_for_action'=>true,
        ];
        RouteHookDirectoryMode::G()->init($options, $context=null);
        RouteHookDirectoryMode::G()->init($options, Route::G());
        
        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';
        
        Route::G()->bindServerData([
            'DOCUMENT_ROOT'=>rtrim($base_path,'/'),
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ]);
        Route::G()->run();
        
        SuperGlobal::G()->_SERVER['REQUEST_URI']='';
        SuperGlobal::G()->_SERVER['PATH_INFO']='';
echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";
        SuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=rtrim($base_path,'/');
        echo RouteHookDirectoryMode::G()->onURL("/izx");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("BUG");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("m");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("m/index");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("m/foo");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("a/b/c");
        echo PHP_EOL;
        echo RouteHookDirectoryMode::G()->onURL("a/b/index");
        echo PHP_EOL;

echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";

    
        $file='/a/b.php';
        SuperGlobal::G()->_SERVER['DOCUMENT_ROOT']=rtrim($base_path,'/'); ///a/b.php
        SuperGlobal::G()->_SERVER['SCRIPT_FILENAME']=rtrim($base_path,'/').$file;
        SuperGlobal::G()->_SERVER['PATH_INFO']='';
        SuperGlobal::G()->_SERVER['REQUEST_URI']=$file;
        SuperGlobal::G()->_SERVER['REQUEST_URI'].=SuperGlobal::G()->_SERVER['PATH_INFO'];
        
        var_dump(SuperGlobal::G()->_SERVER['REQUEST_URI']);

        Route::G()->bindServerData(SuperGlobal::G()->_SERVER);
        Route::G()->run();
        
        \MyCodeCoverage::G()->end(RouteHookDirectoryMode::class);
        $this->assertTrue(true);
    }
}
class RouteHookDirectoryModeTesttMain
{    
    function index(){
        var_dump(DATE(DATE_ATOM));
    }
    function i()
    {
        var_dump("I");
    }
}