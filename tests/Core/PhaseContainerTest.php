<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\PhaseContainer;
use DuckPhp\SingletonEx\SingletonExTrait;

class PhaseContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(PhaseContainer::class);
        $LibCoverage = \LibCoverage\LibCoverage::G();
        
        PhaseContainer::ReplaceSingletonImplement();
        PhaseContainer::ReplaceSingletonImplement();
        //PhaseContainer::GetObject();
        
        PhaseContainer::GetContainerInstanceEx();
        PhaseContainer::GetContainerInstanceEx(new MyPhaseContainer());
       
        PhaseContainer::GetContainerInstanceEx()->setDefaultContainer('DEFAULT');
        PhaseContainer::GetContainerInstanceEx()->addPublicClasses([]);
        PhaseContainer::GetContainerInstanceEx()->removePublicClasses([]);
        PhaseContainer::GetContainerInstanceEx()->setCurrentContainer('CURRENT');
        PhaseContainer::GetContainerInstanceEx()->getCurrentContainer();
        
        MyObject::G()->foo();
        MyObject::G(new MyObject2())->foo();
        
        PhaseContainer::GetContainerInstanceEx()->setCurrentContainer('NEW');

        PhaseContainer::GetContainerInstanceEx()->addPublicClasses([MyObject::class]);
        MyObject::G()->foo();
        MyObject::G(new MyObject2())->foo();
        
        PhaseContainer::GetContainerInstanceEx()->dumpAllObject();
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