<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\Helper;
use PHPUnit\Framework\Assert;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);

        Helper::Setting('path');  // Helper::Setting() 存在

        // __callStatic 测试：调用不存在的静态方法应该报错
        $errorTriggered = false;
        set_error_handler(function ($errno, $errstr) use (&$errorTriggered) {
            if (strpos($errstr, 'Call to undefined method') !== false) {
                $errorTriggered = true;
            }
            return true;
        });
        Helper::nonExistentMethod();
        restore_error_handler();
        Assert::assertTrue($errorTriggered, 'Should trigger error for non-existent method');

        \LibCoverage\LibCoverage::End();

    }

}
