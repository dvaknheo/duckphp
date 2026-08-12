<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\Component\DbManager;
use DuckPhp\Ext\SqlDumperSupporter;
//use tests_Data_SqlDumper\Model\EmptyModel;

class SqlDumperSupporterTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SqlDumperSupporter::class);
        try{
            SqlDumperSupporter::_()->getAllTable();
        }catch(\Exception $ex){}
        try{
            SqlDumperSupporter::_()->getSchemeByTable('table');
        }catch(\Exception $ex){}

        $options =[
            'database_driver'=>'mysql',
        ];
        DuckPhp::_()->init($options);
        SqlDumperSupporter::Current();

        $options =[
            'database_driver'=>'no_exists',
        ];
        DuckPhp::_()->init($options);
        DbManager::_()->options['database_driver']="no_exists";
        try{
        SqlDumperSupporter::Current();
        }catch(\Exception $ex){}
        
        \LibCoverage\LibCoverage::End();
    }
}
