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
            ThrowOnObject::ThrowOn(true, "Message", 2, null);
        } catch (\Throwable $ex) {
            echo "1Done";
        }
        try {
            ThrowOnObject::ThrowOn(true, "Message", 2, ThrowOnException::class);
        } catch (\Throwable $ex) {
            echo "2Done";
        }
        try {
            ThrowOnObject::ThrowOn(true, "Message", ThrowOnException::class);
        } catch (\Throwable $ex) {
            echo  get_class($ex);
            echo "3Done";
        }
        
        \MyCodeCoverage::G()->end(ThrowOn::class);
        $this->assertTrue(true);
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
