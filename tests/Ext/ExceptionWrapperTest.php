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
        ExceptionWrapperObject::G(ExceptionWrapper::Wrap(ExceptionWrapperObject::G()));
        $x=ExceptionWrapperObject::G()->foo();
        var_dump($x);
        ExceptionWrapperObject::G(ExceptionWrapper::Release());
        try{
            ExceptionWrapperObject::G()->foo();
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