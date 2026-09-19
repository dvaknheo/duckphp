<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\ExceptionTrait;

class ExceptionTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ExceptionTrait::class);
        \LibCoverage\LibCoverage::End();
    }
    /**
     * 模板骨架与 demo 的“工程异常基类”必须真的是 Throwable。
     *
     * 它们曾经只 `use ExceptionTrait`（该 trait 只带来 ThrowOnTrait）而**没有** `extends \Exception`，
     * 于是 `Helper::BusinessThrowOn()` 抛它时直接致命错误（Cannot throw objects that do not implement Throwable）。
     * 这里直接加载真实文件断言，避免模板再次退化。
     */
    public function testProjectExceptionClassesAreThrowable()
    {
        $root = dirname(__DIR__, 2);
        $targets = [
            $root . '/skeleton/src/System/ProjectException.php' => 'YourProjectName\\System\\ProjectException',
            $root . '/demo/src/System/ProjectException.php' => 'ProjectNameTemplate\\System\\ProjectException',
        ];
        foreach ($targets as $file => $class) {
            $this->assertFileExists($file);
            require_once $file;
            $this->assertTrue(is_subclass_of($class, \Throwable::class), $class . ' must be throwable');

            $caught = null;
            try {
                throw new $class('boom');
            } catch (\Throwable $ex) {
                $caught = $ex;
            }
            $this->assertInstanceOf($class, $caught);
            $this->assertSame('boom', $caught->getMessage());
        }
    }
}
