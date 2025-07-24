<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\FinderForController;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
use DuckPhp\Core\AutoLoader;

class FinderForControllerTest extends \PHPUnit\Framework\TestCase
{
    public function adjuster($first)
    {
        return $first;
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(FinderForController::class);
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(FinderForController::class);

        AutoLoader::_()->init([
            'path' => $path,
            'namespace' => 'tests_Ext_FinderForController',
            'path_namespace' => '',
        ])->run();
        DuckPhp::_()->init([
            'path'=>$path,
            'namespace'=>'tests_Ext_FinderForController',
            'controller_class_postfix'=>'Controller',
            'controller_method_prefix'=>'action_',
            
        ]);
        //*
        FinderForController::_()->getRoutePathInfoMap();
        FinderForController::_()->getRoutePathInfoMapWithChildren();
        FinderForController::_()->getAllAdminController();
        FinderForController::_()->getAllUserController();
        //////////////
        
        FinderForController::_()->pathInfoFromClassAndMethod(static::class,'testAll');
        FinderForController::_()->pathInfoFromClassAndMethod('tests_Ext_FinderForController\\Controller\Main_Notcontroller','testAll');
        FinderForController::_()->pathInfoFromClassAndMethod('tests_Ext_FinderForController\\Controller\MainController','testAll',[$this,'adjuster']);
        FinderForController::_()->pathInfoFromClassAndMethod('tests_Ext_FinderForController\\Controller\MainController','action_noexist',[$this,'adjuster']);
        //*/
        //////////////
        Route::_()->options['controller_class_adjust']='uc_method;uc_class;uc_full_class';
        FinderForController::_()->pathInfoFromClassAndMethod('tests_Ext_FinderForController\\Controller\MainController','action_index');

        Route::_()->options['controller_class_adjust']=[];
        FinderForController::_()->pathInfoFromClassAndMethod('tests_Ext_FinderForController\\Controller\MainController','action_index');
        
        Route::_()->options['namespace']="NoExists";
        FinderForController::_()->getRoutePathInfoMap();
        
        \LibCoverage\LibCoverage::End();
    }
}
class MyFinderForController extends FinderForController
{
    //
}
