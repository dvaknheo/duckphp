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
        $options =[
            'notEmpty' => true,
        ];
        PhaseProxyMainApp::RunQuickly($options);
        
        
        $object = PhaseProxy::CreatePhaseProxy(PhaseProxyMainApp::class, PhaseProxyAdminAction::class, true);
        $object->id();
        
        $options = [
            'ext' =>[PhaseProxySubApp::class =>[
                    'notEmpty' => true,
                ],PhaseProxySubApp2::class =>[
                    'notEmpty' => true,
                ],
            ],
        ];
        PhaseProxyMainApp::RunQuickly($options);
        $phase =PhaseProxyMainApp::Root()::Phase();
        var_dump(PhaseProxyMainApp::Admin()->id());
        var_dump(PhaseProxyMainApp::User()->id());
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class PhaseProxyMainApp extends DuckPhp
{
    public function run(): bool
    {
        $this->_Phase(static::class);
        if (!$this->isChild) {
            (self::class)::G($this);
        }
        
        var_dump( date(DATE_ATOM));
        return true;
    }
}
class PhaseProxySubApp extends DuckPhp
{
    protected function proxySingletonExToRoot($class)
    {
        $phase = static::Phase();
        static::Phase(get_class(static::Root()));
        $object = $class::G(PhaseProxy::CreatePhaseProxy(static::class,$class));
        static::Phase($phase);
        return $object;
    }

    public function onInit()
    {
        $object = $this->proxySingletonExToRoot(PhaseProxyAdminAction::class);
        static::Root()::Admin($object);
        static::Root()::User(PhaseProxy::CreatePhaseProxy(static::class, PhaseProxyUserAction::class, false));
    }
}
class PhaseProxySubApp2 extends PhaseProxySubApp
{
}
class PhaseProxyAdminAction
{
    public static $AppClass = SubApp::class;
    use SingletonExTrait;
    public function id()
    {
        return '>>'. PhaseProxyMainApp::Phase().DATE(DATE_ATOM);
    }
}
class PhaseProxyUserAction
{
    public static $AppClass = PhaseProxySubApp::class;

    use SingletonExTrait;
    public function id()
    {
        return '>>'. PhaseProxyMainApp::Phase().DATE(DATE_ATOM);
    }
}