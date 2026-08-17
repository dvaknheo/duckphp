<?php
namespace tests\DuckPhp\Foundation\Controller;

use DuckPhp\DuckPhp;
use DuckPhp\Core\PhaseContainer;
use DuckPhp\Foundation\Controller\UserControllerBase;

class UserControllerBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(UserControllerBase::class);

        // 成功构造：已安装 + 配置 user_provider
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'user_provider' => FakeUserProviderForControllerBase::class,
        ]);
        $obj = new UserControllerBase();
        $this->assertInstanceOf(UserControllerBase::class, $obj);

        // 失败构造：installed=true 但未配置 provider → checkAccess 抛异常
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init(['installed' => true]);
        try {
            new UserControllerBase();
            $this->fail('expected exception');
        } catch (\Throwable $ex) {
        }

        // canAccess=false + 非 Ajax → Show302 + exit 分支（23-25 行）
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'user_provider' => FakeUserDenyProviderForControllerBase::class,
        ]);
        \DuckPhp\Core\SystemWrapper::system_wrapper_replace(['exit' => function () {}]);
        $obj = new UserControllerBase();
        $this->assertInstanceOf(UserControllerBase::class, $obj);

        // canAccess=false + Ajax → UserException 分支（27 行）
        PhaseContainer::RestAllContainerForTesting();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'user_provider' => FakeUserDenyProviderForControllerBase::class,
        ]);
        try {
            new UserControllerBase();
            $this->fail('expected UserException');
        } catch (\DuckPhp\GlobalUser\UserException $ex) {
        }
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        \LibCoverage\LibCoverage::End();
    }
}
class FakeUserDenyProviderForControllerBase
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
class FakeUserProviderForControllerBase
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
