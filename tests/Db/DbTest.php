<?php
namespace tests\DuckPhp\Db;

use DuckPhp\Db\Db;

class DbTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Db::class);
        $options=[
	'dsn'=>"mysql:host=127.0.0.1;port=3306;dbname=DnSample;charset=utf8;",
	'username'=>'admin',	
	'password'=>'123456'
];
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
        
        //code here
        $db->close($db);
        
        \MyCodeCoverage::G()->end();
        /*
        $db->init($options=[], $context=null);
        $db->CreateDbInstance($db_config);
        $db->CloseDbInstance($db, $tag=null);
        $db->check_connect();
        $db->close();
        $db->getPDO();
        $db->quote($string);
        $db->fetchAll($sql, ...$args);
        $db->fetch($sql, ...$args);
        $db->fetchColumn($sql, ...$args);
        $db->execute($sql, ...$args);
        $db->rowCount();
        //*/
    }
}
