<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ModelHelperTrait;

class ModelHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(ModelHelperTrait::class);

        try{
        $sql="Select * from users";
        ModelHelper::SqlForPager($sql,1,5);
        }catch(\Throwable $ex){}
        try{
        ModelHelper::SqlForCountSimply($sql);
        }catch(\Throwable $ex){}
        try{
            ModelHelper::DB();
        }catch(\Throwable $ex){}
        try{
            ModelHelper::DbForRead();
        }catch(\Throwable $ex){}
        try{
            ModelHelper::DbForWrite();
        }catch(\Throwable $ex){}
        \LibCoverage\LibCoverage::End();
        /*
        //*/
    }
}
class ModelHelper
{
    use ModelHelperTrait;
}