<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\RouteHookDirectoryMode;
use DuckPhp\Core\App;
use DuckPhp\Core\Route;

class RouteHookDirectoryModeTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookDirectoryMode::class);
        
        $base_path=\LibCoverage\LibCoverage::G()->getClassTestPath(RouteHookDirectoryMode::class);
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
        RouteHookDirectoryMode::G()->init($options, App::G());
        
        $_SERVER['REQUEST_URI']='';
        $_SERVER['PATH_INFO']='';
        
        $server=[
            'DOCUMENT_ROOT'=>rtrim($base_path,'/'),
            'PATH_INFO'=>'Missed',
            'REQUEST_METHOD'=>'POST',
        ];
        Route::G()->reset();
        Route::G()->run();
        
        $_SERVER['REQUEST_URI']='';
        $_SERVER['PATH_INFO']='';
echo "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz\n";
        $_SERVER['DOCUMENT_ROOT']=rtrim($base_path,'/');
        echo RouteHookDirectoryMode::URL("/izx");
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
        $_SERVER['DOCUMENT_ROOT']=rtrim($base_path,'/'); ///a/b.php
        $_SERVER['SCRIPT_FILENAME']=rtrim($base_path,'/').$file;
        $_SERVER['PATH_INFO']='';
        $_SERVER['REQUEST_URI']=$file;
        $_SERVER['REQUEST_URI'].=$_SERVER['PATH_INFO'];
        
        var_dump($_SERVER['REQUEST_URI']);

        $_SERVER=$_SERVER;
        Route::G()->reset();
        Route::G()->run();
        RouteHookDirectoryMode::G()->isInited();

        \LibCoverage\LibCoverage::End();
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