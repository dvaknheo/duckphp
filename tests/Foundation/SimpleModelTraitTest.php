<?php
namespace tests\DuckPhp\Foundation;

use DuckPhp\Foundation\SimpleModelTrait;
use DuckPhp\DuckPhpAllInOne;

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
        DuckPhpAllInOne::_(new DuckPhpAllInOne())->init($options);
        
        echo EmptyModel::_()->table();
$sql = "DROP TABLE IF EXISTS `empty`;";
        DuckPhpAllInOne::Db()->execute($sql);

        $sql="CREATE TABLE `empty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='keep me empty'";
        DuckPhpAllInOne::Db()->execute($sql);

        EmptyModel::_()->test($id);
        
$sql= 'DROP TABLE IF EXISTS `empty`';
DuckPhpAllInOne::Db()->execute($sql);
        EmptyModel::CallInPhase(DuckPhpAllInOne::class)->foo();
        \LibCoverage\LibCoverage::End();
    }
}
class Base
{
    //use \DuckPhp\SingletonEx\SingletonExTrait;
    use SimpleModelTrait;
    //static $class_var;
}
class EmptyModel extends Base
{
    protected $table_name=null;
    protected $table_pk='id';
    public function test($id)
    {
        EmptyModel::_()->find('1');
        EmptyModel::_()->getList();
        $id=EmptyModel::_()->add(['data' =>DATE(DATE_ATOM)]);
        EmptyModel::_()->update($id,['data' =>DATE(DATE_ATOM)]);
        $sql="delete from `'TABLE'` where id =?";
        $sql=EmptyModel::_()->prepare($sql);
        DuckPhpAllInOne::Db()->execute($sql,$id);

        EmptyModel::_()->fetchAll("select * from `'TABLE'` where id =? ", $id);
        EmptyModel::_()->fetch("select * from `'TABLE'` where id =? ", $id);
        EmptyModel::_()->fetchColumn("select * from `'TABLE'` where id =? ", $id);
        EmptyModel::_()->fetchObject("select * from `'TABLE'` where id =? ", $id);
        EmptyModel::_()->fetchObjectAll("select * from `'TABLE'` where id =? ", $id);
        EmptyModel::_()->execute("update `'TABLE'` set data = ?  where id =? ",  DATE(DATE_ATOM),$id);
    }
    public function foo()
    {
        //
    }
}

