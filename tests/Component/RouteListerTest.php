<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Component\RouteLister;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonTrait as SingletonExTrait;
use DuckPhp\Core\AutoLoader;

class RouteListerTest extends \PHPUnit\Framework\TestCase
{
    public function adjuster($first)
    {
        return $first;
    }
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(RouteLister::class);
        $path=\LibCoverage\LibCoverage::G()->getClassTestPath(RouteLister::class);

        AutoLoader::_()->init([
            'path' => $path,
            'namespace' => 'tests_Ext_RouteLister',
            'path_namespace' => '',
        ])->run();
        DuckPhp::_()->init([
            'path'=>$path,
            'namespace'=>'tests_Ext_RouteLister',
            'controller_class_postfix'=>'Controller',
            'controller_method_prefix'=>'action_',
            'ext'=>[
                RouteHookRouteMap::class => true,
                RouteHookRewrite::class => true,
            ],
        ]);
        RouteHookRouteMap::_()->assignRoute('user/login', 'MainController@action_index');
        RouteHookRouteMap::_()->assignImportantRoute('user/important', '~MainController@action_index');
        RouteHookRouteMap::_()->assignRoute('no-arrow', 'MainController');
        RouteHookRewrite::_()->assignRewrite('a/b', 'user/login');
        // listAll 默认：顺序 rewrite_map -> route_map_important -> controller -> route_map
        $all = RouteLister::_()->listAll();
        $this->assertSame('a/b', $all[0]['url']);
        $this->assertTrue($all[0]['rewrite_map']);
        $this->assertSame('user/important', $all[1]['url']);
        $this->assertTrue($all[1]['route_map_important']);
        $last = end($all);
        $this->assertTrue($last['route_map']);
        $this->assertSame('no-arrow', $last['url']);
        $login_row = null;
        foreach ($all as $r) {
            if ($r['url'] === 'user/login') {
                $login_row = $r;
                break;
            }
        }
        $this->assertTrue($login_row['route_map']);
        $this->assertSame('MainController', $login_row['controller']);
        $this->assertSame('action_index', $login_row['method']);
        // 控制器行含 is_admin/is_user 字段
        $controller_rows = array_filter($all, function ($r) { return $r['controller'] !== ''; });
        $row = reset($controller_rows);
        $this->assertArrayHasKey('is_admin', $row);
        $this->assertArrayHasKey('is_user', $row);
        $this->assertArrayHasKey('phase', $row);
        $this->assertArrayHasKey('method', $row);

        // only_controller：排除 map 来源
        $only = RouteLister::_()->listAll(false, true);
        foreach ($only as $r) {
            $this->assertTrue($r['controller'] !== '');
            $this->assertFalse($r['rewrite_map']);
            $this->assertFalse($r['route_map']);
            $this->assertFalse($r['route_map_important']);
        }

        // only_admin / only_user 过滤
        $admins = RouteLister::_()->listAll(false, false, true);
        foreach ($admins as $r) {
            $this->assertTrue($r['is_admin']);
        }
        $users = RouteLister::_()->listAll(false, false, false, true);
        foreach ($users as $r) {
            $this->assertTrue($r['is_user']);
        }

        // only_admin && only_user 互斥异常
        try {
            RouteLister::_()->listAll(false, false, true, true);
            $this->fail('expected exception');
        } catch (\InvalidArgumentException $ex) {
        }

        // with_children
        RouteLister::_()->listAll(true);

        // pathInfoFromClassAndMethod 保留
        RouteLister::_()->pathInfoFromClassAndMethod(static::class,'testAll');
        RouteLister::_()->pathInfoFromClassAndMethod('tests_Ext_RouteLister\\Controller\Main_Notcontroller','testAll');
        RouteLister::_()->pathInfoFromClassAndMethod('tests_Ext_RouteLister\\Controller\MainController','testAll',[$this,'adjuster']);
        RouteLister::_()->pathInfoFromClassAndMethod('tests_Ext_RouteLister\\Controller\MainController','action_noexist',[$this,'adjuster']);

        Route::_()->options['controller_class_adjust']='uc_method;uc_class;uc_full_class';
        RouteLister::_()->pathInfoFromClassAndMethod('tests_Ext_RouteLister\\Controller\MainController','action_index');

        Route::_()->options['controller_class_adjust']=[];
        RouteLister::_()->pathInfoFromClassAndMethod('tests_Ext_RouteLister\\Controller\MainController','action_index');

        Route::_()->options['namespace']="NoExists";
        RouteLister::_()->listAll();

        \LibCoverage\LibCoverage::End();
    }
}
class MyRouteLister extends RouteLister
{
    //
}
