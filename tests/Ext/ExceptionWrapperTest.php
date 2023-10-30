<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\ExceptionWrapper;
use DuckPhp\SingletonEx\SingletonExTrait;

class ExceptionWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExceptionWrapper::class);
        //
        ExceptionWrapperObject::_(ExceptionWrapper::Wrap(ExceptionWrapperObject::_()));
        $x=ExceptionWrapperObject::_()->foo();
        var_dump($x);
        ExceptionWrapperObject::_(ExceptionWrapper::Release());
        try{
            ExceptionWrapperObject::_()->foo();
        }catch(\Exception $ex){
            var_dump($ex->getMessage());
        }
        \LibCoverage\LibCoverage::End();
    }
}
class ExceptionWrapperObject
{
    use SingletonExTrait;
    public function foo()
    {
        throw new \Exception("HHH");
    }
}