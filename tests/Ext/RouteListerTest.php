<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Component\RouteHookRewrite;
use DuckPhp\Component\RouteHookRouteMap;
use DuckPhp\Ext\RouteLister;
use DuckPhp\DuckPhp;
use DuckPhp\Core\Route;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SystemWrapper;
use DuckPhp\Core\SingletonExTrait as SingletonExTrait;
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
            'app'=>[
                SubAppForRouteLister::class => ['name' => '@'],
            ],
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
        // with_children + 子应用（覆盖内嵌递归）
        DuckPhp::_()->options['app']['DisabledAppForRouteLister'] = false;
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
        //////////////
        // 确保 namespace_controller 设置正确，指向 Controller 目录
        Route::_()->options['namespace_controller'] = 'Controller';
        Route::_()->options['namespace'] = 'tests_Ext_RouteLister';
        // 强制重新扫描
        $all_routes = RouteLister::_()->listAll();
        // 查找 admin 和 user 控制器
        $has_admin = false;
        $has_user = false;
        foreach ($all_routes as $route) {
            if (!empty($route['controller'])) {
                if ($route['is_admin']) {
                    $has_admin = true;
                }
                if ($route['is_user']) {
                    $has_user = true;
                }
            }
        }
        $this->assertTrue($has_admin, 'Should have admin controller route');
        $this->assertTrue($has_user, 'Should have user controller route');
        // command_routes 测试 - 需要有 admin/user 路由才覆盖分支
        ob_start();
        RouteLister::_()->command_routes();
        $output = ob_get_clean();
        // 验证输出中包含 admin/user 标记
        $this->assertStringContainsString('admin', $output);
        $this->assertStringContainsString('user', $output);
        //////////////
        // getControllerPathByApp()：用一个真实 App 的文件位置 + namespace_controller 定位控制器目录
        // （从 Component 搬到 Ext 时这段丢了，导致该方法只剩“找不到目录返回 null”那半条被覆盖）
        PhaseContainer::RestAllContainerForTesting();
        \tests_Ext_RouteLister\System\RouteListerApp::_()->init(['path' => $path]);
        $controller_path = MyRouteLister::_()->getControllerPathByApp(Route::_()->getControllerNamespacePrefix());
        $this->assertSame($path.'Controller'.DIRECTORY_SEPARATOR, $controller_path);
        $this->assertDirectoryExists($controller_path);

        // prefix 以 \ 开头：namespace_controller 设成 '\' 时 getControllerNamespacePrefix() 就返回 '\'
        // → getControllerPathByApp() 直接返回 null
        Route::_()->options['namespace_controller'] = '\\';
        $this->assertNull(MyRouteLister::_()->getControllerPathByApp(Route::_()->getControllerNamespacePrefix()));
        Route::_()->options['namespace_controller'] = 'Controller';

        \LibCoverage\LibCoverage::End();
    }
}
class MyRouteLister extends RouteLister
{
    public function getControllerPathByApp($prefix)
    {
        return parent::getControllerPathByApp($prefix);
    }
}
class SubAppForRouteLister extends DuckPhp
{

}
