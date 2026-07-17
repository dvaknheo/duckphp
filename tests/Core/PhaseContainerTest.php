<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SingletonTrait;

class PhaseContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(PhaseContainer::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        
        PhaseContainer::_();
        PhaseContainer::_(new MyPhaseContainer());
       
        PhaseContainer::_()->setDefaultContainer('DEFAULT');
        PhaseContainer::_()->addPublicClasses([]);
        PhaseContainer::_()->removePublicClasses([]);
        PhaseContainer::_()->setCurrentContainer('CURRENT');
        PhaseContainer::_()->getCurrentContainer();
        
        MyObject::_()->foo();
        MyObject::_(new MyObject2())->foo();
        
        PhaseContainer::_()->setCurrentContainer('NEW');

        PhaseContainer::_()->addPublicClasses([MyObject::class]);
        MyObject::_()->foo();
        MyObject::_(new MyObject2())->foo();
        MyObject2::_();
        PhaseContainer::_()->createLocalObject(MyObject::class);
        PhaseContainer::_()->removeLocalObject(MyObject::class);
        
        PhaseContainer::_()->dumpAllObject();
        PhaseContainer::GetObject(MyObject::class);
        PhaseContainer::_()->removePublicClasses([MyObject::class]);
        
        PhaseContainer::_()->issetContainer("JustPhase");
        PhaseContainer::_()->getClassOfContainer(MyObject::class);
        PhaseContainer::Dump();
        
        
                PhaseContainer::RestAllContainerForTesting();

        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();
    }
}
class MyPhaseContainer extends PhaseContainer
{

}
class MyObject
{
    use SingletonTrait;
    public function foo()
    {
        echo "foo!";
    }
}
class MyObject2 extends MyObject
{
}