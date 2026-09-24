<?php
namespace tests\DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\DuckPhpSystemException;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\DuckPhp;
use DuckPhp\GlobalAdmin\Admin;
use DuckPhp\GlobalAdmin\AdminServiceInterface;

class AdminTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Admin::class);

        ////////////////////////////////////////////////////////////////////
        // 1) 桩类本体：没装配 provider 时，每个入口都该抛 DuckPhpSystemException
        ////////////////////////////////////////////////////////////////////
        $admin = new Admin();
        $stubs = [
            ['id',           [true], 'No GlobalAdmin Provider.', -1],
            ['name',         [true], 'No GlobalAdmin Provider.', -2],
            ['data',         [true], 'Need Provider',            -1],
            ['urlForHome',   [],     'Need Provider',            -1],
            ['urlForLogin',  [null], 'Need Provider',            -1],
            ['urlForLogout', [],     'Need Provider',            -1],
        ];
        foreach ($stubs as $case) {
            [$method, $args, $message, $code] = $case;
            try {
                $admin->$method(...$args);
                $this->fail("Admin::$method() 在未装配 provider 时应抛异常");
            } catch (DuckPhpSystemException $ex) {
                $this->assertSame($message, $ex->getMessage());
                $this->assertSame($code, $ex->getCode());
            }
        }

        // 经 localService() 的四个入口：同样是 Need Provider
        $via_local_service = [
            ['service',   []],
            ['canAccess', []],
            ['log',       ['msg']],
            ['isSuper',   []],
        ];
        foreach ($via_local_service as $case) {
            [$method, $args] = $case;
            try {
                $admin->$method(...$args);
                $this->fail("Admin::$method() 在未装配 provider 时应抛异常");
            } catch (DuckPhpSystemException $ex) {
                $this->assertSame('Need Provider', $ex->getMessage());
            }
        }

        // mergeViewData() 第一行就调 id(true) → 也抛
        try {
            $admin->mergeViewData([]);
            $this->fail('mergeViewData() 在未装配 provider 时应抛异常');
        } catch (DuckPhpSystemException $ex) {
            $this->assertSame('No GlobalAdmin Provider.', $ex->getMessage());
        }

        ////////////////////////////////////////////////////////////////////
        // 2) 装配好的子类：mergeViewData / service / canAccess / log / isSuper 的正常分支
        ////////////////////////////////////////////////////////////////////
        $path = \LibCoverage\LibCoverage::G()->getClassTestPath(DuckPhp::class);
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_()->init(['path' => $path]);   // service() 里要读当前 Phase 名

        $service = new AdminStub_Service();
        $my = new AdminStub_Admin();
        $my->service = $service;

        // mergeViewData()：五个 __logined_* 字段都来自被覆盖的取值方法
        $data = $my->mergeViewData(['A' => 'b']);
        $this->assertSame('b', $data['A']);
        $this->assertSame(1, $data['__logined_id']);
        $this->assertSame('admin', $data['__logined_name']);
        $this->assertSame(['id' => 1], $data['__logined_data']);
        $this->assertSame('/home', $data['__logined_url_home']);
        $this->assertSame('/logout', $data['__logined_url_logout']);

        // service()：包成当前 Phase 的 PhaseProxy，方法仍转发到真身（这里真身就是 service）
        $proxy = $my->service();
        $this->assertInstanceOf(PhaseProxy::class, $proxy);
        $this->assertSame($service, $proxy->self());
        $this->assertTrue($proxy->isSuper(1));

        // canAccess()/log()/isSuper()：把 id 与参数原样打给底层 service
        $this->assertTrue($my->canAccess('/u', 'C', 'm'));
        $this->assertSame([1, '/u', 'C', 'm'], $service->last_can_access);
        $this->assertSame('logged', $my->log('msg', 'type', ['k' => 'v']));
        $this->assertSame([1, 'msg', 'type', ['k' => 'v']], $service->last_log);
        $this->assertTrue($my->isSuper());
        $this->assertSame(1, $service->last_is_super);

        PhaseContainer::RestAllContainerForTesting();
        \LibCoverage\LibCoverage::End();
    }
}

/**
 * 把 Admin 的取值入口与 localService() 都接上，用来跑通父类的组合方法。
 * 名字带 AdminStub 前缀：同目录/同命名空间下的假类不能重名（PHPUnit 收集阶段会一起载入）。
 */
class AdminStub_Admin extends Admin
{
    public $service = null;

    public function id(bool $check_login = true)
    {
        return 1;
    }
    public function name(bool $check_login = true): string
    {
        return 'admin';
    }
    public function data(bool $check_login = true): array
    {
        return ['id' => 1];
    }
    public function urlForHome(): string
    {
        return '/home';
    }
    public function urlForLogin(?string $url_back = null): string
    {
        return '/login';
    }
    public function urlForLogout(): string
    {
        return '/logout';
    }
    /** 父类是 protected，这里放宽成 public 只是方便测试注入 */
    public function localService()
    {
        return $this->service;
    }
}

class AdminStub_Service implements AdminServiceInterface
{
    public $last_can_access = null;
    public $last_log = null;
    public $last_is_super = null;

    public function canAccess($admin_id, $url, $class, $method): bool
    {
        $this->last_can_access = [$admin_id, $url, $class, $method];
        return true;
    }
    public function log($admin_id, string $string, ?string $type = null, array $ext = [])
    {
        $this->last_log = [$admin_id, $string, $type, $ext];
        return 'logged';
    }
    public function isSuper($admin_id): bool
    {
        $this->last_is_super = $admin_id;
        return true;
    }
}
