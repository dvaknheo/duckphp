<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\Component\DbManager;
use DuckPhp\Ext\Supporter;
//use tests_Data_SqlDumper\Model\EmptyModel;

class SupporterTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Supporter::class);
        try{
            Supporter::_()->getAllTable();
        }catch(\Exception $ex){}
        try{
            Supporter::_()->getSchemeByTable('table');
        }catch(\Exception $ex){}

        $options =[
            'database_driver'=>'mysql',
        ];
        DuckPhp::_()->init($options);
        Supporter::Current();

        $options =[
            'database_driver'=>'no_exists',
        ];
        DuckPhp::_()->init($options);
        DbManager::_()->options['database_driver']="no_exists";
        try{
        Supporter::Current();
        }catch(\Exception $ex){}
        
        \LibCoverage\LibCoverage::End();
    }
}
