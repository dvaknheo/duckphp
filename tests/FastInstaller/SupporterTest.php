<?php
namespace tests\DuckPhp\FastInstaller;

use DuckPhp\DuckPhp as DuckPhp;
use DuckPhp\FastInstaller\Supporter;
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
            Supporter::_()->getInstallDesc([]);
        }catch(\Exception $ex){}
        try{
            Supporter::_()->getSchemeByTable('table');
        }catch(\Exception $ex){}
        try{
            Supporter::_()->writeDsnSetting([]);
        }catch(\Exception $ex){}
        
        $options =[
            'database_driver'=>'mysql',
        ];
        DuckPhp::_()->init($options);
        Supporter::Current();
        $options =[];
        Supporter::_()->readDsnSetting($options);
        
        $options =["dsn"=>'mysql:host=127.0.0.1;port=3306',"a"=>"b"];
        Supporter::_()->readDsnSetting($options);
        
        \LibCoverage\LibCoverage::End();
    }
}
