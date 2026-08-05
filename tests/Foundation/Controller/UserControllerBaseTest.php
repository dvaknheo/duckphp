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

        // 成功构造：已安装 + 配置 class_user provider
        PhaseContainer::RestAllContainerForTesting();
        DuckPhp::_(new DuckPhp())->init([
            'installed' => true,
            'class_user' => FakeUserProviderForControllerBase::class,
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
        \LibCoverage\LibCoverage::End();
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
    public function checkAccess($class = null, $method = null, $url = null)
    {
        return;
    }
}
