<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SimpleModelTrait;
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
        
        TestModel::G()->find('1');
        TestModel::G()->getList();
        $id=TestModel::G()->add(['content' =>DATE(DATE_ATOM)]);
        TestModel::G()->update($id,['content' =>DATE(DATE_ATOM)]);
        $sql="delete from 'TABLE' where id =?";
        $sql=TestModel::G()->prepare($sql);
        DuckPhp::Db()->execute($sql,$id);
        
        try{
            TestModel::G()->delete($id);
        }catch(\Exception $ex){}
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

}

