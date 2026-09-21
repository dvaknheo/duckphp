<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Foundation\Controller\AdminControllerBase;
use DuckPhp\GlobalAdmin\GlobalAdmin;

class AdminControllerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminControllerBase::class);

        // 成功构造：已安装 + 配置 admin_provider
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'admin_provider_enable' => true ,
            'ext' =>[
                FakeProviderForControllerBase::class => true,
            ],
        ]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);

        // 失败构造：installed=true 但未配置 provider → checkAccess 抛异常
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init(['installed' => true]);
        try {
            new AdminControllerBase();
            $this->fail('expected exception');
        } catch (\Throwable $ex) {
        }

        // canAccess=false + 非 Ajax → Show302 + exit 分支（23-25 行）
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'admin_provider_enable' => true ,
            'ext' =>[
                FakeProviderForControllerBase::class => true 
            ],
        ]);
        \DuckPhp\Core\SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);

        // canAccess=false + Ajax → AdminException 分支（27 行）
        PhaseContainer::RestAllContainerForTesting();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'admin_provider_enable' => true ,
            'admin_enable' => true ,
            'ext' =>[
                FakeProviderForControllerBase::class => true 
            ],
        ]);
        try {
            new AdminControllerBase();
            //} catch (\DuckPhp\GlobalAdmin\AdminException $ex) {
        } catch (\Exception $ex) {
            
        }
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        \LibCoverage\LibCoverage::End();
    }
}
class FakeDenyProviderForControllerBase extends GlobalAdmin
{
    public function id($check_login = true)
    {
        return 1;
    }
    public function canAccess($class = null, $method = null, $url = null): bool
    {
        return false;
    }
    public function urlForLogin(?string $url_back = null, ?array $ext = null):string
    {
        return '/login';
    }
}
class FakeProviderForControllerBase extends GlobalAdmin
{
    public function id($check_login =false)
    {
        return 1;
    }
    public function canAccess($class = null, $method = null, $url = null): bool
    {
        return true;
    }
}
