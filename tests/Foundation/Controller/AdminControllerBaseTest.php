<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Foundation\Controller\AdminControllerBase;

class AdminControllerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(AdminControllerBase::class);

        // 成功构造：已安装 + 配置 class_user provider
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'class_admin' => FakeProviderForControllerBase::class,
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
            'class_admin' => FakeDenyProviderForControllerBase::class,
        ]);
        \DuckPhp\Core\SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        $obj = new AdminControllerBase();
        $this->assertInstanceOf(AdminControllerBase::class, $obj);

        // canAccess=false + Ajax → AdminException 分支（27 行）
        PhaseContainer::RestAllContainerForTesting();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'class_admin' => FakeDenyProviderForControllerBase::class,
        ]);
        try {
            new AdminControllerBase();
            $this->fail('expected AdminException');
        } catch (\DuckPhp\GlobalAdmin\AdminException $ex) {
        }
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        \LibCoverage\LibCoverage::End();
    }
}
class FakeDenyProviderForControllerBase
{
    use \DuckPhp\Foundation\SingletonTrait;
    public function init($options = [], $context = null)
    {
        return $this;
    }
    public function id()
    {
        return 1;
    }
    public function canAccess($class = null, $method = null, $url = null): bool
    {
        return false;
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        return '/login';
    }
}
class FakeProviderForControllerBase
{
    use \DuckPhp\Foundation\SingletonTrait;
    public function init($options = [], $context = null)
    {
        return $this;
    }
    public function id()
    {
        return 1;
    }
    public function canAccess($class = null, $method = null, $url = null): bool
    {
        return true;
    }
}
