<?php
namespace tests\DuckPhp\SingletonEx;

use DuckPhp\SingletonEx\SingletonEx;

class SingletonExTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(SingletonEx::class);
        
        SingletonExObject::G();
        SingletonExObject::G(new SingletonExObject());
        define('__SINGLETONEX_REPALACER',SingletonExObject::class.'::CreateObject');
        SingletonExObject::G();
        
        \MyCodeCoverage::G()->end();
        /*
        SingletonEx::G()->G($object=null);
        //*/
    }
}
class SingletonExObject
{
    use \DuckPhp\SingletonEx\SingletonEx;
    
    public static function CreateObject($class, $object)
    {
        static $_instance;
        $_instance=$_instance??[];
        $_instance[$class]=$object?:($_instance[$class]??($_instance[$class]??new static));
        return $_instance[$class];
    }

}
