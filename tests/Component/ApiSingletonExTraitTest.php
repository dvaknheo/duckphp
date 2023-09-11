<?php 
namespace tests\DuckPhp\Component;
use DuckPhp\Component\ApiSingletonExTrait;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;

class ApiSingletonExTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(ApiSingletonExTrait::class);
        ApiSingletonExTraitObject::G();
        $options = [
            'ext' =>[ASESubApp::class =>[
                    'notEmpty' => true,
                ],ASESubApp2::class =>[
                    'notEmpty' => true,
                ],
            ],
        ];
        ASEMainApp::RunQuickly($options,function(){
            ApiSingletonExTraitObject::G();
           // ASEMainAdminAction::G();
            
        });
        ASEAdminAction::G();
        $phase =ASEMainApp::Root()::Phase();
        var_dump(ASEMainApp::Admin()->id());
        var_dump(ASEMainApp::User()->id());
        
        ASEAdminAction::CallInPhase(ASESubApp2::class);
        
        /////////////
        ASEMainApp2::RunQuickly($options,function(){
            ASEMainAdminAction::G();
            // remark  static::$AppClass must be same Class
        });
        
        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class ApiSingletonExTraitObject
{
    use ApiSingletonExTrait;
}
class ASEMainApp extends DuckPhp
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
class ASEMainApp2 extends ASEMainApp
{
}
class ASEMainAdminAction
{
    public static $AppClass = ASEMainApp::class;
    use ApiSingletonExTrait;
    public function id()
    {
        return '>>'. ASEMainApp::Phase().DATE(DATE_ATOM);
    }
}

class ASESubApp extends DuckPhp
{
    public function onInit()
    {
        static::Root()::Admin(PhaseProxy::CreatePhaseProxy(static::class, ASEAdminAction::class, true));
        static::Root()::User(PhaseProxy::CreatePhaseProxy(static::class, ASEUserAction::class, false));
    }
}
class ASESubApp2 extends ASESubApp
{
}
class ASEAdminAction
{
    public static $AppClass = ASESubApp::class;
    use ApiSingletonExTrait;
    public function id()
    {
        return '>>'. ASEMainApp::Phase().DATE(DATE_ATOM);
    }
}
class ASEUserAction
{
    public static $AppClass = ASESubApp::class;

    use ApiSingletonExTrait;
    public function id()
    {
        return '>>'. ASEMainApp::Phase().DATE(DATE_ATOM);
    }
}