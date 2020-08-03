<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\ThrowOn;

class ThrowOnTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ThrowOn::class);
        ThrowOnObject::ThrowOn(false, "123");
        try {
            ThrowOnObject::ThrowOn(true, "Message", 2);
        } catch (\Throwable $ex) {
            echo "1Done";
        }
        try {
            ThrowOnObject::ThrowTo(ThrowOnObject2::class);
            ThrowOnObject::ThrowOn(true, "Message", 2, ThrowOnException::class);
        } catch (\Throwable $ex) {
            echo "2Done";
        }
        

        \MyCodeCoverage::G()->end();
        /*
        ThrowOn::G()->ThrowOn($flag, $message, $code=0, $exception_class=null);
        //*/
    }
}
class ThrowOnObject
{
    use ThrowOn;
}
class ThrowOnException extends \Exception
{
}
class ThrowOnObject2 extends ThrowOnObject
{
}