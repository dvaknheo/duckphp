<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Ext\RouteHookResource;
use DuckPhp\Core\App;

class RouteHookResourceTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteHookResource::class);
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(RouteHookResource::class);
        
        App::G()->init([
            'path' => $path,
            'ext'=>[
                RouteHookResource::class => [
                    'path' => $path,
                    'controller_resource_prefix' =>'/RES/'
                ],
            ],
        ]);
        
        App::Route()::PathInfo('/RES/test.txt');
        App::G()->run();
        
        App::Route()::PathInfo('/RES/no_exist.txt');
        App::G()->run();
        
        App::Route()::PathInfo('/RES/no_exist.php');
        App::G()->run();
        
        App::Route()::PathInfo('/RES/../../../no_exist.php');
        App::G()->run();
        App::Route()::PathInfo('/not_hit.php');
        App::G()->run();
        \LibCoverage\LibCoverage::End();
    }
}