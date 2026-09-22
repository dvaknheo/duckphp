<?php
namespace tests\DuckPhp\Foundation\Model;

use DuckPhp\Foundation\Model\Base;
use DuckPhp\Foundation\Model\Helper;
use PHPUnit\Framework\Assert;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);

        try{
        $sql="Select * from users";
        Helper::SqlForPager($sql,1,5);
        }catch(\Throwable $ex){}
        try{
        Helper::SqlForCountSimply($sql);
        }catch(\Throwable $ex){}
        try{
            Helper::Db();
        }catch(\Throwable $ex){}
        try{
            Helper::DbForRead();
        }catch(\Throwable $ex){}
        try{
            Helper::DbForWrite();
        }catch(\Throwable $ex){}
        try{
            Helper::DatabaseDriver();
        }catch(\Throwable $ex){}
        \LibCoverage\LibCoverage::End();
        /*
        //*/
    }
    /**
     * Model\Base 的 6 个数据层静态助手必须是**显式声明**：
     * 并集类走 __callStatic 魔术，但模型基类要保持 `$model->Db()`（静态方法经实例调用）可用。
     */
    public function testModelBaseDeclaresHelpersExplicitly()
    {
        $names = ['Db', 'DbForRead', 'DbForWrite', 'SqlForPager', 'SqlForCountSimply', 'DatabaseDriver'];
        $model = new class extends Base {
        };
        foreach ($names as $name) {
            $m = new \ReflectionMethod(Base::class, $name);
            Assert::assertSame(Base::class, $m->getDeclaringClass()->getName(), "Model\\Base::$name 应为显式声明（不是魔术转发）");
            Assert::assertTrue($m->isStatic(), "Model\\Base::$name 应为 static");
            Assert::assertTrue(is_callable([$model, $name]), "Model\\Base::$name 应可经实例调用（\$model->$name()）");
        }
        // 实例式调用走通（无 DB 环境下允许抛异常，但不能再是「方法不存在」）
        try {
            $model->Db();
        } catch (\Throwable $ex) {
        }
        try {
            $model->DbForRead();
        } catch (\Throwable $ex) {
        }
        try {
            $model->DbForWrite();
        } catch (\Throwable $ex) {
        }
        try {
            $model->SqlForPager('SELECT 1', 1, 5);
        } catch (\Throwable $ex) {
        }
        try {
            $model->SqlForCountSimply('SELECT 1');
        } catch (\Throwable $ex) {
        }
        try {
            $model->DatabaseDriver();
        } catch (\Throwable $ex) {
        }
    }
}
