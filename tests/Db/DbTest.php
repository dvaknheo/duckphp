<?php
namespace tests\DuckPhp\Db;

use DuckPhp\Db\Db;

class DbTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Db::class);
        
        $path_setting = \LibCoverage\LibCoverage::G()->getClassTestPath(Db::class);
        $setting = include $path_setting . 'setting.php';
        $options = $setting['database_list'][0];
        
        $db=new Db;
        $db->init($options);
        $pdo=$db->PDO();
        $db->PDO($pdo);
        $db->setBeforeQueryHandler(function($db, $sql,...$args){
            echo "Quering... ".$db->buildQueryString($sql,...$args) ."\n";
        });
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
        $db->execute($sql);
        $db->execute($sql,['a'=>'b']);
        $name="_Test4";
        $ret=$db->insertData('Users', ['username'=>$name,'password'=>'123456'],false);
        $id=$db->lastInsertId();
        
        $sql="delete from Users where id=?";
        $db->execute($sql,$id);
        var_dump($ret);
        $db->rowCount();
        
        ////[[[[
        $sql="select * from Users limit 1";
        $x=$db->setObjectResultClass(DbTestUser::class)->fetchObjectAll($sql);
        $sql="select * from Users where username=:username";
        $x=$db->fetchObjectAll($sql,['username'=>'aa']);


        $sql="select * from 'TABLE' limit 1";
        $x=$db->table('Users')->fetchObject($sql);
        $sql="select * from Users where username=:username";
        $x=$db->fetchObject($sql,['username'=>'aa']);
        
        ////]]]]
        
        
        //code here
        $db->close($db);
        
        \LibCoverage\LibCoverage::End();
        /*
  `username` varchar(32) COLLATE utf8_bin NOT NULL,
  `password` varchar(64) COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  
        $db->rowCount();
        //*/
    }
}
class DbTestUser
{
    public $username;
    public $password;
    public $created_at;
    public function foo(){
        return;
    }
}
