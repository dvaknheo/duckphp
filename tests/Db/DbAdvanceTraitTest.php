<?php
namespace tests\DuckPhp\Db;

use DuckPhp\Db\DbAdvanceTrait;
use DuckPhp\Db\Db;

class DbAdvanceTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(DbAdvanceTrait::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $options = $setting['database_list'][0];
        
        $db=new Db();
        $db->init($options);
        
        $array=[];
        $db->quoteIn($array);
        $db->qouteInsertArray($array);
        $array=[1,2,3];
        $db->quoteIn($array);
        $db->quoteSetArray($array);
        $db->quoteAndArray($array);
        $db->qouteInsertArray($array);
        $me=$db->findData('Users', 'aa', 'username');
                
        $table_name='Users';
        $name="newTest1";
        $ret=$db->insertData($table_name, ['username'=>$name,'password'=>'123456']);
        $ret=$db->deleteData($table_name, $name,'username','password');
        $ret=$db->updateData($table_name, $name, ['username'=>'111','password'=>'333'], $key='username');
        $ret=$db->deleteData($table_name, $name,'username',null);
        
        $name="newTest2";
        $ret=$db->insertData($table_name, ['username'=>$name,'password'=>'123456'],false);
        $ret=$db->deleteData($table_name, $name,'username',null);

        
        var_dump($db->fetchAll("select * from Users"));
        
        
        $db->pdo=null;
        $db->qouteInsertArray($array);
        
        \LibCoverage\LibCoverage::End();
        /*
        $db->quoteIn($array);
        $db->quoteSetArray($array);
        $db->qouteInsertArray($array);
        $db->findData($table_name, $id, $key='id');
        $db->insertData($table_name, $data, $return_last_id=true);
        $db->updateData($table_name, $id, $data, $key='id');
        $db->deleteData($table_name, $id, $key='id', $key_delete='is_deleted');
        //*/
    }
}
