<?php
namespace tests\DuckPhp\Foundation\Model;

use DuckPhp\Foundation\Model\Helper;

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
}
