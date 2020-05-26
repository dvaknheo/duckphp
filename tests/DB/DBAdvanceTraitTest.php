<?php
namespace tests\DuckPhp\DB;

use DuckPhp\DB\DBAdvanceTrait;
use DuckPhp\DB\DB;

class DBAdvanceTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DBAdvanceTrait::class);
        
        $options=[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
];
        $db=DB::CreateDBInstance($options);
        
        $array=[];
        $db->quoteIn($array);
        $db->qouteInsertArray($array);
        $array=[1,2,3];
        $db->quoteIn($array);
        $db->quoteSetArray($array);
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
        
        \MyCodeCoverage::G()->end();
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
