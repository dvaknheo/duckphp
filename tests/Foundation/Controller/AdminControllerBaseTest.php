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
            'class_user' => FakeProviderForControllerBase::class,
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
        \LibCoverage\LibCoverage::End();
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
    public function checkAccess($class = null, $method = null, $url = null)
    {
        return;
    }
}
