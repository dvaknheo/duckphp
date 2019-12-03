<?php
namespace tests\DuckPhp\DB;

use DuckPhp\DB\DB;

class DBTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(DB::class);
        $options=[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
];
        $db=DB::CreateDBInstance($options);
        $db->getPDO();
        echo $db->quote("'");
        $db->quote(["'"]);
         $db->quote(new \stdClass);
        echo PHP_EOL;
        
        $sql="select * from Users limit 1";
        $x=$db->fetchAll($sql);
        $sql="select * from Users where username=:username";
        $x=$db->fetchAll($sql,['username'=>'aa']);

        $sql="select * from Users limit 1";
        $x=$db->fetch($sql);
        $sql="select * from Users where username=:username";
        $x=$db->fetch($sql,['username'=>'aa']);

        $sql="select * from Users limit 1";
        $x=$db->fetchColumn($sql);
        $sql="select * from Users where username=:username";
        $x=$db->fetchColumn($sql,['username'=>'aa']);

        $sql="show tables";
        $db->execQuick($sql);
        $db->execQuick($sql,['a'=>'b']);
        $name="_Test4";
        $ret=$db->insertData('Users', ['username'=>$name,'password'=>'123456'],false);
        $id=$db->lastInsertId();
        
        $sql="delete from Users where id=?";
        $db->execQuick($sql,$id);
        var_dump($ret);
        $db->rowCount();
        
        //code here
        DB::CloseDBInstance($db);
        
        \MyCodeCoverage::G()->end(DB::class);
        $this->assertTrue(true);
        /*
        $db->init($options=[], $context=null);
        $db->CreateDBInstance($db_config);
        $db->CloseDBInstance($db, $tag=null);
        $db->check_connect();
        $db->close();
        $db->getPDO();
        $db->quote($string);
        $db->fetchAll($sql, ...$args);
        $db->fetch($sql, ...$args);
        $db->fetchColumn($sql, ...$args);
        $db->execQuick($sql, ...$args);
        $db->rowCount();
        //*/
    }
}
