<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Component\SimpleModelTrait;
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
        
        echo EmptyModel::G()->table();
$sql = "DROP TABLE IF EXISTS `empty`;";
        DuckPhp::Db()->execute($sql);

        $sql="CREATE TABLE `empty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='keep me empty'";
        DuckPhp::Db()->execute($sql);

        EmptyModel::G()->test($id);
        
$sql= 'DROP TABLE IF EXISTS `empty`';
DuckPhp::Db()->execute($sql);
        
        \LibCoverage\LibCoverage::End();
    }
}
class Base
{
    use \DuckPhp\SingletonEx\SingletonExTrait;
    use SimpleModelTrait;
    //static $class_var;
}
class EmptyModel extends Base
{
    protected $table_name=null;
    protected $table_pk='id';
    public function test($id)
    {
        EmptyModel::G()->find('1');
        EmptyModel::G()->getList();
        $id=EmptyModel::G()->add(['data' =>DATE(DATE_ATOM)]);
        EmptyModel::G()->update($id,['data' =>DATE(DATE_ATOM)]);
        $sql="delete from 'TABLE' where id =?";
        $sql=EmptyModel::G()->prepare($sql);
        DuckPhp::Db()->execute($sql,$id);

        EmptyModel::G()->fetchAll("select * from 'TABLE' where id =? ", $id);
        EmptyModel::G()->fetch("select * from 'TABLE' where id =? ", $id);
        EmptyModel::G()->fetchColumn("select * from 'TABLE' where id =? ", $id);
        EmptyModel::G()->fetchObject("select * from 'TABLE' where id =? ", $id);
        EmptyModel::G()->fetchObjectAll("select * from 'TABLE' where id =? ", $id);
        EmptyModel::G()->execute("update 'TABLE' set data = ?  where id =? ",  DATE(DATE_ATOM),$id);
    }
}

