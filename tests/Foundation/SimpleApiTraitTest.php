<?php 
namespace tests\DuckPhp\Foundation;
use DuckPhp\Foundation\SimpleApiTrait;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\DuckPhp;
use DuckPhp\SingletonEx\SingletonExTrait;

class SimpleApiTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        $LibCoverage = \LibCoverage\LibCoverage::G();
        \LibCoverage\LibCoverage::Begin(SimpleApiTrait::class);
        SimpleApiTraitObject::G();
        $options = [
            'ext' =>[ASESubApp::class =>[
                    'notEmpty' => true,
                ],ASESubApp2::class =>[
                    'notEmpty' => true,
                ],
            ],
        ];
        ASEMainApp::RunQuickly($options,function(){
            SimpleApiTraitObject::G();

        });
        ASEAdminAction::G();
        //ASEMainApp::Root()->getContainer()->dumpAllObject();
        var_dump(ASEMainApp::Admin()->id());
        //var_dump(ASEMainApp::User()->id());
        
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
class SimpleApiTraitObject
{
    use SimpleApiTrait;
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
    use SimpleApiTrait;
    public function __construct(){}
    public function id()
    {
        return '>>'. ASEMainApp::Phase().DATE(DATE_ATOM);
    }
}

class ASESubApp extends DuckPhp
{
    public function onInit()
    {
        $object = new PhaseProxy(static::class, ASEAdminAction::class);
        static::PhaseCall(get_class(static::Root()),function()use($object){
            static::Root()::Admin($object);
        });
       
        static::Admin(ASEAdminAction::G());
        /*
        static::Phase(static::Root()::Phase());
        static::Root()::User($this->createPhaseProxy(ASEUserAction::class, false));
        static::Phase(static::class);
        static::User(ASEUserAction::G());
        */
        var_dump("Installed");

    }
}
class ASESubApp2 extends ASESubApp
{
}
class ASEAdminAction
{
    public static $AppClass = ASESubApp::class;
    use SimpleApiTrait;
    public function id()
    {
        return '>>'. ASEMainApp::Phase().DATE(DATE_ATOM);
    }
}
class ASEUserAction
{
    public static $AppClass = ASESubApp::class;

    use SimpleApiTrait;
    public function __construct(){}
    public function id()
    {
        return '>>'. ASEMainApp::Phase().DATE(DATE_ATOM);
    }
}