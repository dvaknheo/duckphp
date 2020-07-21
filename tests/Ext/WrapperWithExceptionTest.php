<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\WrapperWithException;
use DuckPhp\Core\SingletonEx;

class WrapperWithExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(WrapperWithException::class);
        //
        WrapperWithExceptionObject::G(WrapperWithException::Wrap(WrapperWithExceptionObject::G()));
        $x=WrapperWithExceptionObject::G()->foo();
        var_dump($x);
        WrapperWithExceptionObject::G(WrapperWithException::Release());
        try{
            WrapperWithExceptionObject::G()->foo();
        }catch(\Exception $ex){
            var_dump($ex->getMessage());
        }
        \MyCodeCoverage::G()->end();
    }
}
class WrapperWithExceptionObject
{
    use SingletonEx;
    public function foo()
    {
        throw new \Exception("HHH");
    }
}