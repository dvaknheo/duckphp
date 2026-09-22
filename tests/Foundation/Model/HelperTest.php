<?php
namespace tests\DuckPhp\Foundation\Model;

use DuckPhp\Foundation\Model\Base;
use DuckPhp\Foundation\Model\ModelHelper as Helper;
use DuckPhp\Foundation\Model\ModelHelperTrait;
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
     * Model\Base 的 6 个数据层静态助手由 `DuckPhp\Foundation\ModelHelperTrait` 提供（真方法，
     * 不是并集类那种 `__callStatic` 魔术），因此 `Base::Db()` 与 `$model->Db()` 都可用。
     */
    public function testModelBaseHelpersAreRealMethods()
    {
        $names = ['Db', 'DbForRead', 'DbForWrite', 'SqlForPager', 'SqlForCountSimply', 'DatabaseDriver'];
        $model = new class extends Base {
        };
        foreach ($names as $name) {
            $m = new \ReflectionMethod(Base::class, $name);
            Assert::assertSame(Base::class, $m->getDeclaringClass()->getName(), "Model\\Base::$name 应由 ModelHelperTrait 提供（不是魔术转发）");
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

    /**
     * 覆盖测试：`ModelHelperTrait` 是独立文件，必须单独 Begin/End 才会有它的覆盖率 dump
     * （模型侧的 ModelHelper/Base 用的是同一个 trait，方法体只在这一个文件里）。
     */
    public function testModelHelperTraitCoverage()
    {
        \LibCoverage\LibCoverage::Begin(ModelHelperTrait::class);

        $sql = 'Select * from users';
        try {
            TraitOnlyModelHelper::SqlForPager($sql, 1, 5);
        } catch (\Throwable $ex) {
        }
        try {
            TraitOnlyModelHelper::SqlForCountSimply($sql);
        } catch (\Throwable $ex) {
        }
        try {
            TraitOnlyModelHelper::Db();
        } catch (\Throwable $ex) {
        }
        try {
            TraitOnlyModelHelper::DbForRead();
        } catch (\Throwable $ex) {
        }
        try {
            TraitOnlyModelHelper::DbForWrite();
        } catch (\Throwable $ex) {
        }
        try {
            TraitOnlyModelHelper::DatabaseDriver();
        } catch (\Throwable $ex) {
        }

        \LibCoverage\LibCoverage::End();
    }
}

class TraitOnlyModelHelper
{
    use ModelHelperTrait;
}
