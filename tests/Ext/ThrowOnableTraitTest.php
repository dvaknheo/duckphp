<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\ThrowOnableTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

class ThrowOnableTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ThrowOnableTrait::class);
        ThrowOnObject::ThrowOn(false, "123");
        ThrowOnObject::G()->setExceptionClass(BaseException::class);
        try {
            ThrowOnObject::ThrowOn(true, "Message", 2);
        } catch (\Throwable $ex) {
            echo ThrowOnObject::ExceptionClass();
        }
        
        \LibCoverage\LibCoverage::End();
    }
}
class BaseException extends \Exception
{
}
class ThrowOnObject
{
    use SingletonExTrait;
    use ThrowOnableTrait;
}