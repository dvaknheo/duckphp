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
        PhaseContainer::GetContainer();
        PhaseContainer::ReplaceSingletonImplement();
        PhaseContainer::ReplaceSingletonImplement();
        //PhaseContainer::GetObject();
        
        PhaseContainer::GetContainerInstanceEx();
        PhaseContainer::GetContainerInstanceEx(new MyPhaseContainer());
               PhaseContainer::ReplaceSingletonImplement();

       
        PhaseContainer::GetContainerInstanceEx()->setDefaultContainer('DEFAULT');
        PhaseContainer::GetContainerInstanceEx()->addPublicClasses([]);
        PhaseContainer::GetContainerInstanceEx()->removePublicClasses([]);
        PhaseContainer::GetContainerInstanceEx()->setCurrentContainer('CURRENT');
        PhaseContainer::GetContainerInstanceEx()->getCurrentContainer();
        
        MyObject::_()->foo();
        MyObject::_(new MyObject2())->foo();
        
        PhaseContainer::GetContainerInstanceEx()->setCurrentContainer('NEW');

        PhaseContainer::GetContainerInstanceEx()->addPublicClasses([MyObject::class]);
        MyObject::_()->foo();
        MyObject::_(new MyObject2())->foo();
        MyObject2::_();
        PhaseContainer::GetContainerInstanceEx()->dumpAllObject();
        PhaseContainer::GetObject(MyObject::class);
        PhaseContainer::GetContainerInstanceEx()->removePublicClasses([MyObject::class]);
        
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