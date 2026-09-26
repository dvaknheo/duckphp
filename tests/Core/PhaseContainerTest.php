<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\PhaseContainer;
use DuckPhp\Core\SingletonExTrait;

class PhaseContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(PhaseContainer::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        
        PhaseContainer::_();
        PhaseContainer::_(new MyPhaseContainer());
       
        PhaseContainer::_()->setDefaultContainer('DEFAULT');
        PhaseContainer::_()->addSharedClasses([]);
        PhaseContainer::_()->removeSharedClasses([]);
        PhaseContainer::_()->setCurrentContainer('CURRENT');
        PhaseContainer::_()->getCurrentContainer();
                PhaseContainer::RestAllContainerForTesting();
        MyObject::_()->foo();
        MyObject::_(new MyObject2())->foo();
        
        PhaseContainer::_()->setCurrentContainer('NEW');

        PhaseContainer::_()->addSharedClasses([MyObject::class =>true]);
        MyObject::_()->foo();
        MyObject::_(new MyObject2())->foo();
        MyObject2::_();
        PhaseContainer::_()->createLocalObject(MyObject::class);
        PhaseContainer::_()->removeLocalObject(MyObject::class);
        
        PhaseContainer::_()->dumpAllObject();
        PhaseContainer::GetObject(MyObject::class);
        PhaseContainer::_()->removeSharedClasses([MyObject::class]);
        
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
    use SingletonExTrait;
    public function foo()
    {
        echo "foo!";
    }
}
class MyObject2 extends MyObject
{
}