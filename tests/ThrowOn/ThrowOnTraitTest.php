<?php
namespace tests\DuckPhp\ThrowOn;

use DuckPhp\ThrowOn\ThrowOnTrait;

class ThrowOnTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ThrowOnTrait::class);
        ThrowOnObject::ThrowOn(false, "123");
        try {
            ThrowOnObject::ThrowOn(true, "Message", 2);
        } catch (\Throwable $ex) {
            echo "1Done";
        }
        try {
            ThrowOnObject2::Handle(ThrowOnObject::class);
            ThrowOnObject::ThrowOn(true, "Message", 2, ThrowOnException::class);
        } catch (\Throwable $ex) {
            try{
                ThrowOnObject::Proxy($ex);
            }catch(ThrowOnObject $ex){
                echo "2Done";
            }
        }
        

        \MyCodeCoverage::G()->end();
        /*
        ThrowOn::G()->ThrowOn($flag, $message, $code=0, $exception_class=null);
        //*/
    }
}
class ThrowOnObject extends \Exception
{
    use ThrowOnTrait;
}
class ThrowOnException extends \Exception
{
}
class ThrowOnObject2 extends ThrowOnObject
{
}