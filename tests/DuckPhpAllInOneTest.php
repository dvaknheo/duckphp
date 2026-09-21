<?php
namespace tests\DuckPhp;

use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Foundation\Helper;
use PHPUnit\Framework\Assert;

class DuckPhpAllInOneTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DuckPhpAllInOne::class);

        Assert::assertTrue(true);
        Assert::assertTrue(true);

        $LibCoverage = \LibCoverage\LibCoverage::G();

        DuckPhpAllInOne::RunQuickly(['cli_enable'=>false]);

        // onPrepare 效果：cmd 含自身类（cli_command_with_app=true 效果）
        $cmd = DuckPhpAllInOne::_()->options['cmd'] ?? [];
        Assert::assertArrayHasKey(DuckPhpAllInOne::class, $cmd);

        // Show() callable view 渲染（view_index + head/foot）
        ob_start();
        DuckPhpAllInOne::Show([], 'index');
        $out = ob_get_clean();
        Assert::assertStringContainsString('<html>', $out);
        Assert::assertStringContainsString('main page work at', $out);
        Assert::assertStringContainsString('</html>', $out);

        // Show() 非 callable view 回退 parent::Show
        $level = ob_get_level();
        try {
            ob_start();
            DuckPhpAllInOne::Show([], 'no_such_view');
            ob_get_clean();
        } catch (\Throwable $ex) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        // __callStatic 测试：调用 Helper 的静态方法
        DuckPhpAllInOne::Setting('path');  // Helper::Setting() 存在

        // __callStatic 测试：调用不存在的静态方法应该报错
        $errorTriggered = false;
        set_error_handler(function ($errno, $errstr) use (&$errorTriggered) {
            if (strpos($errstr, 'Call to undefined method') !== false) {
                $errorTriggered = true;
            }
            return true;
        });
        DuckPhpAllInOne::nonExistentMethod();
        restore_error_handler();
        Assert::assertTrue($errorTriggered, 'Should trigger error for non-existent method');

        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();

    }

}
