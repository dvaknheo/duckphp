<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\ExceptionWrapper;
use DuckPhp\SingletonEx\SingletonEx;

class ExceptionWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ExceptionWrapper::class);
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
        \MyCodeCoverage::G()->end();
    }
}
class ExceptionWrapperObject
{
    use SingletonEx;
    public function foo()
    {
        throw new \Exception("HHH");
    }
}