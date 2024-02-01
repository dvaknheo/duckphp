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
       
        
        App::_()->init([
            'cli_enable' =>false,
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
        App::_()->run();

        
        Route::PathInfo('/RES/no_exist.txt');
        App::_()->run();
        
        Route::PathInfo('/RES/no_exist.php');
        App::_()->run();
        
        Route::PathInfo('/RES/../../../no_exist.php');
        App::_()->run();
        Route::PathInfo('/not_hit.php');
        App::_()->run();
        
        /////////////////////////////
        $options =[
            'cli_enable' =>false,
            'is_debug'=>true,
            'path' => $path,
            'path_resource' => 'res/',
            'controller_resource_prefix' =>'DATA/',
        ];
        $path_init = $path.'public/';
        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        
        $_SERVER['DOCUMENT_ROOT']=$path.'public';
        
        $options['controller_resource_prefix']= 'http://github.com/';
        RouteHookResource::_(new RouteHookResource())->init($options,App::_())->cloneResource();
        $options['controller_resource_prefix']= 'DATA/';
        RouteHookResource::_(new RouteHookResource())->init($options,App::_())->cloneResource();
        RouteHookResource::_()->cloneResource();
        $options['path_resource'] = $path.'res';
        RouteHookResource::_(new RouteHookResource())->init($options,App::_())->cloneResource();

        RouteHookResource::_()->cloneResource(true);
        
        $options['controller_url_prefix']= 'admin/';
        $options['controller_resource_prefix']= 'DATA/';
        RouteHookResource::_(new RouteHookResource())->init($options,App::_())->cloneResource(true);
        
        $options['controller_resource_prefix']= '';
        RouteHookResource::_(new RouteHookResource())->init($options,App::_())->cloneResource(true);

        \LibCoverage\LibCoverage::G()->cleanDirectory($path_init);
        ////////////////////////////
       //*/
        \LibCoverage\LibCoverage::End();
    }
}