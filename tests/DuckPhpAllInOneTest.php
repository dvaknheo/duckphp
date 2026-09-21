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

        // 四层 Helper 并集：AllInOne 直接声明这些静态方法（不再用 __callStatic 魔术转发）
        DuckPhpAllInOne::Setting('path');  // 并集里的 Setting() 存在

        // 不存在的静态方法必须报错（显式方法集下是 PHP 原生 Error，不再被魔术方法吞掉）
        try {
            DuckPhpAllInOne::nonExistentMethod();
            Assert::fail('调用不存在的静态方法应该报错');
        } catch (\Error $ex) {
            Assert::assertStringContainsString('nonExistentMethod', $ex->getMessage());
        }

        \LibCoverage\LibCoverage::G($LibCoverage);
        \LibCoverage\LibCoverage::End();

    }

}
