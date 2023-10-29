<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\RouteHookResource;
use DuckPhp\Core\App;
use DuckPhp\Core\Route;

class RouteHookResourceTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookResource::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(RouteHookResource::class);
       
        
        App::G()->init([
            'is_debug'=>true,
            'path' => $path,
            'ext'=>[
                RouteHookResource::class => [
                    'path' => $path,
                    'controller_resource_prefix' =>'/RES/'
                ],
            ],
        ]);
        
        Route::PathInfo('/RES/test.txt');
        App::G()->run();

        
        Route::PathInfo('/RES/no_exist.txt');
        App::G()->run();
        
        Route::PathInfo('/RES/no_exist.php');
        App::G()->run();
        
        Route::PathInfo('/RES/../../../no_exist.php');
        App::G()->run();
        Route::PathInfo('/not_hit.php');
        App::G()->run();
        
        /////////////////////////////
        $options =[
            'is_debug'=>true,
            'path' => $path,
            'path_resource' => 'res/',
            'controller_resource_prefix' =>'DATA/',
        ];
        $path_init = $path.'public/';
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        
        $_SERVER['DOCUMENT_ROOT']=$path.'public';
        
        $options['controller_resource_prefix']= 'http://github.com/';
        RouteHookResource::G(new RouteHookResource())->init($options,App::G())->cloneResource();
        $options['controller_resource_prefix']= 'DATA/';
        RouteHookResource::G(new RouteHookResource())->init($options,App::G())->cloneResource();
        RouteHookResource::G()->cloneResource();
        $options['path_resource'] = $path.'res';
        RouteHookResource::G(new RouteHookResource())->init($options,App::G())->cloneResource();

        RouteHookResource::G()->cloneResource(true);
        
        $options['controller_url_prefix']= 'admin/';
        $options['controller_resource_prefix']= 'DATA/';
        RouteHookResource::G(new RouteHookResource())->init($options,App::G())->cloneResource(true);
        
        
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        ////////////////////////////
       //*/
        \LibCoverage\LibCoverage::End();
    }
}