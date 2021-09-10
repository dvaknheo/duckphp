<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleModelTrait;
use DuckPhp\DuckPhp;

class SimpleModelTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleModelTrait::class);
        
        $path_app = \LibCoverage\LibCoverage::G()->getClassTestPath(SimpleModelTrait::class);

        $setting = include $path_app . 'setting.php';

        $options=[
            'setting'=>$setting,
            'path_sql_dump' =>$path_app,
        ];
        DuckPhp::G(new DuckPhp())->init($options);
        
        echo TestModel::G()->table();

        

        TestModel::G()->test($id);

        
        \LibCoverage\LibCoverage::End();
    }
}
class Base
{
    use \DuckPhp\SingletonEx\SingletonExTrait;
    use SimpleModelTrait;
    //static $class_var;
}
class TestModel extends Base
{
    protected $table_name=null;
    protected $table_pk='id';
    public function test($id)
    {
        TestModel::G()->find('1');
        TestModel::G()->getList();
        $id=TestModel::G()->add(['content' =>DATE(DATE_ATOM)]);
        TestModel::G()->update($id,['content' =>DATE(DATE_ATOM)]);
        $sql="delete from 'TABLE' where id =?";
        $sql=TestModel::G()->prepare($sql);
        
        
        DuckPhp::Db()->execute($sql,$id);

        TestModel::G()->fetchAll("select * from 'TABLE' where id =? ", $id);
        TestModel::G()->fetch("select * from 'TABLE' where id =? ", $id);
        TestModel::G()->fetchColumn("select * from 'TABLE' where id =? ", $id);
        TestModel::G()->fetchObject("select * from 'TABLE' where id =? ", $id);
        TestModel::G()->fetchObjectAll("select * from 'TABLE' where id =? ", $id);
        TestModel::G()->execute("update 'TABLE' set content = ?  where id =? ",  DATE(DATE_ATOM),$id);
    }
}

