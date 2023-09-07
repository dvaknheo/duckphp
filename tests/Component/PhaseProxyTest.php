<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\ApiSingletonExTrait;
use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;

class PhaseProxyTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(PhaseProxy::class);
        
        $options = [
            'ext' =>[SubApp::class =>[
                    'notEmpty' => true,
                ],
            ],
        ];
        MainApp::G()->RunQuickly($options);
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class MainApp extends DuckPhp
{
    public function run(): bool
    {
        $this->_Phase(static::class);
        if (!$this->isChild) {
            (self::class)::G($this);
        }
        
        var_dump( static::Admin()->id());
        return true;
    }
}
class SubApp extends DuckPhp
{
    public function onInit()
    {
        static::Root()::Admin(PhaseProxy::CreatePhaseProxy(static::class, AdminAction::class, true));
        static::Root()::User(PhaseProxy::CreatePhaseProxy(static::class, UserAction::class, false));
    }
}
class AdminAction
{
    public static $AppClass = SubApp::class;
    use ApiSingletonExTrait;
    public function id()
    {
        return DATE(DATE_ATOM);
    }
}
class UserAction
{
    public static $AppClass = SubApp::class;

    use ApiSingletonExTrait;
    public function id()
    {
        return DATE(DATE_ATOM);
    }
}