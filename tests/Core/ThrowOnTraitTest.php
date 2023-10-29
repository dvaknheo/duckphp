<?php
namespace tests\DuckPhp\Core;

use DuckPhp\Core\ThrowOnTrait;

class ThrowOnTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ThrowOnTrait::class);
        ThrowOnObject::ThrowOn(false, "123");
        try {
            ThrowOnObject::ThrowOn(true, "Message", 2);
        } catch (\Throwable $ex) {
            echo "1Done";
        }

        

        \LibCoverage\LibCoverage::End();
        /*
        ThrowOn::G()->ThrowOn($flag, $message, $code=0, $exception_class=null);
        //*/
    }
}
class ThrowOnObject extends \Exception
{
    use ThrowOnTrait;
}
