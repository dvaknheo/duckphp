<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SimpleModelTrait;

class SimpleModelTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(SimpleModelTrait::class);
        
        //SimpleModel::G()->find();
        
        \LibCoverage\LibCoverage::End();
    }
}
class SimpleModel
{
    use \DuckPhp\SingletonEx\SingletonExTrait;

    //static $class_var;
}
