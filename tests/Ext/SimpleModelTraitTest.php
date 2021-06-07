<?php
namespace tests\DuckPhp\Ext;

use DuckPhp\Ext\SimpleModelTrait;

class SimpleModelTraitTest extends \PHPUnit\Framework\TestCase
{

    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SimpleModelTrait::class);
        
        //SimpleModel::G()->find();
        
        \MyCodeCoverage::G()->end();
    }
}
class SimpleModel
{
    use \DuckPhp\SingletonEx\SingletonExTrait;

    //static $class_var;
}
