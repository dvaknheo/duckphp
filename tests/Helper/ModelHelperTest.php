<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\ModelHelper;

class ModelHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(ModelHelper::class);

        $sql="Select * from users";
        ModelHelper::SqlForPager($sql,1,5);
        ModelHelper::SqlForCountSimply($sql);

        try{
            ModelHelper::DB();
        }catch(\Throwable $ex){}
        try{
            ModelHelper::DB_R();
        }catch(\Throwable $ex){}
        try{
            ModelHelper::DB_W();
        }catch(\Throwable $ex){}
        \MyCodeCoverage::G()->end();
        /*
        //*/
    }
}
